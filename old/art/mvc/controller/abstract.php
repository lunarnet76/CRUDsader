<?php
/**
 * @todo add ob ???, remove Mvc_Controller_Front::getInstance();
 */
abstract class Art_Mvc_Controller_Abstract {
    protected $_configuration=NULL;
    protected $_frontController=NULL;
    protected $_views=array();

    protected $_metas=array();
    protected $_headers=array();
    protected $_title='';
    protected $_preLoads=array();
    protected $_noRender=false;
    protected $_template;

    public function __construct(Art_Mvc_Controller_Front $frontController,Art_Block $configuration) {
        $this->_configuration=$configuration;
        $this->_frontController=$frontController;
        $this->_views['base']=array('controller'=>$frontController->getRouter()->getController(),'action'=>$frontController->getRouter()->getAction());
    }
    
    // helpers
    public function __get($var){
        switch(true){
            case in_array($var,array('module','controller','action','params','uri')):
                return $this->_frontController->$var;
            break;
            case $var=='uri':
                return 'http://'.$this->_configuration->server.$this->module.'/'.$this->controller.'/'.$this->action.$this->_configuration->router->separator->params.implode($this->_configuration->router->separator->params,$this->params);
                break;
            case $this->_frontController->moduleHasPlugin($var)===true:
                return $this->_frontController->moduleGetPlugin($var);
            break;
        }
    }

    public function object($className){
        return Art_Object::instance($className);
    }

    public function url(array $options=array()){
        return $this->_frontController->url($options);
    }

    public function link(array $options=array()){
        $options['url']=$this->url($options);
        return new Art_Mvc_Link($options);
    }

    public function image($options=array()){
        if(!is_array($options))
            $options=array('file'=>$options);
        $options['module']=$this->_frontController->module;
        $options['baseRef']=$this->_frontController->getURL();
        return new Art_Mvc_Image($options);
    }

    public function redirect($options){
        if(Art_Debug::isActivated())
            echo '<a href="'.$this->url($options).'">'.$this->url($options).'</a>';
        else
            header('Location: '.$this->link($options));
        exit;
    }


    // accessor
    public function setMeta($type,$value){
        $this->_metas[$type]=$value;
    }

    public function getMeta($type){
        return $this->_metas[$type];
    }

    public function setHeader($name,$content){
        $this->_headers[$name]=$content;
    }

    public function getHeader($name,$content){
        return $this->_headers[$name];
    }

    public function setTitle($value){
        $this->_title=$value;
    }

    public function preLoad($name,$type='js',$folder=''){
        $this->_preLoads[$type][$name]=$folder;
    }

    public function loadToHTML(){
        $output='';
        foreach($this->_preLoads as $type=>$files)
            foreach($files as $file=>$folder)
                switch($type){
                    case 'js':
                        $folder=$folder=='module'?$this->_frontController->getFolder().$this->_frontController->module.'/js/':'library/'.$folder;
                        $output.='<script type="text/javascript" src="'.$this->_frontController->getURL().$folder.$file.'.js"></script>';
                        break;
                    case 'css':
                        $folder=$folder=='module'?$this->_frontController->getFolder().$this->_frontController->module.'/':'file/'.$folder.'css/';
                        $output.='<link rel="stylesheet" type="text/css" href="'.$this->_frontController->getURL().$folder.$file.'.css"/>';
                        break;
                }
        return $output;
    }

    public function setNoRender($bool){
        $this->_noRender=$bool;
    }

    public function setTemplate($name){
        $this->_template=$name;
    }
    public function init(){}    
    public function preRender(){}
    public function postRender(){}

    // render
    public function render() {
        if($this->_noRender)return;
        if(!$this->_isRendered)
           $this->_isRendered=true;
        $router=$this->_frontController->getRouter();
        $suffix=$this->_configuration->view->suffix;
        foreach($this->_views as $infos){
            $path=file_exists($this->_frontController->getFolder().$router->getModule().'/view/'.($infos['controller']?str_replace('_','/',$infos['controller']).'/':'').$infos['action'].$suffix)?$this->_frontController->getFolder().$router->getModule().'/view/'.($infos['controller']?str_replace('_','/',$infos['controller']).'/':'').$infos['action'].$suffix:$this->_frontController->getFolder().$router->getModule().'/view/default'.$suffix;
            require($path);
        }
    }

    public function renderTemplate(){
        foreach($this->_headers as $name=>$value)
           header($name.':'.$value);
        if($this->_noRender)
            return '';
        if(!isset($this->_template))
            $this->_template=$this->_configuration->view->template;
        $this->preRender();
        if($this->_template){
            $file=$this->_configuration->folder.$this->module.'/view/template/'.$this->_template.$this->_configuration->view->suffix;
            $path=file_exists($file)?$file:$this->_configuration->folder.$this->_frontController->module.'/view/template/'.$this->_template.$this->_configuration->view->suffix;
            require($path);
        }else
            $this->render();
        $this->postRender();
    }
    



    

    /**
     *@todo remove?
     * @param <type> $action
     */
    public function setRender($action,$controller=false){
        $this->_action=$action;
        if($controller)$this->_controller=$controller;
        $this->_views['base']=array('controller'=>$controller,'action'=>$action);
    }

   

    /*
	*
    */
    

    public function returnView($action,$controller=false) {
        $infos=array('action'=>$action,'controller'=>$controller);
         $path=file_exists($this->_frontController->getModuleDirectory().$this->_frontController->getModule().'/view/'.($infos['controller']?str_replace('_','/',$infos['controller']).'/':'').$infos['action'].$suffix)?$this->_frontController->getModuleDirectory().$this->_frontController->getModule().'/view/'.($infos['controller']?str_replace('_','/',$infos['controller']).'/':'').$infos['action'].$suffix:'module/'.$this->_frontController->getModule().'/view/default'.$suffix;
         ob_start();
         require($path);
         return ob_get_clean();
    }

    public function addView($action,$controller=false) {
        $this->_views[]=array('controller'=>$controller?$controller:false,'action'=>$action);
    }

}
class Mvc_Controller_Abstract_Exception extends Exception {

}
?>