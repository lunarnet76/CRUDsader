<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader {

    /**
     * Map the ORM schema to classes
     * @package     CRUDsader
     */
    class Map extends Singleton implements Interfaces\Configurable{
        const BASE_ASSOCIATION_CLASS='__ASSOC__';
        /**
         * @var \CRUDsader\Block 
         */
        protected $_configuration = NULL;
        /**
         * @var array 
         */
        protected $_adapters = array();
        /**
         * @var array
         */
        protected $_map = NULL;

        /**
         * constructor, load the map schema
         */
        public function init() {
            $this->setConfiguration(\CRUDsader\Configuration::getInstance()->map);
        }
        
        public function generateRandom($max=100,$progress=false,$save=true){
            $collection=array();
            if($save)
                $wu=new Object\UnitOfWork();
            foreach($this->_map['classes'] as $className=>$definition){
                for($i=0;$i<$max;$i++){
                    $o=\CRUDsader\Object::instance($className);
                    $o->generateRandom();
                    $collection[]=$o;
                    if($save)$o->save($wu);
                }
                if($progress){
                echo $className.'.';
                flush();}
            }
            if($save){
                echo ': '.\CRUDsader\Debug::getMemoryUsage().' : ... saving';flush();
                $wu->execute ();
                echo ' : DONE '.PHP_EOL;flush();
            }
            return $collection;
        }
        
        /**
         * @param Block $configuration
         */
        public function setConfiguration(\CRUDsader\Block $configuration=null) {
            $this->_configuration = $configuration;
            $this->_adapter['loader'] = \CRUDsader\Adapter::factory(array('map' => 'loader'));
            $this->_map = $this->_adapter['loader']->getSchema($this->_configuration->defaults);
        }

        /**
         * @return Block
         */
        public function getConfiguration() {
            return $this->_configuration;
        }
        
        
        
        public function classGetFieldAttributeType($className,$attributeName){
            return $this->_map['attributeTypes'][$this->_map['classes'][$className]['attributes'][$attributeName]['type']];
        }
        
        public function classGetModelClass($className){
            return $this->_map['classes'][$className]['definition']['phpClass'];
        }

        /**
         * validate the schema
         * @return bool 
         */
        public function validate() {
            return $this->_adapter['loader']->validate($this->_configuration->defaults);
        }

        public function extract() {
            $this->_adapter['extractor'] = \CRUDsader\Adapter::factory(array('map' => 'extractor'));
            $this->_adapter['extractor']->setConfiguration($this->_configuration->defaults);
            return $this->_adapter['extractor']->create($this->_map);
        }

        /**
         *  @return bool
         */
        public function classExists($className) {
            return isset($this->_map['classes'][$className]);
        }

        /**
         * @param string $className
         * @return string 
         */
        public function classGetDatabaseTable($className) {
            return $this->_map['classes'][$className]['definition']['databaseTable'];
        }

        public function classGetDatabaseTableField($className, $attributeName) {
            return $attributeName == 'id' ? $this->_map['classes'][$className]['definition']['databaseIdField'] : $this->_map['classes'][$className]['attributes'][$attributeName]['databaseField'];
        }

        public function classGetAttributeCount($className) {
            return $className == self::BASE_ASSOCIATION_CLASS ? 2 : count($this->_map['classes'][$className]['definition']['attributeCount']);
        }

        public function classHasAssociation($className, $associationName) {
            return isset($this->_map['classes'][$className]['associations'][$associationName]);
        }

        public function classGetAssociation($className, $associationName) {
            return $this->_map['classes'][$className]['associations'][$associationName];
        }

        public function classGetInfos($className) {
            return $this->_map['classes'][$className];
        }

        public function classHasParent($className) {
            return $this->classGetParent($className);
        }

        public function classGetParent($className) {
            return $this->_map['classes'][$className]['inherit'];
        }
        
        public function classInheritsFrom($className,$inheritFrom) {
            if($this->classHasParent($className))
                return  $this->classHasParent($className)==$inheritFrom || $this->classInheritsFrom($this->classGetParent($className),$inheritFrom);
            return false;
        }

        public function classGetJoin($className, $associationName, $fromAlias, $joinedAlias) {
            static $associationAliasTmp='association';
            if ($associationName == 'parent') {
                if(!$this->classHasParent($className))
                     throw new MapException('join error : class "' . $className . '" has no parent');
                $parentClass = $this->classGetParent($className);
                return array(
                    'table' => array(
                        'table' => $this->_map['classes'][$parentClass]['definition']['databaseTable'],
                        'alias' => $joinedAlias,
                        'field' => $this->_map['classes'][$parentClass]['definition']['databaseIdField'],
                        'joinAlias' => $fromAlias,
                        'joinField' => $this->_map['classes'][$className]['definition']['databaseIdField'],
                        'class' => $parentClass,
                        'type' => 'left'
                    )
                );
            }
            if (!isset($this->_map['classes'][$className]['associations'][$associationName]))
                throw new MapException('join error : class "' . $className . '" has no association "' . $associationName . '"');
            $association = $this->_map['classes'][$className]['associations'][$associationName];
            $joins = array();
            switch ($association['reference']) {
                case 'external':
                    $joins['table'] = array(
                        'table' => $this->_map['classes'][$association['to']]['definition']['databaseTable'],
                        'alias' => $joinedAlias,
                        'field' => $association['externalField'],
                        'joinAlias' => $fromAlias,
                        'joinField' => $this->_map['classes'][$className]['definition']['databaseIdField'],
                        'class' => $association['to'],
                        'type' => 'left'
                    );
                    break;
                case 'internal':
                    $joins['table'] = array(
                        'table' => $this->_map['classes'][$association['to']]['definition']['databaseTable'],
                        'alias' => $joinedAlias,
                        'field' => $this->_map['classes'][$association['to']]['definition']['databaseIdField'],
                        'joinAlias' => $fromAlias,
                        'joinField' => $association['internalField'],
                        'class' => $association['to'],
                        'type' => 'left'
                    );
                    break;
                case 'table':
                    $associationAlias=$associationAliasTmp++;
                    $joins['association'] = array(
                        'table' => $association['databaseTable'],// file2post
                        'alias' =>$associationAlias, // 
                        'field' => $association['internalField'],// post
                        'joinAlias' => $fromAlias, // j
                        'joinField' => $this->_map['classes'][$className]['definition']['databaseIdField'],// id
                        'type' => 'left'
                    );
                    $joins['table'] = array(
                        'table' => $this->_map['classes'][$association['to']]['definition']['databaseTable'],
                        'alias' =>$joinedAlias,
                        'field' => $this->_map['classes'][$association['to']]['definition']['databaseIdField'],
                        'joinAlias' => $associationAlias,
                        'joinField' => $association['externalField'],
                        'class' => $association['to'],
                        'type' => 'left'
                    );
                    break;
            }
            return $joins;
        }

        public static function getDatabaseAssociationTable($associationName, $classTo, $classFrom) {
            return $associationName? : ($classFrom > $classTo ? $classTo . '2' . $classFrom : $classFrom . '2' . $classTo);
        }
    }
    class MapException extends \CRUDsader\Exception {
        
    }
}