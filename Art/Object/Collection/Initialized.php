<?php
namespace Art\Object\Collection {
    class Initialized extends \Art\Object\Collection {

        public function __construct($className, \Art\Adapter\Database\Rows $rowSet, array $mapFields) {
            parent::__construct($className);
            if ($rowSet->count()) {
                $lastId = false;
                $aggregatedRows = array();
                $fields = $rowSet->getFields();
                foreach ($rowSet as $row) {
                    if ($lastId === false)
                        $lastId = $row[0];
                    if ($row[0] != $lastId) {
                        pre($aggregatedRows,$lastId);
                        $this->_newObject($row[0], $fields, $mapFields, $aggregatedRows);
                        $aggregatedRows = array();
                    }
                    $aggregatedRows[] = $row;
                    $lastId = $row[0];
                }
                if (count($aggregatedRows))
                    $this->_newObject($lastId, $fields, $mapFields, $aggregatedRows);
            }
           // pre($this->toArray());
        }

        protected function _newObject($id, $fields, $mapFields, $aggregatedRows) {
            $ret = $this->_objects[$this->_iterator] = new NObject($this->_class);
            $ret->writeModeActionSetId($id);
            $ret->writeModeActionLoadArray($aggregatedRows, $mapFields, $fields);
            $this->_objectIndexes[$id] = $this->_iterator;
            $this->_iterator++;
        }
        
        
    }
    class NObject {
        protected $_class;
        protected $_isPersisted = false;
        protected $_infos;
        protected $_associations = array();

        public function __construct($className) {
            $this->_class = $className;
            $this->_infos = \Art\Map::getInstance()->classGetInfos($this->_class);
        }

        public function writeModeActionSetId($id) {
            $this->_isPersisted = $id;
        }

        public function writeModeActionloadArray($rows, $mapFields, $fields) {
            // attributes
            for ($i = 1; $i < $mapFields[$this->_class]['to']; $i++) {
                $this->writeModesetAttribute($fields[$i]->name, $rows[0][$i]);
            }
            $split=array();
            foreach($rows as $k=>$row){
                foreach($mapFields as $name=>$infos){
                    for($i=$infos['from'];$i<$infos['to'];$i++){
                        $split[$name][$k][$i]=$row[$i];
                    }
                }
            }
            pre($split,'split');
            // associations
            foreach ($this->_infos['associations'] as $name => $associationInfos) {
                $this->getAssociation($name);
            }
            pre($this);
            exit;
        }

        public function writeModesetAttribute($fieldIndex, $value) {
            $this->$value = $fieldIndex;
        }

        public function hasAssociation($associationName) {
            
        }

        public function getAssociation($associationName) {
            if (!isset($this->_associations[$associationName]))
                $this->_associations[$associationName] = new NAssociation($this->_infos['associations'][$associationName]['to']);
            return $this->_associations[$associationName];
        }
    }
    class NAssociation extends \Art\Object\Collection {
        protected $_class;
        protected $_initialized = false;

        public function __construct($className) {
            $this->_class = $className;
        }
    }
}


