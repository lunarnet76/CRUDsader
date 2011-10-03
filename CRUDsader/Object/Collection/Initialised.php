<?php
namespace CRUDsader\Object\Collection {
    class Initialised extends \CRUDsader\Object\Collection {

        public function __construct($className, \CRUDsader\Adapter\Database\Rows $rowSet, array $mapFields) {
            $fields = $rowSet->getFields();
            parent::__construct($className);
            $this->_initialised = true;
            if ($rowSet->count()) {
                foreach ($rowSet as $i => $row) {
                    $id = current($row);
                    if (!\CRUDsader\Expression::isEmpty($id)) {
                        if (!isset($this->_objectIndexes[$id])) {
                            if (\CRUDsader\Object\IdentityMap::exists($this->_class, $id))
                                $this->_objects[$this->_iterator] = \CRUDsader\Object\IdentityMap::get($this->_class, $id);
                            else {
                                $this->_objects[$this->_iterator] = \CRUDsader\Object::instance($this->_class);
                            }
                            $this->_objectIndexes[$id] = $this->_iterator;
                            $this->_iterator++;
                        }
                        $aggregate[$id][] = $row;
                    }
                }
                foreach ($aggregate as $id => $rows) {
                    //if (!\CRUDsader\Object\IdentityMap::exists($this->_class, $id))
                        \CRUDsader\Object\Writer::write($this->_objects[$this->_objectIndexes[$id]], $id, $this->_class, $rows, $fields, $mapFields);
                }
            }
        }
    }
}