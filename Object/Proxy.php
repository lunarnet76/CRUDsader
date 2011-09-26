<?php
namespace CRUDsader\Object {
    class Proxy extends \CRUDsader\Object {

        public function __construct($className,$id) {
            parent::__construct($className);
            $this->_isPersisted=$id;
            $this->_initialised=true;
        }
        
        /**
         * check if an object with the same identity exists in database
         * @return bool 
         */
        protected function _checkIdentity(){
            return true;
        }
        
        public function toArray($full=false){
            $parent=parent::toArray($full);
            $parent['id'].='[PROXY]';
            return $parent;
        }
        
        
        public function save(\CRUDsader\Object\UnitOfWork $unitOfWork=null) {
            
        }
    }
}