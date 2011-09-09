<?php
namespace Art\Object\Collection {
    class Association extends \Art\Object\Collection {
        protected $_definition;
        public function __construct($definition,$fromClass){
            $this->_definition=$definition;
            $this->_fromClass=$fromClass;
            $this->_class=$definition['to'];
        }
        
         public function getForm(\Art\Form $form=null){
             
         }
    }
}