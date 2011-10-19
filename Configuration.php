<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader {
    /**
     * singleton containing all the configuration of the application and framework, can be loaded with file or array
     * @package CRUDsader
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
                    'router' => 'explicit',
                    'routerHistoric' => 'lilo'
                ),
                'arrayLoader'=>'yaml'
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
                'default' => array(
                    'module' => '',
                    'controller' => 'default',
                    'action' => 'default'
                ),
                'view' => array(
                    'template' => false,
                    'suffix' => 'php'
                ),
                'route' => array(
                    'suffix' => '.html',
                    'separator' => '/'
                ),
                'plugins'=>array(
                    
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
                        'phpClass' => '\\CRUDsader\\Object\\Attribute\\'
                    ),
                    'associations' => array(
                        'compositionComponentClass'=>'\\CRUDsader\\Form\\Component\\Composition',
                        'reference' => 'internal',
                        'min' => 0,
                        'max' => '*',
                        'databaseIdField' => 'id'
                    ),
                    'attribute'=>array(
                        'searchable'=>true,
                        'input'=>true,
                        'required'=>false,
                        'html'=>true
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
         * @test test_instance_defaults
         */
        protected function __construct() {
            parent::__construct(self::$_defaults);
        }
        
        /**
         *
         * @param type $options 
         * @test test_load_
         */
        public function load($options){
            $loader=\CRUDsader\Adapter::factory('arrayLoader');
            $this->loadArray($loader->load($options));
        }
    }
    class ConfigurationException extends \CRUDsader\Exception {
        
    }
}