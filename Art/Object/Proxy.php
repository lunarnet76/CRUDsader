<?php
namespace Art\Object {
    class Proxy extends \Art\Object {

        public function __construct($className,$id) {
            parent::__construct($className);
            $this->_isPersisted=$id;
            $this->_initialised=true;
        }
        
        public function toArray($full=false){
            if(!$full)return parent::toArray (false);
            $parent=parent::toArray(true);
            $parent['proxy']=true;
            return $parent;
        }
    }
}