<?php
namespace CRUDsader\Object {
    class Writer extends \CRUDsader\Object {

        public static function write(parent $object, $id, $alias, &$rows, &$fields, &$mapFields) {
            $map = \CRUDsader\Map::getInstance();
            if (!$object->_initialised) {
                $object->_isPersisted = $id;
                \CRUDsader\Object\IdentityMap::add($object);
                $object->_initialised = true;
                for ($i = $mapFields[$alias]['from']+1; $i < $mapFields[$alias]['to']; $i++) {
                    if (isset($object->_infos['attributesReversed'][$fields[$i]])) {
                        $object->getAttribute($object->_infos['attributesReversed'][$fields[$i]])->setValueFromDatabase($rows[0][$i]);
                    }
                }
                if($object->_linkedAssociation){
                    $definition=$object->_linkedAssociation->getDefinition();
                    if($definition['reference']=='table'){
                        $object->_linkedAssociationId=$rows[0][$mapFields[$alias]['from']-3];
                    }
                }
                // parent
                $parentClassAlias = $alias . '_parent';
                if (isset($mapFields[$parentClassAlias])) {
                    $parentClass=$map->classGetParent($object->_class);
                    $object->_parent = \CRUDsader\Object::instance($parentClass);
                    $object->_parent->_isPersisted = $id;
                    self::write($object->_parent, $id, $parentClassAlias, $rows, $fields, $mapFields);
                }
            }
            // associations
            foreach ($object->_infos['associations'] as $name => $associationInfos) {
                if (isset($mapFields[$alias . '_' . $name]))
                    \CRUDsader\Object\Collection\Association\Writer::write($object->getAssociation($name), $alias . '_' . $name, $rows, $fields, $mapFields);
            }
        }

        public static function setModified(parent $object) {
            $object->_isModified = true;
            if ($object->hasParent())
                self::setModified($object->getParent());
        }

        public static function linkToAssociation(parent $object, \CRUDsader\Object\Collection\Association $association) {
            $object->_linkedAssociation = $association;
        }
        
        public static function setLinkedAssociationId(parent $object,$id){
            $object->_linkedAssociationId=$id;
        }
    }
}