<?php
class Art_Object_Proxy extends Art_Object{
    public function setId($id){
        $this->_id=$id;
        $this->_isPersisted=true;
        $this->_isProxy=true;
    }

    public function __get($var){
        throw new Exception('you cannot get param from a proxy object');
    }
}
?>
