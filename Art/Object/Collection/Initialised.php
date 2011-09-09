<?php
namespace Art\Object\Collection {
    class Initialised extends \Art\Object\Collection {

        public function __construct($className, \Art\Adapter\Database\Rows $rowSet, array $mapFields) {
            $fields = $rowSet->getFields();
            parent::__construct($className);
            $this->_initialised = true;
            if ($rowSet->count()) {
                foreach ($rowSet as $i => $row) {
                    $id = current($row);
                    if (!\Art\Expression::isEmpty($id)) {
                        if (!isset($this->_objectIndexes[$id])) {
                            $this->_objects[$this->_iterator] = new \Art\Object($this->_class);
                            $this->_objectIndexes[$id] = $this->_iterator;
                            $this->_iterator++;
                        }
                        $aggregate[$id][] = $row;
                    }
                }
            }
            foreach ($aggregate as $id => $rows) {
                \Art\Object\Writer::write($this->_objects[$this->_objectIndexes[$id]],$id, $this->_class,$rows, $fields, $mapFields);
            }
        }
    }
}