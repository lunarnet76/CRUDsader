<?php
namespace Art\Object {
    class Collection  {
        
        protected $_class=null;
        protected $_classInfos=null;
        protected $_objects=array();
        protected $_iterator=0;
        protected $_objectIndexes=array();
        
        
        public function __construct($className){
            $this->_class=$className;
            $this->_classInfos=\Art\Map::getInstance()->classGetInfos($this->_class);
        }
        
        public function toArray(){
            $ret=array();
            foreach($this->_objects as $k=>$object)
                    $ret[$k]=$object->toArray();
            return $ret;
        }
    }
}