<?php
/**
 * LICENSE: see Art/license.txt
 *
 * @author      Jean-Baptiste Verrey <jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     http://www.Art.com/license/1.txt
 * @version     $Id$
 * @link        http://www.Art.com/manual/
 * @since       1.0
 */
namespace Art {
    /**
     * Object class
     * @package     Art
     */
    class Object implements Interfaces\Initialisable,  Interfaces\Arrayable {
        protected $_class;
        protected $_map;
        protected $_initialised = false;
        protected $_parent;
        protected $_isPersisted = false;
        protected $_infos;
        protected $_fields = array();
        protected $_associations = array();

        public function __construct($className) {
            $this->_class = $className;
            $this->_map = \Art\Map::getInstance();
            $this->_infos = $this->_map->classGetInfos($this->_class);
            if (!Map::getInstance()->classExists($className))
                throw new ObjectException('class "' . $className . '" does not exist');
        }
        
        public function getForm($oql=false,$alias=false,\Art\Form $form=null){
            if($alias===false)
                $alias=$this->_class;
            if($form===null)
                $form=new \Art\Form($this->_class.$this->_isPersisted);
            $l=strlen($alias)+1;
            foreach($this->_infos['attributes'] as $name=>$infoAttribute){
               // if(!$infoAttribute['calculated'])
                 //   $form->add($this->getAttribute($name),$name,$infoAttribute['mandatory']);
            }
            if($alias==$this->_class)
                $form->add($this->getParent(true)->getForm($oql,$alias.'_parent',$form),$alias.'_parent',true);
            /*if($oql){
                $query=new \Art\Query($oql);
                $infos=$query->getInfos();
                foreach($infos['mapFields'] as $name=>$useless){
                    if($name==$this->_class)continue;
                    $name=substr($name,$l);
                    if($name=='parent'){
                       $form->add($this->getParent(true)->getForm($oql,$alias.'_parent',$form),$alias.'_parent',true);
                    }//else
                        //$form->add($this->getAssociation($name)->getForm(),$name);
                }
            }else{
                // @todo
            }*/
            return $form;
        }
        
        public function hasParent(){
            return $this->_infos['inherit'];
        }
        
        public function getParent($initialiseIfNot=false){
            if(!isset($this->_parent)){
                if(!$initialiseIfNot)
                    throw new ObjectException ('parent is not initialised');
                $this->_parent=new \Art\Object($this->_infos['inherit']);
            }
            return $this->_parent;
        }

        public function isInitialised() {
            return $this->_initialised;
        }

        public function hasAssociation($associationName) {
            return isset($this->_infos['associations'][$associationName]);
        }

        public function getAssociation($associationName) {
            if (!isset($this->_associations[$associationName]))
                $this->_associations[$associationName] = new \Art\Object\Collection\Association($this->_infos['associations'][$associationName], $this->_class);
            return $this->_associations[$associationName];
        }

        public function toArray($full=false) {
            $ret = $full ? array('class' => $this->_class, 'initialised' => $this->_initialised ? 'yes' : 'no', 'persisted' => $this->_isPersisted, 'fields' => $this->_fields) : $this->_fields;
            if ($this->_parent)
                $ret['parent'] = $this->_parent->toArray($full);
            if (!empty($this->_associations))
                foreach ($this->_associations as $name => $association)
                    $ret['associations'][$name] = $association->toArray($full);
            return $ret;
        }

        public function getAttribute($name) {
            if (!isset($this->_fields[$name])) {
                $type = $this->_map->classGetFieldAttributeType($this->_class, $name);
                $this->_fields[$name] = new Object\Attribute($name, $type['class'], $type['options']);
            }
            return $this->_fields[$name];
        }

        public function __get($var) {
            switch (true) {
                // fields
                case isset($this->_infos['attributes'][$var]):
                    return $this->getAttribute($var)->getValue();
                    break;
            }
        }

        public function __set($var, $value) {
            switch (true) {
                // fields
                case isset($this->_infos['attributes'][$var]):
                    if (!$this->getAttribute($var)->setValue($value, $this->_infos['attributes'][$var]['mandatory']))
                        throw new ObjectException('attribute "' . $var . '" cannot accept "' . $value . '" as a value');
                    break;
            }
        }

        /**
         * forbid cloning
         * @final
         * @access private
         */
        final private function __clone() {
            
        }
    }
    class ObjectException extends \Exception {
        
    }
}