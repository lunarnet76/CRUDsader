<?php
class Art_Mvc_Historic{
    protected static $_instance;
    protected $_session;
    protected $_forbiddenController=array();

    public static function getInstance(){
        if(!isset(self::$_instance))
            self::$_instance=new self();
        return self::$_instance;
    }

    protected function __construct(){
        $this->_session=Art_Session::useNamespace('mvc_historic');
        if(!isset($this->_session->iterator))
                $this->_session->iterator=10;
    }

    public function dontRegister($controller){
        $this->_forbiddenController[$controller]=true;
    }

    public function register(Art_Mvc_Router_Abstract $router){
        if((!isset($this->_session->{$this->_session->iterator}) || $this->_session->{$this->_session->iterator}->uri!=$router->getUri()) && !isset($this->_forbiddenController[$router->getController()])){
            $index=++$this->_session->iterator;
            if($index==10)$this->_session->iterator=0;
            $this->_session->{$index}=array(
                    'uri'=>$router->getUri(),
                    'module'=>$router->getModule(),
                    'controller'=>$router->getController(),
                    'action'=>$router->getAction(),
                    'params'=>$router->parseParams($router->getParams())
            );
        }
    }

    public function getLast(){
        return isset($this->_session->{$this->_session->iterator})?$this->_session->{$this->_session->iterator}->toArray():false;
    }

    public function toArray(){
        return $this->_session->toArray();
    }

    public function reset(){
        $this->_session->reset();
        $this->_session->iterator=0;
    }
}
?>
