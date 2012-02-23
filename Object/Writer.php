<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Object {
	class Writer extends \CRUDsader\Object {

		public static function write(parent $object, $id, $alias, &$rows, &$fields, &$mapFields, &$extraColumns = false)
		{
			$map = \CRUDsader\Instancer::getInstance()->map;
			if (!$object->_initialised) {
				$object->_isPersisted = $id;
				\CRUDsader\Object\IdentityMap::add($object);
				$object->_initialised = true;
				if (isset($mapFields[$alias])) {
					for ($i = $mapFields[$alias]['from'] + 1; $i < $mapFields[$alias]['to']; $i++) {
						if (isset($extraColumns[$i])) {
							$object->addExtraAttribute($extraColumns[$i], $rows[0][$i]);
						} else if (isset($fields[$i]) && isset($object->_infos['attributesReversed'][$fields[$i]]) && $object->hasAttribute($object->_infos['attributesReversed'][$fields[$i]])) {
							
							$object->getAttribute($object->_infos['attributesReversed'][$fields[$i]])->setValueFromDatabase($rows[0][$i]);
						} else if (isset($fields[$i]) && isset($object->_infos['attributesReversed'][$fields[$i]])) {
							// FKs
							$object->addExtraAttribute($object->_infos['attributesReversed'][$fields[$i]], (int) $rows[0][$i]);
						}
					}
				}

				if ($object->_linkedAssociation) {
					$definition = $object->_linkedAssociation->getDefinition();
					if ($definition['reference'] == 'table') {
						if ($mapFields[$alias]['from'] - 3 > 0)
							$object->_linkedAssociationId = $rows[0][$mapFields[$alias]['from'] - 3];
					}
				}
			}
			// parent
			$parentClassAlias = $alias . '_parent';
			if (isset($mapFields[$parentClassAlias])) {
				if (!isset($object->_parent)) {
					$parentClass = $map->classGetParent($object->_class);
					$object->_parent = \CRUDsader\Object::instance($parentClass);
					$object->_parent->_isPersisted = $id;
					$object->_parent->_child = $object;
				}
				self::write($object->_parent, $id, $parentClassAlias, $rows, $fields, $mapFields, $extraColumns);
			}
			// associations
			foreach ($object->_infos['associations'] as $name => $associationInfos) {
				if (isset($mapFields[$alias . '_' . $name])) {
					\CRUDsader\Object\Collection\Association\Writer::write($object->getAssociation($name), $alias . '_' . $name, $rows, $fields, $mapFields, $extraColumns);
				}
			}
		}

		public static function setModified(parent $object)
		{
			$object->_isModified = true;
			if ($object->hasParent())
				self::setModified($object->getParent());
		}

		public static function linkToAssociation(parent $object, \CRUDsader\Object\Collection\Association $association)
		{
			$object->_linkedAssociation = $association;
		}

		public static function setLinkedAssociationId(parent $object, $id)
		{
			$object->_linkedAssociationId = $id;
		}
	}
}