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

		public function __construct(\CRUDsader\Object $object, $definition, $fromClass)
		{
			parent::__construct($definition['to']);
			$this->_linkedObject = $object;
			$this->_definition = $definition;
			$this->_fromClass = $fromClass;
		}

		public function receiveArray(array $array)
		{
			$this->_initialised = true;
			// array is in fact an object
			if ($this->_definition['reference'] == 'internal' || $this->_definition['max'] == 1) {
				$o = $this->newObject();
				$o->receiveArray($array, true);
				return;
			}

			foreach ($array as $objectArray) {
				if (isset($objectArray[$this->_class]))// special json
					$objectArray = $objectArray[$this->_class];
				//if($this->_)
				$o = $this->newObject();
				$o->receiveArray($objectArray, true);
			}
		}

		public function toJson($base = true)
		{
			$ret = parent::toJson($base);
			if ($this->_definition['reference'] == 'internal' || $this->_definition['max'] == 1) {
				return $base ? (count($ret[$this->_class]) ? current($ret[$this->_class]) : null) : (isset($ret[0]) ? $ret[0] : null);
			}
			return $ret;
		}

		public function offsetSet($index, $value)
		{
			if (!$value instanceof \CRUDsader\Object) {// fk
				$object = \CRUDsader\Instancer::getInstance()->{'object.proxy'}($this->_class, (int)$value);
				$this->offsetSet($index, $object);
				$this->update($object);
			} else {
				if (!isset($this->_objects[$index]) && $this->_definition['max'] != '*' && $this->_iterator == $this->_definition['max'])
					throw new AssociationException('association "' . $this->_definition['to'] . '" cannot have more than "' . $this->_definition['max'] . '" objects');
				$value = parent::offsetSet($index, $value);
				\CRUDsader\Object\Writer::linkToAssociation($value, $this);
				\CRUDsader\Object\Writer::setModified($this->_linkedObject);

				$this->_isModified = true;
				return $value;
			}
		}

		public function generateRandom()
		{
			$max = rand((int) $this->_definition['min'], $this->_definition['max'] == '*' ? '3' : (int) $this->_definition['max']);
			$this->_objects = array();
			$this->_initialised = true;
			$this->rewind();

			for ($i = 0; $i < $max; $i++) {
				if ($this->_definition['composition']) {
					$this->newObject()->generateRandom();
					$this->_isModified = true;
				} else {
					$query = \CRUDsader\Instancer::getInstance()->query('FROM ' . $this->_definition['to'] . ' ORDER BY ? LIMIT 1');
					$found = $query->fetch(array(sl()->expression('rand()')));
					if ($found) {
						$this->_objects[$i] = $found;
						$this->_isModified = true;
					} else {
						/* $object = $this->newObject();
						  $object->generateRandom();
						  $this->_isModified = true; */
					}
				}
			}
		}

		/**
		 * @return type 
		 */
		public function newObject()
		{
			if ($this->_definition['max'] != '*' && $this->_iterator == $this->_definition['max'])
				throw new AssociationException('association cannot have more than "' . $this->_definition['max'] . '" objects');
			$object = parent::newObject();
			\CRUDsader\Object\Writer::linkToAssociation($object, $this);
			return $object;
		}

		public function getDefinition()
		{
			return $this->_definition;
		}

		public function save(\CRUDsader\Object\UnitOfWork $unitOfWork = null)
		{
			// check
			$cnt = 0;
			$db = \CRUDsader\Instancer::getInstance()->database;

			if ($this->_isModified) {
				foreach ($this->_objects as $index => $object) {
					if ($object->isPersisted() && isset($this->_objectsToBeDeleted[$object->getId()])) {
						switch ($this->_definition['reference']) {
							case 'internal':
								$unitOfWork->update($this->_linkedObject->getDatabaseTable(), array($this->_definition['internalField'] => null), $db->quoteIdentifier($this->_definition['databaseIdField']) . '=' . $db->quote($this->_linkedObject->isPersisted()));
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

						unset($this->_objects[$index]);
						continue;
					}

					if ($object->isEmpty()) {
						$object->delete($unitOfWork);
					} else {
						$cnt++;
						switch ($this->_definition['reference']) {
							case 'internal':
								$object->save($unitOfWork);
								$definition = $this->_linkedObject->getDefinition();
								$unitOfWork->update($this->_linkedObject->getDatabaseTable(), array($this->_definition['internalField'] => $object->isPersisted()), $db->quoteIdentifier($definition['definition']['databaseIdField']) . '=' . $this->_linkedObject->isPersisted());
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
										    //'id' => \CRUDsader\Instancer::getInstance()->{'object.identifier'}->getOID(array('class' => $this->_class)),
										    $this->_definition['internalField'] => $this->_linkedObject->isPersisted(),
										    $this->_definition['externalField'] => $object->isPersisted()
											), $object);
									} else {
										$d = array(
										    $this->_definition['internalField'] => $this->_linkedObject->isPersisted(),
										    $this->_definition['externalField'] => $object->isPersisted()
										);
										if ($object->isPersisted() && $object->getLinkedAssociationId()) {
											// update   
											$unitOfWork->update($this->_definition['databaseTable'], $d, $db->quoteIdentifier($this->_definition['databaseIdField']) . '=' . $db->quote($object->getLinkedAssociationId()));
										} else {
											//$d['id'] = \CRUDsader\Instancer::getInstance()->{'object.identifier'}->getOID(array('class' => $this->_class));
											$unitOfWork->delete($this->_definition['databaseTable'], $db->quoteIdentifier($this->_definition['externalField']) . '=' . $db->quote($object->isPersisted()) . ' AND ' . $db->quoteIdentifier($this->_definition['internalField']) . '=' . $db->quote($this->_linkedObject->isPersisted()));
											$unitOfWork->insert($this->_definition['databaseTable'], $d, $object);
											//\CRUDsader\Object\Writer::setLinkedAssociationId($object, $d['id']);
										}
									}
								}
						}
					}
				}
				if ($this->_definition['max'] != '*' && $cnt > $this->_definition['max']) {
					throw new AssociationException('error.association.save.max');
				}
				if ($cnt < $this->_definition['min'])
					throw new AssociationException('error.association.save.min');
			}
		}

		public function offsetUnset($id)
		{
			if (isset($this->_objectIndexes[$id])) {
				$this->_objectsToBeDeleted[$id] = true;
				$this->_isModified = true;
				\CRUDsader\Object\Writer::setModified($this->_linkedObject);
			}
		}

		public function delete(\CRUDsader\Object\UnitOfWork $unitOfWork = null)
		{
			if ($unitOfWork === null)
				throw new AssociationException('no UnitOfWork');
			$db = \CRUDsader\Instancer::getInstance()->database;
			foreach ($this->_objects as $object) {
				if (!$object->isPersisted())
					continue;
				switch ($this->_definition['reference']) {
					case 'internal':
						$unitOfWork->update($this->_linkedObject->getDatabaseTable(), array($this->_definition['internalField'] => null), $db->quoteIdentifier($this->_definition['internalField']) . '=' . $db->quote($this->_linkedObject->isPersisted()));
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

		public function isModified()
		{
			return $this->_isModified;
		}

		public function getLinkedObject()
		{
			return $this->_linkedObject;
		}

		public function getForm($oql = false, $alias = false, \CRUDsader\Form $form = null)
		{

			if (empty($alias))
				$alias = $this->_class;
			$this->_initialised = true;
			$formAssociation = $form->add(new \CRUDsader\Form($alias), $this->_definition['name'] ? $this->_definition['name'] : $this->_definition['to']);
			$formAssociation->setHtmlLabel(\CRUDsader\Instancer::getInstance()->i18n->translate($alias . '.association')); //.association is mandatory for translation purpose
			$max = $this->_definition['reference'] == 'internal' ? 1 : ($this->_definition['max'] == '*' ? 3 : $this->_definition['max']);
			if ($this->_definition['min'] > $max)
				$max = $this->_definition['min'];
			$this->rewind();
			$this->_formValues = array();
			for ($i = 0; $i < $max; $i++) {
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
					if ($this->_definition['inputPhpClass']) {
						$class = $this->_definition['inputPhpClass'];
						$input = new $class($this->_class . $i, array('class' => $this->_class, 'definition' => $this->_definition));
					}else
						$input = \CRUDsader\Instancer::getInstance()->{'form.component.association'}(array('class' => $this->_class));
					$component = $formAssociation->add($input, $i, false);
					$component->setHtmlLabel($i == 0 ? \CRUDsader\Instancer::getInstance()->i18n->translate($alias . '.object') : ' ');
					$component->setParameter('compositionIndex', $this->_iterator);
					if ($i < $this->_definition['min']) {
						$component->setInputRequired(true);
						$formAssociation->setInputRequired(true);
					}
					if ($object->isPersisted())
						$component->setValueFromInput($object->isPersisted());
					$component->attach($this);
				}
				$this->next();
			}
		}

		/**
		 * @todo erase empty ones!
		 * @param \SplSubject $component 
		 */
		public function update(\SplSubject $component)
		{
			if ($component instanceof \CRUDsader\Form\Component && $component->hasParameter('compositionIndex')) {

				$index = $component->getParameter('compositionIndex');
				$value = $component->getValue();
				$empty = $component->isEmpty();
				if (!$empty && isset($this->_formValues[$value]))
					throw new AssociationException($this->_class . '_duplicates');
				$target = $this->_objects[$index];

				// has the target changed ???
				if ($target->isPersisted()) {
					if ($empty) {// delete the object. in DB as well
						$this->_objectsToBeDeleted[$index] = true;
						$this->_isModified = true;
						\CRUDsader\Object\Writer::setModified($this->_linkedObject);
					} else
					if ($value != $target->isPersisted()) {// replace by proxy
						unset($this->_objects[$index]);
						if (isset($this->_objectIndexes[$target->isPersisted()]))
							unset($this->_objectIndexes[$target->isPersisted()]);
						$this->_objects[$index] = \CRUDsader\Instancer::getInstance()->{'object.proxy'}($this->_class, $component->getValue());
						$this->_isModified = true;
						\CRUDsader\Object\Writer::setModified($this->_linkedObject);
					}
				}else if ($empty) {// delete object
					unset($this->_objects[$index]);
				} else {
					// replace by proxy
					unset($this->_objects[$index]);
					if (isset($this->_objectIndexes[$target->isPersisted()]))
						unset($this->_objectIndexes[$target->isPersisted()]);
					$this->_objects[$index] = \CRUDsader\Instancer::getInstance()->{'object.proxy'}($this->_class, $value);
					$this->_isModified = true;
					\CRUDsader\Object\Writer::setModified($this->_linkedObject);
				}
			}
			if ($component instanceof \CRUDsader\Object) {
				\CRUDsader\Object\Writer::setModified($this->_linkedObject);
				switch ($this->_definition['reference']) {
					case 'internal':
						$field = $this->_definition['internalField'];
						$this->_linkedObject->addExtraAttribute($field, (int) $component->getId()); // fks
						break;
					case 'external':
						$field = $this->_definition['externalField'];
						$component->addExtraAttribute($field, (int) $this->_linkedObject->getId()); // fks
						break;
					default:
				}
				$this->_isModified = true;
			}
		}

		public function rewind()
		{
			if (!$this->_initialised)
				throw new AssociationException('collection is not initialised');
			parent::rewind();
		}
	}
	class AssociationException extends \CRUDsader\Exception {
		
	}
}