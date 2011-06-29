<?php
/**
 * All adapters are singletoned
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
 * @category   Adapter
 * @package    Art2
 */
abstract class Art_Adapter_Abstract {
    private static $_instances=array();

    public static function getInstanceOf($class){
        if(!isset(self::$_instances[$class]))
            self::$_instances[$class]=new $class();
        return self::$_instances[$class];
    }
    /**
     * singletoned instance
     * @access protected
     */
    final protected function __construct() {
        $this->init();
    }
    
    protected function init(){}
    /**
     * @static
     * @return self
     */
     public static function getInstance(){
         throw new Exception('You must implement public static function getInstance(){} in your class');
     }
}
?>
