<?php
/**
 * LICENSE:     see CRUDsader/license.txt
 *
 * @author      Jean-Baptiste Verrey <jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     http://www.CRUDsader.com/license/1.txt
 * @version     $Id$
 * @link        http://www.CRUDsader.com/manual/
 * @since       1.0
 */
namespace CRUDsader {
    /**
     * @package     CRUDsader
     */
    class Session extends Block {
        /**
         * wether the session is started or not
         * @access protected
         * @static
         * @var bool
         */
        protected static $_isstarted;
        /**
         * the framework uses $_SESSION[$_generalNamespace]
         * @access protected
         * @static
         * @var string
         */
        protected static $_generalNamespace = 'CRUDsader';

        /**
         * session_start()
         * @static
         */
        public static function start() {
            if (!self::$_isstarted) {
                $configuration = Configuration::getInstance();
                if (!empty($configuration->session->path))
                    session_save_path($configuration->session->path);
                if (!isset($_SESSION))
                    @session_start();
                if (!isset($_SESSION[self::$_generalNamespace]))
                    $_SESSION[self::$_generalNamespace] = array();
                self::$_isstarted = true;
            }
        }

        /**
         * to avoid conflict we uses namespaces
         * @param string $namespace
         * @static
         * @return self
         */
        public static function useNamespace($namespace) {
            if (!self::$_isstarted)
                self::start();
            if (!isset($_SESSION[self::$_generalNamespace][$namespace]))
                $_SESSION[self::$_generalNamespace][$namespace] = array();
            return new self($_SESSION[self::$_generalNamespace][$namespace]);
        }

        /**
         * if you want to define the general namespace to be something else than CRUDsader
         * @param string $namespace
         * @static
         */
        public static function setGeneralNamespace($namespace=false) {
            self::$_generalNamespace = $namespace ? $namespace : 'CRUDsader';
            if (!isset($_SESSION[self::$_generalNamespace]))
                $_SESSION[self::$_generalNamespace] = array();
        }

        /**
         * session_destroy
         * @static
         */
        public static function destroy() {
            unset($_SESSION[self::$_generalNamespace]);
            $_SESSION[self::$_generalNamespace] = array();
        }

        /**
         * receive a reference to a $_SESSION var
         * @param &$_SESSION $index 
         */
        protected function __construct(&$index=NULL) {
            if (!self::$_isstarted)
                self::start();
            if (!isset($index))
                $this->_properties = &$_SESSION[self::$_generalNamespace];
            else
                $this->_properties = &$index;
        }
    }
}