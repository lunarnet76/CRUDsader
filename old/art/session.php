<?php
/**
 * utility for sessions
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
 * @category   Session
 * @package    Art2
 */
class Art_Session extends Art_Block{
    protected static $_isStarted;
    protected static $_configuration;
    protected static $_generalNamespace='Art';

     public function __construct(&$index=NULL) {
        if(!self::$_isStarted)
            self::start();
        if(!isset($index))
            $this->_properties=&$_SESSION[self::$_generalNamespace];
        else
            $this->_properties=&$index;
    }

    public static function setGeneralNamespace($namespace=false){
        self::$_generalNamespace=$namespace?$namespace:'Art';
    }

    public static function useNamespace($namespace){
        if(!self::$_isStarted)
            self::start();
        if(!isset($_SESSION['Art'][$namespace]))
            $_SESSION['Art'][$namespace]=array();
        return new self($_SESSION['Art'][$namespace]);
    }

    public static function start(){
        if(self::$_isStarted)return;
        $configuration=Art_Configuration::getInstance();
        if(!empty($configuration->session->path))
            session_save_path($configuration->session->path);
        if(!isset($_SESSION))
            session_start();
        if(!isset($_SESSION['Art']))
            $_SESSION['Art']=array();
        self::$_isStarted=true;
    }

    public static function resetAll() {
        session_destroy();
        $_SESSION['Art']=array();
    }
}
?>
