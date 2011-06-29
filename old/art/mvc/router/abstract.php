<?php
/**
 * Abstract router
 *
 * LICENSE: see Art/license.txt
 *
 * @author Jean-Baptiste Verrey <jeanbaptiste.verrey@gmail.com>
 * @copyright  2010 Jean-Baptiste Verrey
 * @license    http://www.Art.com/license/2.txt
 * @version    $Id$
 * @link       http://www.Art.com/manual/
 * @since      2.0
 */

/**
 * @category   MVC
 * @package    Art2
 */
abstract class Art_Mvc_Router_Abstract {
    /*
     * @var Art_Block
     */
    protected $_configuration=NULL;
    /*
     * @var string
     */
    protected $_uri=NULL;
    /*
     * @var string
     */
    protected $_module=NULL;
    /*
     * @var string
     */
    protected $_controller=NULL;
    /*
     * @var string
     */
    protected $_action=NULL;
    /*
     * @var string
     */
    protected $_params='';
    
    public function __construct(Art_Block $configuration){
        $this->_configuration=$configuration;
    }

    public function getModule(){return $this->_module;}
    public function getController(){return $this->_controller;}
    public function getAction(){return $this->_action;}
    public function getParams(){return $this->_params;}
    public function getUri(){return $this->_uri;}
    public function setModule($module){$this->_module=$module;}
    public function setController($controller){$this->_controller=$controller;}
    public function setAction($action){$this->_action=$action;}
    public function setParams($params){$this->_params=$params;}
    public function setUri($uri){$this->_uri=$uri;}

    public function parseParams($string){
        if(empty($string))return array();
         $ex=explode($this->_configuration->router->separator->params, $string);
         $count = count($ex);
         $ret=array();
         for ($i = 0; $i < $count; $i+=2)
               $ret[$ex[$i]] = isset($ex[$i + 1]) ? $ex[$i + 1] : true;
         return $ret;
    }

    public function parseParamsInverse(array $array){
        $ret='';
        foreach($array as $k=>$v)
            $ret.=$this->_configuration->router->separator->params.$k.$this->_configuration->router->separator->params.$v;
       return $ret;
    }

    abstract public function route();
    
}
?>
