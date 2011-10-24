<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader {
    /**
     * utility to include classes depending on their namespace or that follows the PEAR naming convention : My_Class mapped to My/Class.php
     * @abstract
     * @package CRUDsader
     */
    abstract class Autoload {
        /**
         * @access protected
         * @static
         * @var array the loaded classes, array($className=>$includedFile)
         */
        protected static $_includedClasses = array();
        /**
         * @access protected
         * @static
         * @var array($nameSpaceName=>$folder)
         */
        protected static $_nameSpaces = array();
        
        /**
         * @static
         * @var string
         */
        public static $simpleAutoloadPath='';

        /**
         * all classes from this nameSpace will be mapped to the specified folder
         * @param string $nameSpace
         * @param string $folder
         * @test test_Namespaces
         */
        public static function registerNameSpace($nameSpace, $folder) {
            self::$_nameSpaces[$nameSpace] = $folder;
        }

        /**
         * return if wether a nameSpace is mapped
         * @param string $nameSpace
         * @return bool
         * @test test_Namespaces
         */
        public static function hasNameSpace($nameSpace) {
            return isset(self::$_nameSpaces[$nameSpace]);
        }

        /**
         * return the folder mapped to the nameSpace
         * @param string $nameSpace
         * @return string
         * @test test_Namespaces
         */
        public static function getNamespace($nameSpace) {
            return self::$_nameSpaces[$nameSpace];
        }

        /**
         * unmap a namespace
         * @param string $name
         * @test test_Namespaces
         */
        public static function unregisterNameSpace($name) {
            unset(self::$_nameSpaces[$name]);
        }

        /**
         * provide the list of mapped namespaces
         * @return array
         * @test test_Namespaces
         */
        public static function getNamespaces() {
            return self::$_nameSpaces;
        }

        /**
         * return if a class has been included by the autoloader
         * @param string $className
         * @return bool
         * @est test_includeClass
         */
        public static function hasClass($className) {
            return isset(self::$_includedClasses[$className]);
        }

        /**
         * manually add an included class to the autoloader
         * @param string $className
         * @param string $filePath
         * @test test_includeClass
         */
        public static function includeClass($className, $filePath) {
            self::$_includedClasses[$className] = $filePath;
        }

        /**
         * manually remove an included class to the autoloader
         * @param string $className
         * @param string $filePath
         * @test_includeClass
         */
        public static function unincludeClass($className) {
            unset(self::$_includedClasses[$className]);
        }

        /**
         * load a class, for performance reason it does not check wether if the file exists
         * @param string $className
         */
        public static function autoloader($className) {
            $filePath = self::_findIncludePathFor($className);
            if ($filePath){
                 self::$_includedClasses[$className]=$filePath;
                require($filePath);
            }else
                throw new AutoloadException('class "' . $className . '" cannot be autoloaded');
        }

        /**
         * load a class simply following the PEAR naming convention
         * @param string $className
         */
        public static function simpleAutoload($className) {
            include(self::$simpleAutoloadPath.str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php');
        }
        
        /**
         * register the autoload and the default namespace
         * @static
         */
        public static function register(){
            self::registerNameSpace('CRUDsader', __DIR__.'/');
            spl_autoload_register(array('CRUDsader\Autoload', 'autoloader'));
        }
        
        /**
         * return the include path for the classname to be included, using the namespace
         * @static
         * @param string $className
         * @return string
         */
        protected static function _findIncludePathFor($className) {
            if (isset(self::$_includedClasses[$className]))
                return self::$_includedClasses[$className];
            $pos = strpos($className, '\\');
            if ($pos === false)
                return self::$simpleAutoloadPath.str_replace('_','/',$className).'.php';
            if ($pos === 0) {
                $pos = strpos($className, '\\', 1);
                if ($pos === false)
                    return false;
                $namespace = substr($className, 1, $pos - 1);
            }else
                $namespace = substr($className, 0, $pos);
            return isset(self::$_nameSpaces[$namespace]) ? self::$_nameSpaces[$namespace] . str_replace('\\', DIRECTORY_SEPARATOR, substr($className, $pos + 1)) . '.php' : false;
        }
    }
    class AutoloadException extends \Exception {
        
    }
}