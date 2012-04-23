<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader {
	/**
	 * Object class
	 * @package     CRUDsader
	 */
	class Object implements Interfaces\Initialisable, Interfaces\Arrayable, \SplObserver, \SplSubject {
		protected $_class;
		protected $_map;
		protected $_initialised = false;
		protected $_isModified = false;
		protected $_linkedAssociation = false;
		protected $_linkedAssociationId = false;
		protected $_parent;
		protected $_child;
		protected $_isPersisted = false;
		protected $_infos;
		protected $_saveId=false;
		protected $_fields = array();
		protected $_associations = array();
		protected $_observers = array();

		/**
		 * @param string $className 
		 */
		public function __construct($className)
		{
			$this->_class = $className;
			$this->_map = \CRUDsader\Instancer::getInstance()->map;
			$this->_infos = $this->_map->classGetInfos($this->_class);
			if (!$this->_map->classExists($className))
				throw new ObjectException('class "' . $className . '" does not exist');
			foreach ($this->_infos['attributes'] as $attributeName => $attributeInfos) {
				if (isset($attributeInfos['default'])) {
					$this->getAttribute($attributeName)->setDefaultValue($attributeInfos['default']);
				}
			}
		}

		public static function findByid($class, $id)
		{
			$query = \CRUDsader\Instancer::getInstance()->query('FROM ' . $class . ' o WHERE o.id=?');
			return $query->fetch($id);
		}

		public static function instance($className)
		{
			return Instancer::getInstance()->object($className);
		}

		public function getDefinition()
		{
			return $this->_infos;
		}

		public function sync()
		{
			if ($this->_isPersisted)
				\CRUDsader\Instancer::getInstance()->query('FROM ' . $this->_class . ' o WHERE o.id=?')->fetch($this->_isPersisted);
		}

		public function receiveArray(array $array)
		{
			foreach ($this->_infos['attributes'] as $attributeName => $attributeInfos) {
				if (isset($array[$attributeName]))
					$this->$attributeName = $array[$attributeName];
			}
			foreach ($this->_infos['associations'] as $associationName => $associationInfos) {

				if ($associationInfos['reference'] == 'internal' && isset($array[$associationInfos['internalField']]))
					$this->{$associationName}[] = \CRUDsader\Instancer::getInstance()->{'object.proxy'}($associationInfos['to'], $array[$associationInfos['internalField']]);
			}
		}

		public function addExtraAttribute($name, $value = null)
		{
			$this->_infos['attributes'][$name] = array(
			    'json' => true,
			    'extra' => true,
			    'required' => false,
			    'html' => false,
			    'calculated' => false,
			    'listing' => true,
			    'extra' => true,
			    'input' => false
			);
			$this->getAttribute($name)->setValueFromDatabase($value);
		}

		public function generateRandom()
		{
			foreach ($this->_infos['attributes'] as $name => $v) {
				if (!$v['calculated'])
					$this->getAttribute($name)->setValueFromInput($this->getAttribute($name)->generateRandom($this));
			}
			if ($this->hasParent())
				$this->getParent()->generateRandom();

			foreach ($this->_infos['associations'] as $name => $v) {

				$this->getAssociation($name)->generateRandom();
			}
		}

		public function getPolymorphismClass()
		{
			return isset($this->_child) ? $this->_child->getPolymorphismClass() : $this->_class;
		}

		public function getId()
		{
			return $this->isPersisted();
		}

		public function setId($id,$setter=null)
		{
			if($setter instanceof \CRUDsader\Object\UnitOfWork){
				$this->_isPersisted = $id;
			}else
			$this->_saveId = $id;
		}

		public function toHtml($base = false, $prefix = false, $allowedClasses = false, $displayTitle = true)
		{
			if (isset($allowedClasses[$this->_class]) && !$allowedClasses[$this->_class])
				return '';
			$html = '';
			if (!$prefix)
				$prefix = $this->_class;
			if (!$base) {
				$html.='<div class="object">';
				$nobase = true;
			}

			$base.= ( $base ? '.' : '') . 'object';
			if ($displayTitle)
				$html.='<div class="title">' . \CRUDsader\Instancer::getInstance()->i18n->translate($prefix . '.' . $base) . '</div>';
			foreach ($this->_fields as $name => $attribute) {
				if (($this->_infos['attributes'][$name]['html']))
					$html.='<div class="row"><div class="label">' . \CRUDsader\Instancer::getInstance()->i18n->translate($prefix . '.attributes.' . $name) . '</div><div class="value">' . ($attribute->isEmpty() ? '&nbsp;' : $attribute->toHtml()) . '</div></div>';
			}
			if ($this->hasParent())
				$html.=$this->getParent()->toHtml(false, $prefix, $allowedClasses, false);

			foreach ($this->_associations as $name => $association) {
				if (isset($allowedClasses[$name]) && !$allowedClasses[$name])
					continue;
				$html.='<div class="title">' . \CRUDsader\Instancer::getInstance()->i18n->translate($prefix . '.' . $name) . '</div>';
				$collection = $this->getAssociation($name);
				$first = true;
				foreach ($collection as $object) {
					if ($first)
						$first = false;
					else
						$html.='<div class="row">&nbsp;</div>';
					$html.='<div class="associated">' . $object->toHtml($base, $name, $allowedClasses, false) . '</div>';
				}
			}
			if (isset($nobase))
				$html.='</div>';
			return $html;
		}

		public function getInfos()
		{
			return $this->_infos;
		}

		public function getLinkedAssociation()
		{
			return $this->_linkedAssociation;
		}

		public function getLinkedAssociationId()
		{
			return $this->_linkedAssociationId;
		}

		public function __get($var)
		{
			if (!$this->_initialised && $this->_isPersisted)
				throw new ObjectException('Object is not initialised');
			switch (true) {

				case $this->hasAssociation($var):
					return $this->getAssociation($var);
					break;

				case isset($this->_infos['attributes'][$var]):
					return $this->getAttribute($var)->getValue();
					break;



				case $this->hasParent():
					if ($var == 'parent')
						return $this->getParent();
					return $this->getParent()->__get($var);
					break;
			}
			throw new ObjectException('Object has no attribute nor association named "' . $var . '"');
		}

		public function isModified()
		{
			return $this->_isModified;
		}

		public function __set($var, $value)
		{
			switch (true) {
				case isset($this->_infos['attributes'][$var]):
					$this->getAttribute($var)->setValueFromInput($value);
					if (($this->getAttribute($var)->isEmpty() && $this->_infos['attributes'][$var]['required']) || $this->getAttribute($var)->isValid() !== true) {
						// return to base value
						$this->getAttribute($var)->setValueFromInput(null);
						throw new ObjectException('attribute "' . $var . '" cannot accept "' . $value . '" as a value');
					}else
						$this->_initialised = true;
					break;
				case $this->hasParent():
					return $this->getParent()->__set($var, $value);
					break;
				default:
					throw new ObjectException('cannot set "' . $var . '"');
			}
		}

		/**
		 * save to db
		 * @param \CRUDsader\Object\UnitOfWork $unitOfWork 
		 */
		public function save(\CRUDsader\Object\UnitOfWork $unitOfWork = null)
		{

			if (!$this->_checkRequiredFields())
				throw new ObjectException($this->_class . '.error.fields-required');
			if ($this->_isModified || $this->_infos['definition']['abstract']) {

				if ($unitOfWork === null) {
					$unitOfWork = \CRUDsader\Instancer::getInstance()->{'object.unitOfWork'};
					$unitOfWorkToBeExecuted = true;
				}

				// update
				if ($this->hasParent())
					$this->getParent()->save($unitOfWork);

				$paramsToSave = $this->_getParamsForSave();

				// identity check
				$this->validateForSave();

				if (!$this->_checkIdentity())
					throw new ObjectException($this->_class . '.error.already-exists');
				$db = \CRUDsader\Instancer::getInstance()->database;

				if ($this->_isPersisted) {
					if ($unitOfWork->register($this->_class, $this->_isPersisted))
						if (!$this->_infos['definition']['abstract'])
							$unitOfWork->update($this->_infos['definition']['databaseTable'], $paramsToSave, $db->quoteIdentifier($this->_infos['definition']['databaseIdField']) . '=' . $this->_isPersisted);
				} else {
					$unitOfWork->insert($this->_infos['definition']['databaseTable'], $paramsToSave, $this);
					$unitOfWork->register($this->_class, $this->_isPersisted);
				}

				$this->saveAssociations($unitOfWork);
				if (isset($unitOfWorkToBeExecuted)) {
					\CRUDsader\Object\IdentityMap::add($this);
					$unitOfWork->commit();
				}
			}
		}

		protected function _checkRequiredFields()
		{
			foreach ($this->_infos['attributes'] as $attributeName => $attributeInfos) {
				if ($attributeInfos['required']) {
					if (!isset($this->_fields[$attributeName]) || $this->_fields[$attributeName]->isEmpty()) {

						return false;
					}
				}
			}
			return true;
		}

		public function isEmpty()
		{
			foreach ($this->_fields as $fieldName => $field) {
				if (!$field->isEmpty()) {
					if (!isset($this->_infos['attributes'][$fieldName]['extra']))
						return false;
				}
			}
			foreach ($this->_associations as $association)
				if (!$association->isEmpty())
					return false;
			if ($this->hasParent())
				return $this->getParent()->isEmpty();
			return true;
		}

		public function saveAssociations(\CRUDsader\Object\UnitOfWork $unitOfWork)
		{
			foreach ($this->_associations as $association)
				$association->save($unitOfWork);
		}

		public function validateForSave()
		{
			foreach ($this->_infos['attributes'] as $name => $attributeInfos) {
				if (!isset($this->_fields[$name]) && $attributeInfos['required'])
					throw new ObjectException('class needs attribute "' . $name . '" initialised');
				if (isset($attributeInfos['extra']))
					continue;
				$attribute = $this->getAttribute($name);
				if ($attribute->isEmpty() && $attributeInfos['required'])
					throw new ObjectException('class needs attribute "' . $name . '" to be filled');
				if (!$attribute->isEmpty() && !$attribute->isValid()) {
					throw new ObjectException('class needs attribute "' . $name . '" to be valid');
				}
			}
		}

		public function delete(\CRUDsader\Object\UnitOfWork $unitOfWork = null)
		{
			if ($unitOfWork === null) {
				$unitOfWork = \CRUDsader\Instancer::getInstance()->{'object.unitOfWork'};
				$unitOfWorkToBeExecuted = true;
			}
			if ($this->hasParent())
				$this->getParent()->delete($unitOfWork);
			if ($this->_isPersisted) {
				$db = \CRUDsader\Instancer::getInstance()->database;
				\CRUDsader\Object\IdentityMap::remove($this);
				$db->delete($this->_infos['definition']['databaseTable'], $db->quoteIdentifier($this->_infos['definition']['databaseIdField']) . '=' . $this->_isPersisted);
				$this->_isPersisted = false;
			}
			foreach ($this->_associations as $association)
				$association->delete($unitOfWork);
			if (isset($unitOfWorkToBeExecuted)) {
				$unitOfWork->commit();
			}
		}

		public function getDatabaseTable()
		{
			return $this->_infos['definition']['databaseTable'];
		}

		public function getDatabaseIdField()
		{
			return $this->_infos['definition']['databaseIdField'];
		}

		public function _getParamsForSave()
		{
			$ret = array();
			if (!$this->_isPersisted)
				$ret[$this->_infos['definition']['databaseIdField']] = isset($this->_parent) ? $this->_parent->_isPersisted : ($this->_saveId?$this->_saveId:\CRUDsader\Instancer::getInstance()->expression('NULL'));
			foreach ($this->_infos['attributes'] as $k => $attributeInfos) {
				if ($attributeInfos['calculated']) {
					$this->getAttribute($k)->setDefaultValue($ret[$attributeInfos['databaseField']] = $this->calculateAttribute($k));
				} else if (isset($this->_fields[$k]) && !isset($this->_infos['attributes'][$k]['extra'])) {
					$ret[$this->_infos['attributes'][$k]['databaseField']] = $this->_fields[$k]->getValueForDatabase();
				}
			}
			return $ret;
		}

		public function calculateAttribute($attributeName)
		{
			return $this->_fields[$attributeName]->getValueForDatabase();
		}

		/**
		 * check if an object with the same identity exists in database
		 * @return bool 
		 */
		protected function _checkIdentity()
		{
			if (empty($this->_infos['definition']['identity']))
				return $this->hasParent() ? $this->getParent()->_checkIdentity() : true;
			$db = \CRUDsader\Instancer::getInstance()->database;
			if ($this->_isPersisted)
				$where = array($db->quoteIdentifier($this->_infos['definition']['databaseIdField']) . '!=' . $db->quote($this->_isPersisted));
			else
				$where = array();
			foreach ($this->_infos['definition']['identity'] as $fieldName) {
				$empty = $this->getAttribute($fieldName)->isEmpty();
				if (!$this->_infos['attributes'][$fieldName]['calculated'] && $empty)
					throw new ObjectException('cannot check as attribute "' . $this->_class . '.' . $fieldName . '" is empty');
				if ($empty)
					$where[] = $db->quoteIdentifier($this->_infos['attributes'][$fieldName]['databaseField']) . ' IS NULL';
				else
					$where[] = $db->quoteIdentifier($this->_infos['attributes'][$fieldName]['databaseField']) . '=' . $db->quote($this->getAttribute($fieldName)->getValueForDatabase());
			}
			$ret = 0 == $db->countSelect(array('from' => array('table' => $this->_infos['definition']['databaseTable'], 'alias' => 't', 'id' => $this->_infos['definition']['databaseIdField']), 'where' => implode(' AND ', $where), 'limit' => array('count' => 1)));

			return $ret;
		}

		public function getCollectionOfObjectsWithSameIdentity()
		{
			if (empty($this->_infos['definition']['identity']))
				return false;
			$db = \CRUDsader\Instancer::getInstance()->database;
			if ($this->_isPersisted) {
				$where = array('o.id=?');
				$args = array(array('!=' => $this->_isPersisted));
			}else
				$args = $where = array();
			foreach ($this->_infos['definition']['identity'] as $fieldName) {
				$empty = $this->getAttribute($fieldName)->isEmpty();
				if (!$this->_infos['attributes'][$fieldName]['calculated'] && $empty)
					throw new ObjectException('cannot check as attribute "' . $this->_class . '.' . $fieldName . '" is empty');
				if ($empty) {
					$where[] = 'o.' . $fieldName . '=?';
					$args[] = null;
				} else {
					$where[] = 'o.' . $fieldName . '=?';
					$args[] = $this->getAttribute($fieldName)->getValueForDatabase();
				}
			}
			$ret = \CRUDsader\Instancer::getInstance()->query('FROM ' . $this->_class . ' o WHERE ' . implode(' AND ', $where))->fetchAll($args);
			return $ret;
		}

		public function isPersisted()
		{
			return $this->_isPersisted;
		}

		public function getClass()
		{
			return $this->_class;
		}

		public function getForm($oql = false, $alias = false, \CRUDsader\Form $form = null)
		{
			if (empty($alias))
				$alias = $this->_class;
			if ($form === null) {
				$form = \CRUDsader\Instancer::getInstance()->{'form'}($alias . $this->_isPersisted);
				$form->setInputRequired(true);
				$form->setHtmlLabel(false);
			}
			$this->_getFormAttributes($form, $oql, $alias);
			$this->_getFormAssociations($form, $oql, $alias);
			return $form;
		}

		protected function _getFormAssociations(\CRUDsader\Form $form, $oql = false, $alias = false)
		{
			if ($oql) {
				$l = strlen($alias) + 1;
				$query = \CRUDsader\Instancer::getInstance()->{'query'}($oql);
				$infos = $query->getInfos();
				foreach ($infos['mapFields'] as $oname => $useless) {
					if ($oname == $this->_class)
						continue;
					$name = substr($oname, $l);

					if ($name && $this->hasAssociation($name)) {
						$this->getAssociation($name)->getForm($oql, $this->_class . '.' . $name, $form);
					}
				}
				if ($this->hasParent())
					$this->getParent(true)->_getFormAssociations($form, $oql, $alias . '_parent');
			}else {
				foreach ($this->_associations as $name => $association)
					$this->getAssociation($name)->getForm($oql, $name, $form);
				if ($this->hasParent())
					$this->getParent(true)->_getFormAssociations($form, $oql, $alias . '_parent');
			}
		}

		protected function _getFormAttributes(\CRUDsader\Form $form, $oql = false, $alias = false)
		{
			foreach ($this->_infos['attributes'] as $name => $infoAttribute) {
				if ($infoAttribute['input']) {
					$form->add($this->getAttribute($name), $name, $infoAttribute['required']);
					$this->getAttribute($name)->setHtmlLabel(\CRUDsader\Instancer::getInstance()->i18n->translate($this->_class . '.attributes.' . $name));
				}
			}
			if ($this->hasParent())
				$this->getParent()->_getFormAttributes($form, $alias);
		}

		public function __toString()
		{
			$ret = array();
			foreach ($this->_fields as $name => $field) {
				$ret[] = $field->isEmpty() ? '' : $field->getValue();
			}
			return implode(',', $ret) . ($this->hasParent() ? ',' . $this->getParent()->__toString() : '');
		}

		public function hasParent()
		{
			return $this->_infos['inherit'];
		}

		public function getParent()
		{
			if (!isset($this->_parent) && $this->_infos['inherit']) {
				$this->_parent = \CRUDsader\Object::instance($this->_infos['inherit']);
				$this->_parent->_child = $this;
			}
			return $this->_parent;
		}

		public function isInitialised()
		{
			return $this->_initialised;
		}

		public function hasAssociation($associationName)
		{
			return isset($this->_infos['associations'][$associationName]);
		}

		public function getAssociation($associationName)
		{
			if (!isset($this->_associations[$associationName]))
				$this->_associations[$associationName] = \CRUDsader\Instancer::getInstance()->{'object.collection.association'}($this, $this->_infos['associations'][$associationName], $this->_class);
			return $this->_associations[$associationName];
		}

		public function toArray($full = false)
		{
			if ($full) {
				$ret = array('class' => $this->_class, 'initialised' => $this->_initialised ? 'yes' : 'no', 'persisted' => $this->_isPersisted);
				if ($this->_parent)
					$ret['parent'] = $this->_parent->toArray($full);
				foreach ($this->_fields as $name => $field)
					$ret['fields'][$name] = $field->toArray($full);
				if (!empty($this->_associations))
					foreach ($this->_associations as $name => $association)
						$ret['associations'][$name] = $association->toArray($full);
			}else {
				$ret = array('id' => $this->_isPersisted . '[' . $this->_class . ']' . ($this->_isModified ? '(modified)' : ''));
				foreach ($this->_fields as $name => $field)
					$ret[$name] = $field->getValue();
				if (!empty($this->_associations))
					foreach ($this->_associations as $name => $association)
						$ret[$name] = $association->toArray($full);
				if ($this->_parent)
					$ret['parent'] = $this->_parent->toArray($full);
			}
			return $ret;
		}

		public function toJson()
		{
			$ret = array();
			$ret['id'] = $this->_isPersisted;
			foreach ($this->_fields as $name => $field)
				if ($this->_infos['attributes'][$name]['json']) {
					$ret[$name] = $this->filter($field->getValue(), $name, 'json');
				}
			if (!empty($this->_associations))
				foreach ($this->_associations as $name => $association)
					$ret[$name] = $association->toJson();
			if ($this->_parent)
				$ret[$this->_parent->getClass()] = $this->_parent->toJson();
			return $ret;
		}

		public function filter($value, $name, $from)
		{
			return $value;
		}

		public function hasAttribute($name)
		{
			return isset($this->_infos['attributes'][$name]);
		}

		public function get($attributeName, $valueIsEmpty = false)
		{
			$v = $this->$attributeName;
			return !isset($v) ? $valueIsEmpty : $v;
		}

		public function getAttribute($name)
		{
			if (!isset($this->_fields[$name]) && isset($this->_infos['attributes'][$name]) && isset($this->_infos['attributes'][$name]['extra']) && $this->_infos['attributes'][$name]['extra']) {
				$type = strpos($name,'_id')!==false?$this->_map->classGetFieldAttributeIdType():$this->_map->classGetFieldAttributeDefaultType();
				$class = $type['phpNamespace'] . $type['class'];
				$this->_fields[$name] = new $class('default');
				$this->_fields[$name]->attach($this);
			} else if (!isset($this->_fields[$name])) {
				$type = $this->_map->classGetFieldAttributeType($this->_class, $name);
				$class = $type['phpNamespace'] . $type['class'];
				$this->_fields[$name] = new $class($name, $type['options']);
				$this->_fields[$name]->attach($this);
				if (isset($this->_infos['attributes'][$name]['default']))
					$this->_fields[$name]->setDefaultValue($this->_infos['attributes'][$name]['default']);
			}
			return $this->_fields[$name];
		}

		// ** INTERFACE ** SplObserver
		/**
		 * @param \SplSubject $subject 
		 */
		public function update(\SplSubject $subject)
		{

			if (($subject instanceof \CRUDsader\Object\Attribute && $subject->isModified()) || $subject instanceof \CRUDsader\Object) {
				$this->_isModified = true;
				$this->_initialised = true;
				if ($this->_linkedAssociation)
					$this->_linkedAssociation->update($this);
				if (isset($this->_child))
					$this->_child->update($this);
			}
		}

		// ** INTERFACE ** SplSubject
		/**
		 * start being observerd by this object
		 * @param \SplObserver $observer 
		 */
		public function attach(\SplObserver $observer)
		{
			$this->_observers[spl_object_hash($observer)] = $observer;
		}

		/**
		 * stop being observed by this object
		 * @param \SplObserver $observer 
		 */
		public function detach(\SplObserver $observer)
		{
			unset($this->_observers[spl_object_hash($observer)]);
		}

		/**
		 * notify all observers that we have been updated
		 */
		public function notify()
		{
			foreach ($this->_observers as $observer)
				$observer->update($this);
		}

		/**
		 * forbid cloning
		 * @final
		 * @access private
		 */
		final private function __clone()
		{
			
		}
	}
	class ObjectException extends \Exception {
		
	}
}