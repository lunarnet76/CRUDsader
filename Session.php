<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader {
    /**
     * @package     CRUDsader
     */
    class Session extends Block {
        /**
         * wether the session has been started or not
         * @access protected
         * @static
         * @var bool
         */
        protected static $_init;
        /**
         * the framework uses $_SESSION[$_globalNamespace]
         * @access protected
         * @static
         * @var string
         */
        protected static $_globalNamespace = 'CRUDsader';

        /**
         * session_start()
         * @static
         * @test test_start
         */
        public static function start() {
            if (!self::$_init) {
                $configuration = Configuration::getInstance();
                if (!empty($configuration->session->path))
                    session_save_path($configuration->session->path);
                if (!isset($_SESSION))
                    @session_start();
                if (!isset($_SESSION[self::$_globalNamespace]))
                    $_SESSION[self::$_globalNamespace] = array();
                self::$_init = true;
            }
        }

        /**
         * to avoid conflict we uses namespaces
         * @param string $namespace
         * @static
         * @return self
         * @test test_useNamespace
         */
        public static function useNamespace($namespace) {
            if (!self::$_init)
                self::start();
            if (!isset($_SESSION[self::$_globalNamespace][$namespace]))
                $_SESSION[self::$_globalNamespace][$namespace] = array();
            return new self($_SESSION[self::$_globalNamespace][$namespace]);
        }

        /**
         * if you want to define the general namespace to be something else than CRUDsader
         * @param string $namespace
         * @static
         * @test_setGlobalNamespace
         */
        public static function setGlobalNamespace($namespace=false) {
            self::$_globalNamespace = $namespace ? $namespace : 'CRUDsader';
            if (!isset($_SESSION[self::$_globalNamespace]))
                $_SESSION[self::$_globalNamespace] = array();
        }

        /**
         * session_destroy
         * @static
         * @test test_destroy
         */
        public static function destroy() {
            unset($_SESSION[self::$_globalNamespace]);
            $_SESSION[self::$_globalNamespace] = array();
        }

        /**
         * receive a reference to a $_SESSION var
         * @access protected
         * @param &$_SESSION $index 
         */
        protected function __construct(&$index=NULL) {
            if (!self::$_init)
                self::start();
            if (!isset($index))
                $this->_properties = &$_SESSION[self::$_globalNamespace];
            else
                $this->_properties = &$index;
        }
    }
}