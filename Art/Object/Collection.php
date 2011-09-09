<?php
namespace Art\Object {
    class Collection  implements \Art\Interfaces\Initialisable{
        
        protected $_initialised=false;
        protected $_class=null;
        protected $_classInfos=null;
        protected $_objects=array();
        protected $_iterator=0;
        protected $_objectIndexes=array();
        
        
        public function __construct($className){
            $this->_class=$className;
            $this->_classInfos=\Art\Map::getInstance()->classGetInfos($this->_class);
        }
        
        public function toArray($full=false){
                $ret=array('class'=>$this->_class,'initialised'=>$this->_initialised?'yes':'no','objects'=>array(),'indexMap'=>$this->_objectIndexes);
            foreach($this->_objects as $k=>$object)
                    $ret['objects'][$k]=$object->toArray($full);
            return $full?$ret:$ret['objects'];
        }
        
        public function isInitialised(){
            return $this->_initialised;
        }
    }
}