<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Object\Collection {
    class Association extends \CRUDsader\Object\Collection implements \SplObserver {
        protected $_isModified = false;
        protected $_definition;
        protected $_linkedObject;
        protected $_fromClass;
        protected $_formValues = array();
        protected $_objectsToBeDeleted = array();

        public function __construct(\CRUDsader\Object $object, $definition, $fromClass) {
            parent::__construct($definition['to']);
            $this->_linkedObject = $object;
            $this->_definition = $definition;
            $this->_fromClass = $fromClass;
        }
	
	public function toJson(){
	    $ret = parent::toJson();
	    if($this->_definition['reference'] == 'internal' || $this->_definition['max'] == 1)
		    return count($ret)? current($ret) : null;
            return $ret;
	}

        public function offsetSet($index, $value) {
            if (!isset($this->_objects[$index]) && $this->_definition['max'] != '*' && $this->_iterator == $this->_definition['max'])
                throw new AssociationException('association "' . $this->_definition['to'] . '" cannot have more than "' . $this->_definition['max'] . '" objects');
            $value = parent::offsetSet($index, $value);
            \CRUDsader\Object\Writer::linkToAssociation($value, $this);
            \CRUDsader\Object\Writer::setModified($this->_linkedObject);
            $this->_isModified = true;
            return $value;
        }

        public function generateRandom() {
            for ($i = 0; $i < rand((int) $this->_definition['min'], (int) $this->_definition['max']); $i++) {
                if ($this->_definition['composition']) {
                    $this->newObject()->generateRandom();
                    $this->_isModified = true;
                } else {
                    $query = \CRUDsader\Instancer::getInstance()->query('FROM ' . $this->_definition['to'] . ' ORDER BY tools.rand LIMIT 1');
                    $found = $query->fetch();
                    if ($found) {
                        $this->_objects[$i] = $found;
                        $this->_isModified = true;
                    }
                }
            }
        }

        /**
         * @return type 
         */
        public function newObject() {
            if ($this->_definition['max'] != '*' && $this->_iterator == $this->_definition['max'])
                throw new AssociationException('association cannot have more than "' . $this->_definition['max'] . '" objects');
            $object = parent::newObject();
            \CRUDsader\Object\Writer::linkToAssociation($object, $this);
            return $object;
        }

        public function getDefinition() {
            return $this->_definition;
        }

        public function save(\CRUDsader\Object\UnitOfWork $unitOfWork=null) {
            // check
            $cnt = 0;
            $db = \CRUDsader\Instancer::getInstance()->database;
            if ($this->_isModified) {
                foreach ($this->_objects as $index => $object) {
                    if (isset($this->_objectsToBeDeleted[$index])) {
                        switch ($this->_definition['reference']) {
                            case 'internal':
                                $unitOfWork->update($this->_linkedObject->getDatabaseTable(), array($this->_definition['internalField'] => new \CRUDsader\Expression\Nil), $db->quoteIdentifier($this->_definition['internalField']) . '=' . $db->quote($this->_linkedObject->isPersisted()));
                                break;
                            case 'external':
                                // in the $object, so it's going to get erased anyway
                                break;
                            default:
                                $d = array(
                                    $db->quoteIdentifier($this->_definition['externalField']) . '=' . $db->quote($object->isPersisted()),
                                    $db->quoteIdentifier($this->_definition['internalField']) . '=' . $db->quote($this->_linkedObject->isPersisted())
                                );
                                $unitOfWork->delete($this->_definition['databaseTable'], implode(' AND ', $d));
                        }
                        $object->delete($unitOfWork);
                        continue;
                    }
                    if ($object->isEmpty()) {
                        $object->delete($unitOfWork);
                    } else {
                        $cnt++;
                        switch ($this->_definition['reference']) {
                            case 'internal':
                                $object->save($unitOfWork);
                                $unitOfWork->update($this->_linkedObject->getDatabaseTable(), array($this->_definition['internalField'] => $object->isPersisted()), 'id=' . $this->_linkedObject->isPersisted());
                                break;
                            case 'external':
                                $infos = $object->getInfos();
                                $object->save($unitOfWork);
                                if ($object->isPersisted())
                                    $unitOfWork->update($object->getDatabaseTable(), array($this->_definition['externalField'] => $this->_linkedObject->isPersisted()), $db->quoteIdentifier($infos['definition']['databaseIdField']) . '=' . $object->isPersisted());
                                break;
                            default:
                                $object->save($unitOfWork);
                                if ($this->_linkedObject->isPersisted() && $object->isPersisted()) {
                                    if ($object instanceof \CRUDsader\Object\Proxy) {
                                        $unitOfWork->delete($this->_definition['databaseTable'], $db->quoteIdentifier($this->_definition['externalField']) . '=' . $db->quote($object->isPersisted()) . ' AND ' . $db->quoteIdentifier($this->_definition['internalField']) . '=' . $db->quote($this->_linkedObject->isPersisted()));
                                        $unitOfWork->insert($this->_definition['databaseTable'], array(
                                            'id' => \CRUDsader\Instancer::getInstance()->{'object.identifier'}->getOID(array('class' => $this->_class)),
                                            $this->_definition['internalField'] => $this->_linkedObject->isPersisted(),
                                            $this->_definition['externalField'] => $object->isPersisted()
                                        ));
                                    } else {
                                        $d = array(
                                            $this->_definition['internalField'] => $this->_linkedObject->isPersisted(),
                                            $this->_definition['externalField'] => $object->isPersisted()
                                        );
                                        if ($object->isPersisted() && $object->getLinkedAssociationId()) {
                                            // update   
                                            $unitOfWork->update($this->_definition['databaseTable'], $d, $db->quoteIdentifier($this->_definition['databaseIdField']) . '=' . $db->quote($object->getLinkedAssociationId()));
                                        } else {
                                            $d['id'] = \CRUDsader\Instancer::getInstance()->{'object.identifier'}->getOID(array('class' => $this->_class));
                                            $unitOfWork->delete($this->_definition['databaseTable'], $db->quoteIdentifier($this->_definition['externalField']) . '=' . $db->quote($object->isPersisted()) . ' AND ' . $db->quoteIdentifier($this->_definition['internalField']) . '=' . $db->quote($this->_linkedObject->isPersisted()));
                                            $unitOfWork->insert($this->_definition['databaseTable'], $d);
                                            \CRUDsader\Object\Writer::setLinkedAssociationId($object, $d['id']);
                                        }
                                    }
                                }
                        }
                    }
                }
                if ($this->_definition['max'] != '*' && $cnt > $this->_definition['max'])
                    throw new AssociationException('error.association.save.max');
                if ($cnt < $this->_definition['min'])
                    throw new AssociationException('error.association.save.min');
            }
        }

        public function delete(\CRUDsader\Object\UnitOfWork $unitOfWork=null) {
            if ($unitOfWork === null)
                throw new AssociationException('no UnitOfWork');
            $db = \CRUDsader\Instancer::getInstance()->database;
            foreach ($this->_objects as $object) {
                if (!$object->isPersisted())
                    continue;
                switch ($this->_definition['reference']) {
                    case 'internal':
                        $unitOfWork->update($this->_linkedObject->getDatabaseTable(), array($this->_definition['internalField'] => new \CRUDsader\Expression\Nil), $db->quoteIdentifier($this->_definition['internalField']) . '=' . $db->quote($this->_linkedObject->isPersisted()));
                        break;
                    case 'external':
                        // in the $object, so it's going to get erased anyway
                        break;
                    default:
                        $d = array(
                            $db->quoteIdentifier($this->_definition['externalField']) . '=' . $db->quote($object->isPersisted()),
                            $db->quoteIdentifier($this->_definition['internalField']) . '=' . $db->quote($this->_linkedObject->isPersisted())
                        );
                        $unitOfWork->delete($this->_definition['databaseTable'], implode(' AND ', $d));
                }
                if ($this->_definition['composition'])
                    $object->delete($unitOfWork);
            }
            $this->_objects = array();
            $this->_objectIndexes = array();
        }

        public function isModified() {
            return $this->_isModified;
        }

        public function getLinkedObject() {
            return $this->_linkedObject;
        }

        public function getForm($oql=false, $alias=false, \CRUDsader\Form $form=null) {
            if (empty($alias))
                $alias = $this->_class;
            $this->_initialised = true;
            $formAssociation = $form->add(new \CRUDsader\Form($alias), $this->_definition['name'] ? $this->_definition['name'] : $this->_definition['to']);
            $formAssociation->setHtmlLabel(\CRUDsader\Instancer::getInstance()->i18n->translate($alias));
            $max = $this->_definition['max'] == '*' ? 3 : $this->_definition['max'];
            if ($this->_definition['min'] > $max)
                $max = $this->_definition['min'];
            $this->rewind();
            $this->_formValues = array();
            for ($i = $this->_definition['min']; $i < $max; $i++) {
                if (!$this->valid()) {
                    $object = $this->_objects[$this->_iterator] = \CRUDsader\Object::instance($this->_class);
                    \CRUDsader\Object\Writer::linkToAssociation($object, $this);
                } else {
                    $object = $this->current();
                }
                if ($this->_definition['composition']) {
                    $form2 = $formAssociation->add(new \CRUDsader\Form());
                    $form2->setHtmlLabel(false);
                    $object->getForm($oql, $alias, $form2);
                } else {
                    $class = \CRUDsader\Instancer::getInstance()->configuration->map->defaults->associations->associationComponentSelectClass;
                    $component = $formAssociation->add(new $class(array('class' => $this->_class)), $i, false);
                    $component->setHtmlLabel($i == 0 ? \CRUDsader\Instancer::getInstance()->i18n->translate($alias) : ' ');
                    $component->setParameter('compositionIndex', $this->_iterator);
                    if ($object->isPersisted())
                        $component->inputReceive($object->isPersisted());
                    $component->attach($this);
                }
                $this->next();
            }
        }

        /**
         * @todo erase empty ones!
         * @param \SplSubject $component 
         */
        public function update(\SplSubject $component) {
            if ($component instanceof \CRUDsader\Form\Component && $component->hasParameter('compositionIndex')) {
                $index = $component->getParameter('compositionIndex');
                $value = $component->getInputValue();
                $empty = $component->inputEmpty();
                if (!$empty && isset($this->_formValues[$value]))
                    throw new AssociationException($this->_class . '_duplicates');
                $target = $this->_objects[$index];
                // has the target changed ???
                if ($target->isPersisted()) {
                    if ($empty) {// delete the object. in DB as well
                        $this->_objectsToBeDeleted[$index] = true;
                        $this->_isModified = true;
                    } else
                    if ($value != $target->isPersisted()) {// replace by proxy
                        unset($this->_objects[$index]);
                        if (isset($this->_objectIndexes[$target->isPersisted()]))
                            unset($this->_objectIndexes[$target->isPersisted()]);
                        $this->_objects[$index] = \CRUDsader\Instancer::getInstance()->{'object.proxy'}($this->_class, $component->getInputValue());
                        $this->_isModified = true;
                    }
                }else if ($empty) {// delete object
                    unset($this->_objects[$index]);
                } else {
                    // replace by proxy
                    unset($this->_objects[$index]);
                    if (isset($this->_objectIndexes[$target->isPersisted()]))
                        unset($this->_objectIndexes[$target->isPersisted()]);
                    $this->_objects[$index] = \CRUDsader\Instancer::getInstance()->{'object.proxy'}($this->_class, $component->getInputValue());
                    $this->_isModified = true;
                }
            }
            if ($component instanceof \CRUDsader\Object) {
                \CRUDsader\Object\Writer::setModified($this->_linkedObject);
                $this->_isModified = true;
            }
        }

        public function rewind() {
            if (!$this->_initialised)
                throw new AssociationException('collection is not initialised');
            parent::rewind();
        }
    }
    class AssociationException extends \CRUDsader\Exception {
        
    }
}