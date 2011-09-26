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
    class Object implements Interfaces\Initialisable, Interfaces\Arrayable, \SplObserver, \SplSubject {
        protected $_class;
        protected $_map;
        protected $_initialised = false;
        protected $_isModified = false;
        protected $_linkedAssociation = false;
        protected $_linkedAssociationId = false;
        protected $_parent;
        protected $_isPersisted = false;
        protected $_infos;
        protected $_fields = array();
        protected $_associations = array();
        protected $_observers = array();

        public function __construct($className) {
            $this->_class = $className;
            $this->_map = \Art\Map::getInstance();
            $this->_infos = $this->_map->classGetInfos($this->_class);
            if (!Map::getInstance()->classExists($className))
                throw new ObjectException('class "' . $className . '" does not exist');
        }

        public function getLinkedAssociation() {
            return $this->_linkedAssociation;
        }
        
         public function getLinkedAssociationId() {
            return $this->_linkedAssociationId;
        }

        public function __get($var) {
            if (!$this->_initialised)
                throw new ObjectException('Object is not initialised');
            switch (true) {
                case isset($this->_infos['attributes'][$var]):
                    return $this->getAttribute($var)->getValue();
                    break;
                case $this->hasAssociation($var):
                    return $this->getAssociation($var);
                    break;
                case $this->hasParent():
                    if ($var == 'parent')
                        return $this->getParent();
                    return $this->getParent()->__get($var);
                    break;
            }
            throw new ObjectException('Object hass no attribute or association named "' . $var . '"');
        }

        public function isModified() {
            return $this->_isModified;
        }

        public function __set($var, $value) {
            switch (true) {
                case isset($this->_infos['attributes'][$var]):
                    $this->getAttribute($var)->receiveInput($value);
                    if (($this->getAttribute($var)->inputEmpty() && $this->_infos['attributes'][$var]['required']) || $this->getAttribute($var)->inputValid() !== true) {
                        // return to base value
                        $this->getAttribute($var)->receiveInput(null);
                        throw new ObjectException('attribute "' . $var . '" cannot accept "' . $value . '" as a value');
                    }
                    break;
                case $this->hasParent():
                    return $this->getParent()->__set($var, $value);
                    break;
            }
        }

        public function save(\Art\Object\UnitOfWork $unitOfWork=null) {
            if ($this->_isModified) {
                if ($unitOfWork === null) {
                    $unitOfWork = new \Art\Object\UnitOfWork();
                    $unitOfWorkToBeExecuted = true;
                }
                // identity check
                if (!$this->_checkIdentity()) {
                    throw new ObjectException($this->_class . '_already_exists');
                }
                // update
                $paramsToSave = $this->_getParamsForSave();
                if ($this->_isPersisted) {
                    $db = \Art\Database::getInstance();
                    \Art\Object\IdentityMap::add($this);
                } else
                    $paramsToSave[$this->_infos['definition']['databaseIdField']] = \Art\Adapter::factory('identifier')->getOID();
                if (!empty($paramsToSave))
                    $unitOfWork->update($this->_infos['definition']['databaseTable'], $paramsToSave, $db->quoteIdentifier($this->_infos['definition']['databaseIdField']) . '=' . $this->_isPersisted);
                if ($this->hasParent())
                    $this->getParent()->save($unitOfWork);
                foreach ($this->_associations as $association)
                    $association->save($unitOfWork);
                if (isset($unitOfWorkToBeExecuted)) {
                    $unitOfWork->execute();
                }
            }
        }

        public function delete(\Art\Object\UnitOfWork $unitOfWork=null) {
            if ($unitOfWork === null) {
                $unitOfWork = new \Art\Object\UnitOfWork();
                $unitOfWorkToBeExecuted = true;
            }
            if ($this->_isPersisted) {
                $db = \Art\Database::getInstance();
                \Art\Object\IdentityMap::remove($this);
                $db->delete($this->_infos['definition']['databaseTable'],$db->quoteIdentifier($this->_infos['definition']['databaseIdField']) . '=' . $this->_isPersisted);
            }
            foreach ($this->_associations as $association)
                    $association->delete($unitOfWork);
            if (isset($unitOfWorkToBeExecuted)) {
                $unitOfWork->execute();
            }
        }

        public function getDatabaseTable() {
            return $this->_infos['definition']['databaseTable'];
        }

        public function _getParamsForSave() {
            $ret = array();
            foreach ($this->_fields as $k => $attribute) {
                $ret[$this->_infos['attributes'][$k]['databaseField']] = $attribute->getValueForDatabase();
            }
            return $ret;
        }

        /**
         * check if an object with the same identity exists in database
         * @return bool 
         */
        protected function _checkIdentity() {
            if (empty($this->_infos['definition']['identity']))
                return true;
            $db = \Art\Database::getInstance();
            if ($this->_isPersisted)
                $where = array($db->quoteIdentifier($this->_infos['definition']['databaseIdField']) . '!=' . $db->quote($this->_isPersisted));
            else
                $where = array();
            foreach ($this->_infos['definition']['identity'] as $fieldName) {
                if ($this->getAttribute($fieldName)->inputEmpty())
                    throw new ObjectException('cannot save as attribute "' . $fieldName . '" is empty');
                $where[] = $db->quoteIdentifier($this->_infos['attributes'][$fieldName]['databaseField']) . '=' . $db->quote($this->getAttribute($fieldName)->getValueForDatabase());
            }
            $query = $db->countSelect(array('from' => array('table' => $this->_infos['definition']['databaseTable'], 'alias' => 't', 'id' => $this->_infos['definition']['databaseIdField']), 'where' => implode(' AND ', $where), 'limit' => array('count' => 1)));
            $countRow = $query->current();
            return $countRow[0] == 0;
        }

        public function isPersisted() {
            return $this->_isPersisted;
        }

        public function getClass() {
            return $this->_class;
        }

        public function getForm($oql=false, $alias=false, \Art\Form $form=null) {
            if (empty($alias))
                $alias = $this->_class;
            if ($form === null) {
                $form = new \Art\Form();
                $form->setHtmlLabel($alias);
            }
            $this->_getFormAttributes($form, $oql, $alias);
            $this->_getFormAssociations($form, $oql, $alias);
            return $form;
        }

        protected function _getFormAssociations(\Art\Form $form, $oql=false, $alias=false) {
            if ($oql) {
                $l = strlen($alias) + 1;
                $query = new \Art\Query($oql);
                $infos = $query->getInfos();
                foreach ($infos['mapFields'] as $oname => $useless) {
                    if ($oname == $this->_class)
                        continue;
                    $name = substr($oname, $l);
                    if ($name && $this->hasAssociation($name)) {
                        $this->getAssociation($name)->getForm($oql, $alias . '_' . $name, $form);
                    }
                }
                if ($this->hasParent())
                    $this->getParent(true)->_getFormAssociations($form, $oql, $alias . '_parent');
            }else{
                foreach($this->_associations as $name=>$association)
                    $this->getAssociation($name)->getForm($oql, $alias . '_' . $name, $form);
                  if ($this->hasParent())
                    $this->getParent(true)->_getFormAssociations($form, $oql, $alias . '_parent');
            }
        }

        public function __toString() {
            $ret = '';
            foreach ($this->_fields as $name => $field) {
                $ret.=$field->getValue();
            }
            return $ret;
        }

        protected function _getFormAttributes(\Art\Form $form, $oql=false, $alias=false) {
            foreach ($this->_infos['attributes'] as $name => $infoAttribute) {
                if (!$infoAttribute['calculated']) {
                    $form->add($this->getAttribute($name), $name, $infoAttribute['required']);
                    $this->getAttribute($name)->setHtmlLabel($alias ? $alias . '_' . $name : $this->_class . '_' . $name);
                }
            }
            if ($this->hasParent())
                $this->getParent()->_getFormAttributes($form, $alias);
        }

        public function hasParent() {
            return $this->_infos['inherit'];
        }

        public function getParent() {
            if (!isset($this->_parent) && $this->_infos['inherit']) {
                $this->_parent = new \Art\Object($this->_infos['inherit']);
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
                $this->_associations[$associationName] = new \Art\Object\Collection\Association($this, $this->_infos['associations'][$associationName], $this->_class);
            return $this->_associations[$associationName];
        }

        public function toArray($full=false) {
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
                    $ret[$name] = (string) $field->getValue();
                if (!empty($this->_associations))
                    foreach ($this->_associations as $name => $association)
                        $ret[$name] = $association->toArray($full);
                if ($this->_parent)
                    $ret['parent'] = $this->_parent->toArray($full);
            }
            return $ret;
        }

        public function getAttribute($name) {
            if (!isset($this->_fields[$name])) {
                $type = $this->_map->classGetFieldAttributeType($this->_class, $name);
                $this->_fields[$name] = new Object\Attribute($name, $type['class'], $type['options']);
                $this->_fields[$name]->attach($this);
            }
            return $this->_fields[$name];
        }

        // ** INTERFACE ** SplObserver
        /**
         * @todo more test, such as if it's really the parent
         * @param \SplSubject $subject 
         */
        public function update(\SplSubject $subject) {
            if ($subject instanceof \Art\Object\Attribute || $subject instanceof \Art\Object) {
                $this->_isModified = true;
                if ($this->_linkedAssociation)
                    $this->_linkedAssociation->update($this);
                if ($this->hasParent())
                    $this->getParent()->update($this);
            }
        }

        // ** INTERFACE ** SplSubject
        /**
         * start being observerd by this object
         * @param \SplObserver $observer 
         */
        public function attach(\SplObserver $observer) {
            $this->_observers[spl_object_hash($observer)] = $observer;
        }

        /**
         * stop being observed by this object
         * @param \SplObserver $observer 
         */
        public function detach(\SplObserver $observer) {
            unset($this->_observers[spl_object_hash($observer)]);
        }

        /**
         * notify all observers that we have been updated
         */
        public function notify() {
            foreach ($this->_observers as $observer)
                $observer->update($this);
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