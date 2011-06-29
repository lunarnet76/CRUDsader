<?php
class Art_Object_Collection_Composition extends Art_Object_Collection_Association {
    protected function _transformObject(Art_Object $value){
        return Art_Object_Composed::transform($this,$value);
    }
    
    protected function _instanceObject(){
        return new Art_Object_Composed($this->_definition['to'],$this);
    }

    public function getForm($baseClassName=false) {
        $form = new Art_Data_Form($this->_definition['to']);
        $form->setLabel(false);
        $form->setRequired($this->_definition['mandatory']);
        if ($this->_definition['cardinality'] == 'one-to-one'){
            $form->add($this->getCurrentObject()->getForm($baseClassName,1,false,$this->_definition['mandatory']));
        }else {
            $this->rewind();
            for ($i = 0; $i < 3; $i++) {
                if ($this->valid())
                    $form->add($this->current()->getForm($baseClassName, $i,false,$this->_definition['mandatory']));
                else 
                    $form->add($this->getCurrentObject()->getForm($baseClassName, $i,false,$this->_definition['mandatory']));
                $this->next();
            }
        }
        return $form;
    }
}
?>