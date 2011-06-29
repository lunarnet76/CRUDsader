<?php
/**
 * metaclass for the framework
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
 * @category   Exception
 * @package    Art2
 */
class Art_Exception extends Exception{
    protected $_exception;
    protected $_args;
    public function __construct($errorOrException,$args=array()){
        if($errorOrException instanceof Exception){
            $this->_exception=$errorOrException;
        }
        else $this->message=$errorOrException;
        $this->_args=$args;
    }

    public function __toString(){
        if(Art_Debug::isActivated()){
            ob_start();
            Art_Debug::pre($this);
            return ob_get_clean();
        }else
            return $this->getMessage();
    }
}
?>
