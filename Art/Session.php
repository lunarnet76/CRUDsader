<?php
/**
 * LICENSE:     see Art/license.txt
 *
 * @author      Jean-Baptiste Verrey <jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     http://www.Art.com/license/1.txt
 * @version     $Id$
 * @link        http://www.Art.com/manual/
 * @since       1.0
 */
namespace Art {
    /**
     * @package     Art
     */
    class Session extends Block {
        /**
         * wether the session is started or not
         * @access protected
         * @static
         * @var bool
         */
        protected static $_isStarted;
        /**
         * the framework uses $_SESSION[$_generalNamespace]
         * @access protected
         * @static
         * @var string
         */
        protected static $_generalNamespace = 'Art';

        /**
         * session_start()
         * @static
         */
        public static function start() {
            if (!self::$_isStarted) {
                $configuration = Configuration::getInstance();
                if (!empty($configuration->utility->session->path))
                    session_save_path($configuration->utility->session->path);
                if (!isset($_SESSION))
                    @session_start();
                if (!isset($_SESSION[self::$_generalNamespace]))
                    $_SESSION[self::$_generalNamespace] = array();
                self::$_isStarted = true;
            }
        }

        /**
         * to avoid conflict we uses namespaces
         * @param string $namespace
         * @static
         * @return self
         */
        public static function useNamespace($namespace) {
            if (!self::$_isStarted)
                self::start();
            if (!isset($_SESSION[self::$_generalNamespace][$namespace]))
                $_SESSION[self::$_generalNamespace][$namespace] = array();
            return new self($_SESSION[self::$_generalNamespace][$namespace]);
        }

        /**
         * if you want to define the general namespace to be something else than Art
         * @param string $namespace
         * @static
         */
        public static function setGeneralNamespace($namespace=false) {
            self::$_generalNamespace = $namespace ? $namespace : 'Art';
            if (!isset($_SESSION[self::$_generalNamespace]))
                $_SESSION[self::$_generalNamespace] = array();
        }

        /**
         * session_destroy
         * @static
         */
        public static function destroy() {
            $_SESSION[self::$_generalNamespace] = array();
        }

        /**
         * receive a reference to a $_SESSION var
         * @param &$_SESSION $index 
         */
        protected function __construct(&$index=NULL) {
            if (!self::$_isStarted)
                self::start();
            if (!isset($index))
                $this->_properties = &$_SESSION[self::$_generalNamespace];
            else
                $this->_properties = &$index;
        }
    }
}