<?php
namespace Art\Object\Collection {
    class Initialized extends \Art\Object\Collection {

        public function __construct($className, \Art\Adapter\Database\Rows $rowSet, array $mapFields) {
            parent::__construct($className);
            if ($rowSet->count()) {
            //pre($rowSet,'rw');
            /*pre($mapFields);
             //*/
                $lastId = false;
                $aggregatedRows = array();
                $fields = $rowSet->getFields();
                foreach ($rowSet as $row) {
                    if ($lastId === false)
                        $lastId = $row[0];
                    if ($row[0] != $lastId) {
                        $this->_newObject($lastId, $fields, $mapFields, $aggregatedRows);
                        $aggregatedRows = array();
                    }
                    $aggregatedRows[] = $row;
                    $lastId = $row[0];
                }
                if (count($aggregatedRows))
                    $this->_newObject($lastId, $fields, $mapFields, $aggregatedRows);
            }
        }

        protected function _newObject($id, $fields, $mapFields, $aggregatedRows) {
            $ret = $this->_objects[$this->_iterator] = new Object($this->_class); 
            ObjectWriter::setId($ret, $id);
            ObjectWriter::loadArray($ret, $aggregatedRows, $mapFields, $fields);
            $this->_objectIndexes[$id] = $this->_iterator;
            $this->_iterator++;
        }
    }
    // WRITER
    class ObjectWriter extends Object {

        public static function setId(Object $object, $id) {
            $object->_isPersisted = $id;
        }

        public static function loadArray(Object $object, $rows, $mapFields, $fields) {
            $split = array();
            foreach ($rows as $k => $row) {
                foreach ($mapFields as $name => $infos) {
                    for ($i = $infos['from']; $i < $infos['to']; $i++) {
                        $split[$name][$k][$i] = isset($row[$i])?$row[$i]:new \Art\Expression\Void();
                    }
                }
            }
            // attributes
            foreach ($split[$object->_class] as $i => $v) {
                if ($i !== 0)
                    self::setAttribute($object, $fields[$i]->name, $rows[0][$i]);
            }
            // parent
            // associations
            foreach ($object->_infos['associations'] as $name => $associationInfos) {
                $object->getAssociation($name);
            }
            pre($object->toArray());
            exit;
        }

        public static function setAttribute(Object $object, $fieldIndex, $value) {
            if (!isset($object->_infos['associations'][$fieldIndex]))
                $object->_fields[$fieldIndex] = $value;
        }
    }
    
    // OBJECT
    class Object {
        protected $_class;
        protected $_isPersisted = false;
        protected $_infos;
        protected $_fields = array();
        protected $_associations = array();

        public function __construct($className) {
            $this->_class = $className;
            $this->_infos = \Art\Map::getInstance()->classGetInfos($this->_class);
        }

        public function hasAssociation($associationName) {
            
        }

        public function getAssociation($associationName) {
            if (!isset($this->_associations[$associationName]))
                $this->_associations[$associationName] = new Association($this->_infos['associations'][$associationName]['to']);
            return $this->_associations[$associationName];
        }

        public function toArray() {
            return array('persisted'=>$this->_isPersisted,'fields'=>$this->_fields);
        }
    }
    // ASSOC
    class Association extends \Art\Object\Collection {
        protected $_class;
        protected $_initialized = false;

        public function __construct($className) {
            $this->_class = $className;
        }
    }
}


