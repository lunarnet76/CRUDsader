<?php
namespace Art\Object {
    class Writer extends \Art\Object {

        public static function write(parent $object, $id, $alias, &$rows, &$fields, &$mapFields) {
            $map = \Art\Map::getInstance();
            if (!$object->_initialised) {
                $object->_isPersisted = $id;
                $object->_initialised = true;
                for($i=$mapFields[$alias]['from']+1;$i<$mapFields[$alias]['to'];$i++){
                    if (isset($object->_infos['attributesReversed'][$fields[$i]->name])){
                        $object->getAttribute($object->_infos['attributesReversed'][$fields[$i]->name])->setValueFromDatabase($rows[0][$i]);
                    }
                }
                // parent
                $parentClassAlias = $object->_class . '_parent';
                if (isset($mapFields[$parentClassAlias])) {
                    $object->_parent = new \Art\Object($map->classGetParent($object->_class));
                    self::write($object->_parent, $id,$parentClassAlias, $rows, $fields,$mapFields);
                }
            }
            // associations
            foreach ($object->_infos['associations'] as $name => $associationInfos) {
                if (isset($mapFields[$alias . '_' . $name]))
                    \Art\Object\Collection\Association\Writer::write($object->getAssociation($name), $alias . '_' . $name, $rows, $fields, $mapFields);
            }
        }
    }
}