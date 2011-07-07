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
     * singleton containing all the configuration of the application and framework, can be loaded with file or array
     * @package    Art
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
                'classNameSpace' => 'Art\Adapter',
                'database' => array(
                    'connector' => 'mysqli',
                    'descriptor' => 'mysqli',
                    'rows' => 'mysqli',
                    'profiler' => 'html'
                ),
                'i18n' => array(
                    'translation' => 'transparent'
                ),
                'identifier'=>array(
                    'hilo'=>array(
                        'highIdLength'=>10
                    )
                 ),
                'map'=>array(
                    'loader'=>array(
                        'xml'=>array(
                            'file'=>'map.xml'
                        )
                    )
                )
            ),
            'database' => array(
                'host' => 'localhost',
                'user' => 'root',
                'password' => '',
                'name' => 'artdb'
            ),
            'debug' => array(
                'php' => array(
                    'error' => true
                ),
                'database' => array(
                    'profiler' => true
                )
            ),
            'map' => array(
                'defaults' => array(
                    'inheritance'=>'table',
                    'attributeType'=>array(
                        'databaseType'=>'VARCHAR',
                        'options'=>array(),
                        'class'=>'default',
                        'polymorphism'=>array(
                            'databaseField'=>'isa',
                            'databaseType'=>'VARCHAR',
                            'length'=>'32',
                        )
                    ),
                    'attribute'=>array(
                        'type'=>'default'
                    ),
                    'associations'=>array(
                        'cardinality'=>'one-to-one',
                        'min'=>0,
                        'max'=>1
                    )
                )
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
            $lines = file($filePath);
            if ($lines === false)
                throw new ConfigurationException('file "' . $filePath . '" could not be read properly');
            $configuration = array();
            $depths = array();
            $lastDepth = 0;
            $stop = false;
            foreach ($lines as $lineNumber => $line) {
                switch ($line[0]) {
                    case '#':break; // comments
                    case '[':// namespace
                        if (!preg_match('|^\[([^\:\]\s]*)(\:([^\]\s\:]*)){0,1}\]\s+$|', $line, $match))
                            throw new ConfigurationException('file "' . $filePath . '":' . $lineNumber . ' error :"' . $line . '" is not a proper namespace');
                        if ($stop) {
                            $this->loadArray($configuration[$namespace]);
                            return;
                        }
                        $namespace = $match[1];
                        if ($namespace == $section || !$section) {
                            $stop = true;
                        }
                        $inherit = isset($match[3]) ? $match[3] : false;
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
                        } else if (preg_match('|^(\s*)([^:]*)\:\s*(.*).$|', $line, $match)) {// key: value
                            if (strlen($match[1]) / 4 == 0) {
                                $configuration[$namespace][$match[2]] = $match[3];
                            }
                            else
                                $depths[$depth][$match[2]] = $match[3];
                        }else {// is a value
                            $depths[$depth][] = $line;
                        }
                }
            }
            $this->loadArray($configuration);
        }
    }
    class ConfigurationException extends \Art\Exception {
        
    }
}