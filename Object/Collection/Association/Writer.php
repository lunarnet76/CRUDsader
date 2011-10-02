<?php
namespace CRUDsader\Object\Collection\Association {
    class Writer extends \CRUDsader\Object\Collection\Association {

        public static function write(parent $collection, $alias, &$rows, &$fields, &$mapFields) {
            // simply create an object for each different id
            $collection->_initialised=true;
            $lastId = false;
            $aggregate = array();
            foreach ($rows as $i => $row) {
                $id = $row[$mapFields[$alias]['from']];
                if (!\CRUDsader\Expression::isEmpty($id)) {
                    if (!isset($collection->_objectIndexes[$id])) {
                        $class=\CRUDsader\Map::getInstance()->classGetModelClass($collection->_class);
                        $collection->_objects[$collection->_iterator] = new $class($collection->_class);
                        \CRUDsader\Object\Writer::linkToAssociation($collection->_objects[$collection->_iterator],$collection);
                        $collection->_objectIndexes[$id] = $collection->_iterator;
                        $collection->_iterator++;
                    }
                    $aggregate[$id][] = $rows[$i];
                }
            }
            foreach ($aggregate as $id => $rows) {
                \CRUDsader\Object\Writer::write($collection->_objects[$collection->_objectIndexes[$id]], $id, $alias, $rows, $fields, $mapFields);
            }
        }
    }
}