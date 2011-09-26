<?php
namespace Art\Object\Collection {
    class Association extends \Art\Object\Collection implements \SplObserver {
        protected $_isModified = false;
        protected $_definition;
        protected $_linkedObject;
        protected $_formValues=array();
        protected $_objectsToBeDeleted = array();

        public function __construct(\Art\Object $object, $definition, $fromClass) {
            $this->_linkedObject = $object;
            $this->_definition = $definition;
            $this->_fromClass = $fromClass;
            $this->_class = $definition['to'];
        }

        public function getDefinition() {
            return $this->_definition;
        }

        public function save(\Art\Object\UnitOfWork $unitOfWork=null) {
            $db = \Art\Database::getInstance();
            if ($this->_isModified) {
                foreach ($this->_objects as $object) {
                    switch ($this->_definition['reference']) {
                        case 'internal':
                            $object->save($unitOfWork);
                            $unitOfWork->update($this->_linkedObject->getDatabaseTable(), array($this->_definition['internalField'] => $object->isPersisted()));
                            break;
                        case 'external':
                            $object->save($unitOfWork);
                            $unitOfWork->update($object->getDatabaseTable(), array($this->_definition['externalField'] => $this->_linkedObject->isPersisted()));
                            break;
                        default:
                            $persisted = $object->isPersisted();
                            $object->save($unitOfWork);
                            if ($object instanceof \Art\Object\Proxy) {
                                $unitOfWork->delete($this->_definition['databaseTable'], $db->quoteIdentifier($this->_definition['externalField']) . '=' . $db->quote($object->isPersisted()). ' AND '. $db->quoteIdentifier($this->_definition['internalField']) . '=' . $db->quote($this->_linkedObject->isPersisted()));    
                                $unitOfWork->insert($this->_definition['databaseTable'], array(
                                    'id' => \Art\Adapter::factory('identifier')->getOID(array('class' => $this->_class)),
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
                                    $d['id'] = \Art\Adapter::factory('identifier')->getOID(array('class' => $this->_class));
                                    $unitOfWork->delete($this->_definition['databaseTable'], $db->quoteIdentifier($this->_definition['externalField']) . '=' . $db->quote($object->isPersisted()). ' AND '. $db->quoteIdentifier($this->_definition['internalField']) . '=' . $db->quote($this->_linkedObject->isPersisted()));
                                    $unitOfWork->insert($this->_definition['databaseTable'], $d);
                                    \Art\Object\Writer::setLinkedAssociationId($object, $d['id']);
                                }
                            }
                    }
                }
            }
        }

        public function delete(\Art\Object\UnitOfWork $unitOfWork=null) {
            $db = \Art\Database::getInstance();
            foreach ($this->_objects as $object) {
                switch ($this->_definition['reference']) {
                    case 'internal':
                        if ($this->_definition['composition'])
                            $object->delete($unitOfWork);
                        $unitOfWork->update($this->_linkedObject->getDatabaseTable(), array($this->_definition['internalField'] => new \Art\Expression\Nil));
                        break;
                    case 'external':
                        if ($this->_definition['composition'])
                            $object->delete($unitOfWork);
                        $unitOfWork->update($object->getDatabaseTable(), array($this->_definition['externalField'] => new \Art\Expression\Nil));
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

        /**
         * @todo check max
         * @return type 
         */
        public function newObject() {
            // check max
            $object = parent::newObject();
            \Art\Object\Writer::linkToAssociation($object, $this);
            return $object;
        }

        public function getForm($oql=false, $alias=false, \Art\Form $form=null) {
            if (empty($alias))
                $alias = $this->_class;
            $formAssociation = $form->add(new \Art\Form($alias));
            $formAssociation->setHtmlLabel($alias);
            $max = $this->_definition['max'] == '*' ? 3 : $this->_definition['max'];
            $this->rewind();
            $this->_formValues=array();
            for ($i = 0; $i < $max; $i++) {
                if (!$this->valid()) {
                    $object = $this->_objects[$this->_iterator] = new \Art\Object($this->_class);
                } else {
                    $object = $this->current();
                }
                if ($this->_definition['composition']) {
                    $object->getForm($oql, $alias, $formAssociation);
                } else {
                    $component = $formAssociation->add(new \Art\Form\Component\Composition(array('class' => $this->_class)), $i, false);
                    $component->setHtmlLabel($i == 0 ? $alias : ' ');
                    $component->setParameter('compositionIndex', $this->_iterator);
                    if ($object->isPersisted())
                        $component->receiveInput($object->isPersisted());
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
            if ($component instanceof \Art\Form\Component && !$component->inputEmpty() && $component->hasParameter('compositionIndex')) {
                // replace actual by proxy
                $index = $component->getParameter('compositionIndex');
                $value=$component->getInputValue();
                if(isset($this->_formValues[$value]))
                        throw new AssociationException($this->_class.'_duplicates');                
                        $this->_formValues[$value]=$index;
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
                        $this->_objects[$index] = new \Art\Object\Proxy($this->_class, $component->getInputValue());
                    }
                }else
                    $this->_objects[$index] = new \Art\Object\Proxy($this->_class, $component->getInputValue());
                $this->_isModified = true;
            }
            if ($component instanceof \Art\Object) {
                \Art\Object\Writer::setModified($this->_linkedObject);
                $this->_isModified = true;
            }
        }
    }
    class AssociationException extends \Art\Exception{}
}