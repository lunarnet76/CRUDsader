<?php
/**
 * LICENSE: see CRUDsader/license.txt
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
     * singleton containing all the configuration of the application and framework, can be loaded with file or array
     * @package    CRUDsader
     */
    class Configuration extends Block {
        /**
         * @access protected
         * @static
         * @var self
         */
        protected static $_instance;

        /**
         * singleton instance
         * @return self
         */
        public static function getInstance() {
            if (!isset(self::$_instance))
                self::$_instance = new self();
            return self::$_instance;
        }
        /**
         * this will be loaded in the configuration at the very begining
         * @access protected
         * @static
         * @var <type>
         */
        protected static $_defaults = array(
            'adapter' => array(
                'classNameSpace' => 'CRUDsader\Adapter',
                'database' => array(
                    'connector' => 'mysqli',
                    'descriptor' => 'mysqli',
                    'rows' => 'mysqli',
                    'profiler' => 'html'
                ),
                'i18n' => array(
                    'translation' => 'none'
                ),
                'identifier' => 'hilo',
                'map' => array(
                    'loader' => array(
                        'xml' => array(
                            'file' => 'orm.xml'
                        )
                    ),
                    'extractor' => array(
                        'database' => array(
                        )
                    )
                ),
                'mvc' => array(
                    'router' =>'explicit',
                    'routerHistoric' => 'lilo'
                )
            ),
            'database' => array(
                'host' => 'localhost',
                'user' => 'root',
                'password' => '',
                'name' => 'CRUDsaderdb'
            ),
            'debug' => array(
                'php' => array(
                    'error' => true
                ),
                'database' => array(
                    'profiler' => false
                )
            ),
            'i18n' => array(
                'timezone' => 'Europe/London', //http://php.net/manual/en/function.date-default-timezone-set.php
            ),
            'form' => array(
                'view' => array(
                    'path' => ''
                )
            ),
            'mvc' => array(
                'baseRewrite' => '',
                'applicationPath' => '',
                'controllerDir' => '',
                'route' => array(
                    'suffix' => '.html'
                ),
                'default' => array(
                    'module' => '',
                    'controller' => 'default',
                    'action' => 'default'
                ),
                'view' => array(
                    'template' => false,
                    'suffix' => 'php'
                )
            ),
            'map' => array(
                'defaults' => array(
                    'idField' => 'id',
                    'inheritance' => 'table',
                    'phpClass' => '\\CRUDsader\\Object',
                    'attributeType' => array(
                        'databaseType' => 'VARCHAR',
                        'options' => array(),
                        'class' => 'String',
                        'phpClass' => '\\CRUDsader\\Object\\Attribute\\Wrapper\\'
                    ),
                    'attribute' => array(
                        'type' => 'default',
                        'searchable' => true
                    ),
                    'associations' => array(
                        'reference' => 'internal',
                        'min' => 0,
                        'max' => 1,
                        'databaseIdField' => 'id'
                    )
                )
            ),
            'query' => array(
                'limit' => 50// limit the number of object to that, all the time
            ),
            'session' => array(
                'path' => false
            )
        );

        /**
         * singletoned constructor
         * @access protected
         */
        protected function __construct() {
            parent::__construct(self::$_defaults);
        }

        /**
         * load configuration from a file
         * @param string $filePath
         * @param string $section
         */
        public function load($filePath, $section=false) {
            $lines = @file($filePath);
            if ($lines === false)
                throw new ConfigurationException('file "' . $filePath . '" could not be read properly');
            $configuration = array();
            $depths = array();
            $lastDepth = $depth = 0;
            $namespace = false;
            foreach ($lines as $lineNumber => $line) {
                switch ($line[0]) {
                    case '#':break; // comments
                    case '[':// namespace
                        if (!preg_match('|^\[([^\:\]\s]*)(\:([^\]\s\:]*)){0,1}\]\s*$|', $line, $match))
                            throw new ConfigurationException('file "' . $filePath . '":' . $lineNumber . ' error :"' . $line . '" is not a proper namespace');
                        
                        if ($section && $namespace == $section) {
                            break 2;
                        }
                        $namespace = $match[1];
                        $configuration[$namespace] = array();
                        if (isset($match[3])) {
                            if (!isset($configuration[$match[3]]))
                                throw new ConfigurationException('section "' . $namespace . '" cannot inherit from unexistant section "' . $match[3] . '"');
                            $configuration[$namespace] = $configuration[$match[3]];
                        }
                        break;
                    default:// config line
                        if (preg_match('|^(\s*)([^:]*)\:\s*$|', $line, $match)) {// key:
                            $depth = strlen($match[1]) / 4;
                            $name = $match[2];
                            if ($depth == 0) {
                                $configuration[$namespace][$match[2]] = array();
                                $depths[$depth] = &$configuration[$namespace][$match[2]];
                            } else if ($depth == $lastDepth) {
                                $depths[$depth - 1][$match[2]] = array();
                                $depths[$depth] = &$depths[$depth - 1][$match[2]];
                            } else if ($depth > $lastDepth) {
                                $depths[$lastDepth][$match[2]] = array();
                                $depths[$depth] = &$depths[$lastDepth][$match[2]];
                            } else {
                                $depths[$lastDepth - $depth - 1][$match[2]] = array();
                                $depths[$depth] = &$depths[$lastDepth - $depth - 1][$match[2]];
                            }
                            $lastDepth = $depth;
                        } else if (preg_match('|^(\s*)([^:]*)\=\s*(.*)$|', $line, $match)) {// key: value
                            if (strlen($match[1]) / 4 == 0) {
                                $configuration[$namespace][$match[2]] = rtrim($match[3]);
                            }
                            else
                                $depths[$depth][$match[2]] = rtrim($match[3]);
                        }else {// is a value
                            $depths[$depth][] = $line;
                        }
                }
            }
            if ($section && !isset($configuration[$section]))
                throw new ConfigurationException('section "' . $section . '" does not exist');
            $this->loadArray($section ? $configuration[$section] : $configuration);
        }

        /**
         * load a INI file
         * @param string $iniFilePath path of the file
         * @param string|mix $section the name of the section
         */
        public function loadIniFile($iniFilePath) {
            if (!file_exists($iniFilePath))
                throw new Art_Configuration_Exception('File <b>' . $iniFilePath . '</b> does not exists');
            $properties = @parse_ini_file($iniFilePath, true);
            if ($properties === false)
                throw new Art_Configuration_Exception('File <b>' . $iniFilePath . '</b> could not be loaded as a configuration INI file');
            $finalProperties = array();
            foreach ($properties as $section => $property) {
                $ex = explode(':', $section);
                $child = trim($ex[0]);
                $parent = isset($ex[1]) ? trim($ex[1]) : false;
                if (!isset($finalProperties[$child]))
                    $finalProperties[$child] = array();
                if ($parent && isset($finalProperties[$parent]))
                    foreach ($finalProperties[$parent] as $key => $value)
                        $finalProperties[$child][$key] = $value;
                foreach ($property as $key => $value) {
                    $pos = strpos($key, '.');
                    if ($pos !== false) {
                        $var = '[\'' . str_replace('.', '\'][\'', $key) . '\']';
                        eval('$finalProperties[\'' . $child . '\']' . $var . '=$value;');
                    } else
                        $finalProperties[$child][$key] = $value;
                }
            }
            $this->loadArray($finalProperties);
            //echo '<pre>';print_r($this->toArray());
        }
    }
    class ConfigurationException extends \CRUDsader\Exception {
        
    }
}