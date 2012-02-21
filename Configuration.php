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
         * this will be loaded in the configuration at the very begining
         * @access protected
         * @static
         * @var <type>
         */
        protected static $_defaults = array(
            'database' => array(),
            'database.connector' => array(
                'host' => 'localhost',
                'user' => 'root',
                'password' => '',
                'name' => 'CRUDsaderdb'
            ),
            'debug' => array(
                'error' => false,
                'database' => false,
                'redirection' => false
            ),
            'i18n' => array(
                'timezone' => 'Europe/London'
            ),
            'i18n.translation' => array(
                'file' => ''
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
                    'controller' => 'core',
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
                        'html' => true,
                        'json' => true
                    )
                )
            ),
            'map.loader' => array(
                'file' => 'orm.xml'
            ),
            'query' => array(
                'limit' => 50// limit the number of object to that, all the time
            ),
            'session' => array(
                'path' => false
            ),
            'instances' => array(
                'configuration' => array(
                    'class' => '\\CRUDsader\\Configuration', 'singleton' => true),
                'block' => array(
                    'class' => '\\CRUDsader\\Block', 'singleton' => false),
                'configuration.arrayLoader' => array(
                    'class' => '\\CRUDsader\\ArrayLoader\\Yaml', 'singleton' => false),
                'arrayLoader' => array(
                    'class' => '\\CRUDsader\\ArrayLoader\\Yaml', 'singleton' => false),
                'database' => array(
                    'class' => '\\CRUDsader\\Database', 'singleton' => true),
                'debug' => array(
                    'class' => '\\CRUDsader\\Debug', 'singleton' => true),
                'database.connector' => array(
                    'class' => '\\CRUDsader\\Database\\Connector\\Mysqli', 'singleton' => true),
                'database.descriptor' => array(
                    'class' => '\\CRUDsader\\Database\\Descriptor\\Mysqli', 'singleton' => true),
                'database.profiler' => array(
                    'class' => '\\CRUDsader\\Database\\Profiler\\Html', 'singleton' => true),
                'database.rows' => array(
                    'class' => '\\CRUDsader\\Database\\Rows\\Mysqli', 'singleton' => false),
                'i18n' => array(
                    'class' => '\\CRUDsader\\I18n', 'singleton' => true),
                'i18n.translation' => array(
                    'class' => '\\CRUDsader\\I18n\\Translation\\None', 'singleton' => true),
                'i18n.translation.arrayLoader' => array(
                    'class' => '\\CRUDsader\\ArrayLoader\\Yaml', 'singleton' => false),
                'object' => array(
                    'class' => '\\CRUDsader\\Object', 'singleton' => false),
                'object.identifier' => array(
                    'class' => '\\CRUDsader\\Object\\Identifier\\Hilo', 'singleton' => true),
                'object.collection' => array(
                    'class' => '\\CRUDsader\\Object\\Collection', 'singleton' => false),
                'object.unitOfWork' => array(
                    'class' => '\\CRUDsader\\Object\\UnitOfWork', 'singleton' => false),
                'object.proxy' => array(
                    'class' => '\\CRUDsader\\Object\\Proxy', 'singleton' => false),
                'form' => array(
                    'class' => '\\CRUDsader\\Form', 'singleton' => false),
                'object.collection.association' => array(
                    'class' => '\\CRUDsader\\Object\\Collection\\Association', 'singleton' => false),
                'object.collection.initialised' => array(
                    'class' => '\\CRUDsader\\Object\\Collection\\Initialised', 'singleton' => false),
                'query' => array(
                    'class' => '\\CRUDsader\\Query', 'singleton' => false),
                'map' => array(
                    'class' => '\\CRUDsader\\Map', 'singleton' => true),
                'expression' => array(
                    'class' => '\\CRUDsader\\Expression', 'singleton' => false),
                'expression.null' => array(
                    'class' => '\\CRUDsader\\Expression\\Nil', 'singleton' => false),
                'map.extractor' => array(
                    'class' => '\\CRUDsader\\Map\\Extractor\\Database', 'singleton' => true),
                'map.loader' => array(
                    'class' => '\\CRUDsader\\Map\\Loader\\Xml', 'singleton' => true),
                'mvc.frontController' => array(
                    'class' => '\\CRUDsader\\MVC\\Controller\\Front', 'singleton' => true),
                'mvc.routerHistoric' => array(
                    'class' => '\\CRUDsader\\MVC\\RouterHistoric\\Lilo', 'singleton' => true),
                'mvc.router' => array(
                    'class' => '\\CRUDsader\\MVC\\Router\\Explicit', 'singleton' => true)
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
            $arrayLoader = Instancer::getInstance()->{'configuration.arrayLoader'};
            $this->loadArray($arrayLoader->load($options));
        }
    }
    class ConfigurationException extends \CRUDsader\Exception {
        
    }
}