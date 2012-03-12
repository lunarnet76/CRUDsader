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
	class Map extends MetaClass {
		const BASE_ASSOCIATION_CLASS = '__ASSOC__';
		/**
		 * @var array
		 */
		protected $_map = NULL;

		/**
		 * the list of dependencies
		 * @var array
		 */
		protected $_hasDependencies = array('loader');

		/**
		 * identify the class
		 * @var string
		 */
		protected $_classIndex = 'map';

		/**
		 * list fields to include in array
		 * @var string
		 */
		protected $_toArray = array('_map');

	

		/**
		 * @param Block $configuration
		 */
		public function setConfiguration(\CRUDsader\Block $configuration = null)
		{
			$this->_configuration = $configuration;
			$this->_map = $this->_dependencies['loader']->getSchema($this->_configuration->defaults);
		}

		public function generateRandom($max = 100, $progress = false, $save = true)
		{
			$collection = array();
			if ($save)
				$wu = new Object\UnitOfWork();
			foreach ($this->_map['classes'] as $className => $definition) {
				for ($i = 0; $i < $max; $i++) {
					$o = \CRUDsader\Object::instance($className);
					$o->generateRandom();
					$collection[] = $o;
					if ($save)
						$o->save($wu);
				}
				if ($progress) {
					echo $className . '.';
					flush();
				}
			}
			if ($save) {
				echo ': ' . \CRUDsader\Debug::getMemoryUsage() . ' : ... saving';
				flush();
				$wu->execute();
				echo ' : DONE ' . PHP_EOL;
				flush();
			}
			return $collection;
		}

		public function classGetFieldAttributeType($className, $attributeName)
		{
			return $this->_map['attributeTypes'][$this->_map['classes'][$className]['attributes'][$attributeName]['type']];
		}

		public function classGetFieldAttributeDefaultType()
		{
			return $this->_map['attributeTypes']['default'];
		}

		public function classGetModelClass($className)
		{
			return !empty($this->_map['classes'][$className]['definition']['phpClass']) ? $this->_map['classes'][$className]['definition']['phpClass'] : false;
		}

		public function classGetSearchableFields($className)
		{
			$ret = array();
			if(!empty($this->_map['classes'][$className]['attributes']))
				foreach ($this->_map['classes'][$className]['attributes'] as $attributeName => $attributeInfos){

					if ($attributeInfos['searchable'])
						$ret[] = $attributeName;
				}
			return $ret;
		}

		/**
		 * return the definition of all the classes
		 * @return array
		 */
		public function getClasses()
		{
			return $this->_map['classes'];
		}

		/**
		 * validate the schema
		 * @return bool 
		 */
		public function validate()
		{
			return $this->_dependencies['loader']->validate($this->_configuration->defaults);
		}

		public function extract()
		{
			$this->setDependency('extractor', 'map.extractor');
			$this->_dependencies['extractor']->setConfiguration($this->_configuration->defaults);
			return $this->_dependencies['extractor']->extract($this->_map);
		}

		/**
		 *  @return bool
		 */
		public function classExists($className)
		{
			return isset($this->_map['classes'][$className]);
		}

		/**
		 * @param string $className
		 * @return string 
		 */
		public function classGetDatabaseTable($className)
		{
			return $this->_map['classes'][$className]['definition']['databaseTable'];
		}

		public function classGetDatabaseTableField($className, $attributeName)
		{
			if(!isset($this->_map['classes'][$className]['attributes'][$attributeName]) && isset($this->_map['classes'][$className]['attributesReversed'][$attributeName])){// fks
				return $this->_map['classes'][$className]['attributesReversed'][$attributeName];
			}
			return $attributeName == 'id' ? $this->_map['classes'][$className]['definition']['databaseIdField'] : $this->_map['classes'][$className]['attributes'][$attributeName]['databaseField'];
		}

		public function classGetAttributeCount($className)
		{
			return $className == self::BASE_ASSOCIATION_CLASS ? 2 : count($this->_map['classes'][$className]['definition']['attributeCount']);
		}

		public function classHasAssociation($className, $associationName)
		{
			return isset($this->_map['classes'][$className]['associations'][$associationName]);
		}

		public function classGetAssociation($className, $associationName)
		{
			return $this->_map['classes'][$className]['associations'][$associationName];
		}

		public function classGetInfos($className)
		{
			return $this->_map['classes'][$className];
		}

		public function classHasParent($className)
		{
			return $this->classGetParent($className);
		}

		public function classGetParent($className)
		{
			return $this->_map['classes'][$className]['inherit'];
		}

		public function classInheritsFrom($className, $inheritFrom)
		{
			if ($this->classHasParent($className))
				return $this->classHasParent($className) == $inheritFrom || $this->classInheritsFrom($this->classGetParent($className), $inheritFrom);
			return false;
		}

		public function classGetJoin($className, $associationName, $fromAlias, $joinedAlias)
		{
			static $associationAliasTmp = 'association';
			if ($associationName == 'parent') {
				if (!$this->classHasParent($className))
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
					$associationAlias = $associationAliasTmp++;

					$joins['association'] = array(
					    'table' => $association['databaseTable'], // file2post
					    'alias' => $associationAlias, // 
					    'field' => $association['internalField'], // post
					    'joinAlias' => $fromAlias, // j
					    'joinField' => $this->_map['classes'][$className]['definition']['databaseIdField'], // id
					    'type' => 'left'
					);
					
					$joins['table'] = array(
					    'table' => $this->_map['classes'][$association['to']]['definition']['databaseTable'],
					    'alias' => $joinedAlias,
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

		/**
		 * return an array of lines to create a class file containing datacontract to be used for serialization in .NET
		 * @param string $appPackage
		 * @return array 
		 */
		public function toMicrosoftDataContractClass($appPackage = 'crudsader.model.contract')
		{
			$classes = $this->_map['classes'];

			$ret = array();
				

			foreach ($classes as $name => $definition) {
				
				$ret[$name] = array();
				$ret[$name][] = 'string polymorphism';
				$ret[$name][] = 'string id';
				
				if(!empty($definition['attributes']))
					foreach ($definition['attributes'] as $attributeName => $attributeDefinition) {
						$type = $this->_map['attributeTypes'][$attributeDefinition['type']];
						$ret[$name][$attributeName] = (isset($type['options']['dotnetcast']) ? $type['options']['dotnetcast'] : 'string') . ' ' . $attributeName;
					}
				if(!empty($definition['associations']))
				foreach ($definition['associations'] as $associationName => $associationDefinition) {
					if ($associationDefinition['reference'] == 'internal') {
						$ret[$name][$associationDefinition['to']] = $associationDefinition['to'] . ' ' . ($associationDefinition['to'] == $name ? 'parent':$associationDefinition['to']);
					} else {
						$ret[$associationDefinition['to']][$name] = $name . ' ' . $name;
					}
				}
				
				
			}
			$lines = array('using System.Runtime.Serialization;' . PHP_EOL .'using System.Collections.Generic;'.PHP_EOL. 'namespace ' . $appPackage . '{' . PHP_EOL);

			foreach ($ret as $className => $classMembers) {
				$lines[] = PHP_EOL . PHP_EOL . '	[DataContract]' . PHP_EOL . '	public class ' . $className . '{' . PHP_EOL;
				foreach ($classMembers as $name)
					$lines[] = '		[DataMember]' . PHP_EOL . '		public ' . $name . ';' . PHP_EOL;
				$lines[] = '	}';
				$lines[] = PHP_EOL . PHP_EOL . '	[DataContract]' . PHP_EOL . '	public class ' . $className . 's{' . PHP_EOL.PHP_EOL.'		[DataMember]'.PHP_EOL.'		public List&lt;'.$className.'> '.$className.';'.PHP_EOL.'	}'.PHP_EOL;
			}
			$lines[] = '}';
			

			return $lines;
		}

		public static function getDatabaseAssociationTable($associationName, $classTo, $classFrom)
		{
			return $associationName? : ($classFrom > $classTo ? $classTo . '2' . $classFrom : $classFrom . '2' . $classTo);
		}
	}
	class MapException extends \CRUDsader\Exception {
		
	}
}