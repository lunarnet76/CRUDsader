<?php
/**
 * LICENSE: see Art/license.txt
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
     * utility to include classes depending on their namespace or that follows the PEAR naming convention : My_Class mapped to My/Class.php
     * @package     Art
     * @abstract
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
         * all classes from this nameSpace will be mapped to the specified folder
         * @param string $nameSpace
         * @param string $folder
         */
        public static function registerNameSpace($nameSpace, $folder) {
            self::$_nameSpaces[$nameSpace] = $folder;
        }

        /**
         * return if wether a nameSpace is mapped
         * @param string $nameSpace
         * @return bool
         */
        public static function hasNameSpace($nameSpace) {
            return isset(self::$_nameSpaces[$nameSpace]);
        }

        /**
         * return the folder mapped to the nameSpace
         * @param string $nameSpace
         * @return string
         */
        public static function getNamespace($nameSpace) {
            return self::$_nameSpaces[$nameSpace];
        }

        /**
         * unmap a namespace
         * @param string $name
         */
        public static function unregisterNameSpace($name) {
            unset(self::$_nameSpaces[$name]);
        }

        /**
         * provide the list of mapped namespaces
         * @return array
         */
        public static function getNamespaces() {
            return self::$_nameSpaces;
        }

        /**
         * return if a class has been included by the autoloader
         * @param string $className
         * @return bool
         */
        public static function hasClass($className) {
            return isset(self::$_includedClasses[$className]);
        }

        /**
         * manually add an included class to the autoloader
         * @param string $className
         * @param string $filePath
         */
        public static function includeClass($className, $filePath) {
            self::$_includedClasses[$className] = $filePath;
        }

        /**
         * manually remove an included class to the autoloader
         * @param string $className
         * @param string $filePath
         */
        public static function unincludeClass($className) {
            unset(self::$_includedClasses[$className]);
        }

        /**
         * return if the autoloader can load a class
         * @param string $className
         * @return bool|string path to the file to be included to include this class
         */
        public static function isLoadable($className) {
            $filePath = self::_findIncludePathFor($className);
            if ($filePath && file_exists($filePath))
                return $filePath;
            return false;
        }

        /**
         * return the include path for the classname to be included, using the namespace
         * @static
         * @param <type> $className
         * @return <type>
         */
        protected static function _findIncludePathFor($className) {
            if (isset(self::$_includedClasses[$className]))
                return self::$_includedClasses[$className];
            if(isset(self::$_nameSpaces[true]))
                var_dump(self::$_nameSpaces[true]);
            $pos = strpos($className, '\\');
            if ($pos === false)
                return false;
            if ($pos === 0) {
                $pos = strpos($className, '\\', 1);
                if ($pos === false)
                    return false;
                $namespace = substr($className, 1, $pos - 1);
            }else
                $namespace = substr($className, 0, $pos);
            return isset(self::$_nameSpaces[$namespace]) ? self::$_nameSpaces[$namespace] . str_replace('\\', DIRECTORY_SEPARATOR, substr($className, $pos + 1)) . '.php' : false;
        }

        /**
         * load a class, checking if the namespace and file exists first
         * @param string $className
         */
        public static function load($className) {
            $filePath = self::isLoadable($className);
            if ($filePath)
                require($filePath);
            else
                throw new AutoloadException('class "' . $className . '" cannot be loaded');
        }

        /**
         * load a class, for performance reason it does not check wether if the file exists
         * @param <type> $className
         */
        public static function autoload($className) {
            $filePath = self::_findIncludePathFor($className);
            if ($filePath)
                include($filePath);
            else
                throw new AutoloadException('class "' . $className . '" cannot be autoloaded');
        }

        /**
         * load a class simply following the PEAR naming convention
         * @param <type> $className
         */
        public static function simpleAutoload($className) {
            include(str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php');
        }
    }
    class AutoloadException extends \Exception {
        
    }
}