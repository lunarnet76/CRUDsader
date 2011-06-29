<?php
class Art_I18n{
    protected static $_instance;
    protected $_translation;
    protected $_adapter;
    
    public static function getInstance(){
        if(!isset(self::$_instance))
            self::$_instance=new self();
        return self::$_instance;
    }

    protected function  __construct() {
        $configuration=Art_Configuration::getInstance();
        $this->_adapter=Art_Adapter_Factory::getInstance('i18n');
    }

    public function getAdapter(){
        return $this->_adapter;
    }

    public function get($index){
        return $this->_adapter->get($index);
    }
}
?>
