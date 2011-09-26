<?php
namespace Art\Object {
    class Writer extends \Art\Object {

        public static function write(parent $object, $id, $alias, &$rows, &$fields, &$mapFields) {
            $map = \Art\Map::getInstance();
            if (!$object->_initialised) {
                $object->_isPersisted = $id;
                \Art\Object\IdentityMap::add($object);
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
                $parentClassAlias = $object->_class . '_parent';
                if (isset($mapFields[$parentClassAlias])) {
                    $object->_parent = new \Art\Object($map->classGetParent($object->_class));
                    $object->_parent->_isPersisted = $id;
                    $object->_parent->_isInitialised= true;
                    self::write($object->_parent, $id, $parentClassAlias, $rows, $fields, $mapFields);
                }
            }
            // associations
            foreach ($object->_infos['associations'] as $name => $associationInfos) {
                if (isset($mapFields[$alias . '_' . $name]))
                    \Art\Object\Collection\Association\Writer::write($object->getAssociation($name), $alias . '_' . $name, $rows, $fields, $mapFields);
            }
        }

        public static function setModified(parent $object) {
            $object->_isModified = true;
            if ($object->hasParent())
                self::setModified($object->getParent());
        }

        public static function linkToAssociation(parent $object, \Art\Object\Collection\Association $association) {
            $object->_linkedAssociation = $association;
        }
        
        public static function setLinkedAssociationId(parent $object,$id){
            $object->_linkedAssociationId=$id;
        }
    }
}