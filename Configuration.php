<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader {
    /**
     *  contain all the configuration of the application and framework, can be loaded with file or array
     * @package CRUDsader
     */
    class Configuration extends Block {

        /**
         * return singletoned instances
         * @return static
         */
        public static function getInstance() {
            static $instance = NULL;
            if (NULL === $instance) {
                $instance = new static();
            }
            return $instance;
        }
        /**
         * this will be loaded in the configuration at the very begining
         * @access protected
         * @static
         * @var <type>
         */
        protected static $_defaults = array(
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
                'plugins' => array(
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
                        'associationComponentSelectClass' => '\\CRUDsader\\Form\\Component\\Association',
                        'reference' => 'internal',
                        'min' => 0,
                        'max' => '*',
                        'databaseIdField' => 'id'
                    ),
                    'attribute' => array(
                        'searchable' => true,
                        'input' => true,
                        'required' => false,
                        'html' => true
                    )
                ),
                'loader' => array(
                    'file' => 'orm.xml'
                )
            ),
            'query' => array(
                'limit' => 50// limit the number of object to that, all the time
            ),
            'session' => array(
                'path' => false
            ),
            'instances' => array(
                'configuration' => array(
                    'class' => '\\CRUDsader\\Configuration', 'singleton' => true
                ),
                'configuration.arrayLoader' => array(
                    'class' => '\\CRUDsader\\Adapter\\ArrayLoader\\Yaml', 
                    'singleton' => false
                ),
                'arrayLoader' => array('class' => '\\CRUDsader\\Adapter\\ArrayLoader\\Yaml', 'singleton' => false),
                'database' => array('class' => '\\CRUDsader\\Database', 'singleton' => true),
                'database.connector' => array('class' => '\\CRUDsader\\Adapter\\Database\\Connector\\Mysqli', 'singleton' => true),
                'database.descriptor' => array('class' => '\\CRUDsader\\Adapter\\Database\\Descriptor\\Mysqli', 'singleton' => true),
                'database.profiler' => array('class' => '\\CRUDsader\\Adapter\\Database\\Profiler\\Html', 'singleton' => true),
                'database.rows' => array('class' => '\\CRUDsader\\Adapter\\Database\\Rows\\Mysqli', 'singleton' => false),
                'i18n' => array('class' => '\\CRUDsader\\I18n', 'singleton' => true),
                'i18n.translation' => array('class' => '\\CRUDsader\\Adapter\\I18n\\Translation\\None', 'singleton' => true),
                'i18n.translation.yaml.arrayLoader' => array('class' => '\\CRUDsader\\Adapter\\ArrayLoader\\Yaml', 'singleton' => false),
                'object.identifier' => array('class' => '\\CRUDsader\\Adapter\\Identifier\\Hilo', 'singleton' => true),
                'map' => array('class' => '\\CRUDsader\\Map', 'singleton' => true),
                'map.extractor' => array('class' => '\\CRUDsader\\Adapter\\Map\\Extractor\\Database', 'singleton' => true),
                'map.loader' => array('class' => '\\CRUDsader\\Adapter\\Map\\Loader\\Xml', 'singleton' => true),
                'mvc.frontController' => array('class' => '\\CRUDsader\\MVC\\Controller\\Front', 'singleton' => true),
                'mvc.routerHistoric' => array('class' => '\\CRUDsader\\Adapter\\Mvc\\RouterHistoric\\Lilo', 'singleton' => true),
                'mvc.router' => array('class' => '\\CRUDsader\\Adapter\\Mvc\\Router\\Explicit', 'singleton' => true),
            )
        );

        /**
         * @test test_instance_defaults
         */
        public function __construct() {
            parent::__construct(self::$_defaults);
        }

        /**
         *
         * @param type $options 
         * @test test_load_
         */
        public function load($options) {
            $arrayLoader = Instancer::getInstance()->{'configuration.arrayLoader'}($options);
            $this->loadArray($arrayLoader->get());
        }
    }
    class ConfigurationException extends \CRUDsader\Exception {
        
    }
}