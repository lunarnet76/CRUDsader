<?php
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

        public function offsetSet($index, $value) {
            if ($this->_iterator == $this->_definition['max'])
                throw new AssociationException('association cannot have more than "' . $this->_definition['max'] . '" objects');
            $value = parent::offsetSet($index, $value);
            \CRUDsader\Object\Writer::linkToAssociation($value, $this);
        }

        /**
         * @return type 
         */
        public function newObject() {
            if ($this->_iterator == $this->_definition['max'])
                throw new AssociationException('association cannot have more than "' . $this->_definition['max'] . '" objects');
            $object = parent::newObject();
            \CRUDsader\Object\Writer::linkToAssociation($object, $this);
            return $object;
        }

        public function getDefinition() {
            return $this->_definition;
        }

        public function save(\CRUDsader\Object\UnitOfWork $unitOfWork=null) {
            $db = \CRUDsader\Database::getInstance();
            if ($this->_isModified) {
                foreach ($this->_objects as $object) {
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
                                        'id' => \CRUDsader\Adapter::factory('identifier')->getOID(array('class' => $this->_class)),
                                        $this->_definition['externalField'] => $object->isPersisted(),
                                        $this->_definition['internalField'] => $this->_linkedObject->isPersisted()
                                    ));
                                } else {
                                    $d = array(
                                        $this->_definition['externalField'] => $object->isPersisted(),
                                        $this->_definition['internalField'] => $this->_linkedObject->isPersisted()
                                    );
                                    if ($object->isPersisted() && $object->getLinkedAssociationId()) {
                                        // update
                                        $unitOfWork->update($this->_definition['databaseTable'], $d, $db->quoteIdentifier($this->_definition['databaseIdField']) . '=' . $db->quote($object->getLinkedAssociationId()));
                                    } else {
                                        $d['id'] = \CRUDsader\Adapter::factory('identifier')->getOID(array('class' => $this->_class));
                                        $unitOfWork->delete($this->_definition['databaseTable'], $db->quoteIdentifier($this->_definition['externalField']) . '=' . $db->quote($object->isPersisted()) . ' AND ' . $db->quoteIdentifier($this->_definition['internalField']) . '=' . $db->quote($this->_linkedObject->isPersisted()));
                                        $unitOfWork->insert($this->_definition['databaseTable'], $d);
                                        \CRUDsader\Object\Writer::setLinkedAssociationId($object, $d['id']);
                                    }
                                }
                            }
                    }
                }
            }
        }

        public function delete(\CRUDsader\Object\UnitOfWork $unitOfWork=null) {
            $db = \CRUDsader\Database::getInstance();
            foreach ($this->_objects as $object) {
                switch ($this->_definition['reference']) {
                    case 'internal':
                        if ($this->_definition['composition'])
                            $object->delete($unitOfWork);
                        $unitOfWork->update($this->_linkedObject->getDatabaseTable(), array($this->_definition['internalField'] => new \CRUDsader\Expression\Nil));
                        break;
                    case 'external':
                        if ($this->_definition['composition'])
                            $object->delete($unitOfWork);
                        $unitOfWork->update($object->getDatabaseTable(), array($this->_definition['externalField'] => new \CRUDsader\Expression\Nil));
                        break;
                    default:
                        $d = array(
                            $db->quoteIdentifier($this->_definition['externalField']) . '=' . $db->quote($object->isPersisted()),
                            $db->quoteIdentifier($this->_definition['internalField']) . '=' . $db->quote($this->_linkedObject->isPersisted())
                        );
                        $unitOfWork->delete($this->_definition['databaseTable'], implode(' AND ', $d));
                        if ($this->_definition['composition'])
                            $object->delete($unitOfWork);
                }
            }
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
            $formAssociation = $form->add(new \CRUDsader\Form($alias));
            $formAssociation->setHtmlLabel(\CRUDsader\I18n::getInstance()->translate($alias));
            $max = $this->_definition['max'] == '*' ? 3 : $this->_definition['max'];
            if ($this->_definition['min'] > $max)
                $max = $this->_definition['min'];
            $this->rewind();
            $this->_formValues = array();
            for ($i = 0; $i < $max; $i++) {
                if (!$this->valid()) {
                    $object = $this->_objects[$this->_iterator] = new \CRUDsader\Object($this->_class);
                    \CRUDsader\Object\Writer::linkToAssociation($object, $this);
                } else {
                    $object = $this->current();
                }
                if ($this->_definition['composition']) {
                    $form2 = $formAssociation->add(new \CRUDsader\Form($i), $i);
                    $form2->setHtmlLabel(false);
                    $object->getForm($oql, $alias, $form2);
                } else {
                    $component = $formAssociation->add(new \CRUDsader\Form\Component\Composition(array('class' => $this->_class)), $i, false);
                    $component->setHtmlLabel($i == 0 ? $alias : ' ');
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
            if ($component instanceof \CRUDsader\Form\Component && !$component->inputEmpty() && $component->hasParameter('compositionIndex')) {
                // replace actual by proxy
                $index = $component->getParameter('compositionIndex');
                $value = $component->getInputValue();
                if (isset($this->_formValues[$value]))
                    throw new AssociationException($this->_class . '_duplicates');
                $this->_formValues[$value] = $index;
                $target = $this->_objects[$index];
                $id = $target->isPersisted();
                if ($id) {
                    if ($id == $value) {
                        // do nothing, nothing has changed
                    } else {
                        $this->_objectsToBeDeleted[] = $target;
                        unset($this->_objects[$index]);
                        if (isset($this->_objectIndexes[$id]))
                            unset($this->_objectIndexes[$id]);
                        $this->_objects[$index] = new \CRUDsader\Object\Proxy($this->_class, $component->getInputValue());
                    }
                }else
                    $this->_objects[$index] = new \CRUDsader\Object\Proxy($this->_class, $component->getInputValue());
                $this->_isModified = true;
            }
            if ($component instanceof \CRUDsader\Object) {
                \CRUDsader\Object\Writer::setModified($this->_linkedObject);
                $this->_isModified = true;
            }
        }
    }
    class AssociationException extends \CRUDsader\Exception {
        
    }
}