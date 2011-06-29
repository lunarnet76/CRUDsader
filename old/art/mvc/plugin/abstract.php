<?php
/**
 * abstract pluggin
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
abstract class Art_Mvc_Plugin_Abstract {
    protected $_configuration=array();

    public function __construct($configuration){
        $this->_configuration=$configuration;
        $this->init();
    }

    public function init(){}
    public function postRoute(Art_Mvc_Router_Abstract $router){}
    public function preDispatch(){}
    public function postDispatch(){}
}
?>
