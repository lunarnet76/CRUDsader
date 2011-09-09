<?php
namespace Art\Object\Collection\Association {
    class Writer extends \Art\Object\Collection\Association {

        public static function write(parent $collection, $alias, &$rows, &$fields, &$mapFields) {
            // simply create an object for each different id
            $lastId = false;
            $aggregate = array();
            foreach ($rows as $i => $row) {
                $id = $row[$mapFields[$alias]['from']];
                if (!\Art\Expression::isEmpty($id)) {
                    if (!isset($collection->_objectIndexes[$id])) {
                        $collection->_objects[$collection->_iterator] = new \Art\Object($collection->_class);
                        $collection->_objectIndexes[$id] = $collection->_iterator;
                        $collection->_iterator++;
                    }
                    $aggregate[$id][] = $rows[$i];
                }
            }
            foreach ($aggregate as $id => $rows) {
                \Art\Object\Writer::write($collection->_objects[$collection->_objectIndexes[$id]], $id, $alias, $rows, $fields, $mapFields);
            }
        }
    }
}