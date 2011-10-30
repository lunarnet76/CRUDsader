<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader {
    /**
     * DBAL
     * @package     CRUDsader
     */
    class Database implements Interfaces\Adaptable, Interfaces\Configurable {
        
        /**
         * list of all adapters
         * @var array 
         */
        protected $_adapters = array();

        /**
         * @var string
         */
        protected $_sql = false;

        /**
         * @access protected
         */
        public function __construct() {
            $di=Instancer::getInstance();
            $this->setConfiguration($di->configuration->database);
            $this->_adapters['descriptor'] = $di->{'database.descriptor'};
        }
        
        /**
         * @param \CRUDsader\Block $block 
         */
        public function setConfiguration(\CRUDsader\Block $block=null){
            $di=Instancer::getInstance();
            $this->_configuration=$block;
            if ($di->configuration->debug->database->profiler)
                $this->_adapters['profiler'] = $di->{'database.profiler'};
            $this->_adapters['connector'] = $di->{'database.connector'};
            $this->_adapters['connector']->setConfiguration($this->_configuration);
        }
        
        /**
         * @return  \CRUDsader\Block $block 
         */
        public function getConfiguration(){
            return $this->_configuration;
        }
        
        /**
         * @param string $name
         * @return bool
         */
        public function hasAdapter($name=false) {
            return isset($this->_adapters[$name]);
        }

        /**
         * @param string $name
         * @return \CRUDsader\Adapter
         */
        public function getAdapter($name=false) {
            return $this->_adapters[$name];
        }

        /**
         * @return array
         */
        public function getAdapters() {
            return $this->_adapters;
        }

        /**
         * get last SQL query
         * @return string 
         */
        public function getSql() {
            return $this->_sql;
        }

        
        

        /**
         * execute a SQL query, return is dependant of the $type
         * @param string $sql
         * @param string $type
         * @return \CRUDsader\Adapter\Database\Rows
         */
        public function query($sql, $type='') {
            $this->_sql = $sql;
            if (!$this->hasAdapter('profiler'))
                return $this->_adapters['connector']->query($sql, $type);
            $this->_adapters['profiler']->startQuery($sql, $type);
            try {
                $results = $this->_adapters['connector']->query($sql, $type);
                if ($results instanceof \CRUDsader\Adapter\Database\Rows) {
                    $this->_adapters['profiler']->stopQuery($results->count(), $results->toArray());
                    $results->rewind();
                }else
                    $this->_adapters['profiler']->stopQuery($results);
                return $results;
            } catch (Exception $e) {
                $this->_adapters['profiler']->stopQueryWithException($e->getMessage());
                throw $e;
            }
        }

        /**
         * prepare a SQL statment
         * @param string $sql
         * @param string $type
         * @return \CRUDsader\Adapter\Database\Rows
         */
        public function prepareQueryStatement($sql, $type='') {
            $this->_sql = $sql;
            if (!$this->hasAdapter('profiler'))
                return $this->_adapters['connector']->prepareQueryStatement($sql, $type);;
            $this->_adapters['profiler']->startQueryStatement($sql, $type);
            try {
                return $this->_adapters['connector']->prepareQueryStatement($sql, $type);
            } catch (Exception $e) {
                $this->_adapters['profiler']->stopQueryWithException($e->getMessage());
                throw $e;
            }
        }

        /**
         * execute query using an array of arguments 
         * @param array $args
         * @return array|int|bool 
         */
        public function executeQueryStatement(array $args) {
            if (!$this->hasAdapter('profiler'))
                return $this->_adapters['connector']->executeQueryStatement($args);
            try {
                $results = $this->_adapters['connector']->executeQueryStatement($args);
                if (is_array($results))
                    $this->_adapters['profiler']->stopQueryStatement(count($results), $results, $args);
                else
                    $this->_adapters['profiler']->stopQueryStatement($results, null, $args);
                return $results;
            } catch (Exception $e) {
                $this->_adapters['profiler']->stopQueryStatementWithException($e->getMessage());
                throw $e;
            }
        }

        /**
         * shortcuts
         * @param string $name
         * @param mix $arguments
         * @return mix
         */
        public function __call($name, $arguments) {
            switch ($name) {
                case 'setForeignKeyCheck':
                case 'beginTransaction':
                case 'commit':
                case 'rollBack':
                case 'isConnected':
                case 'isInTransaction':
                case 'escape':
                    return call_user_func_array(array($this->_adapters['connector'], $name), $arguments);
                    break;
                case 'quote':
                case 'quoteIdentifier':
                    return call_user_func_array(array($this->_adapters['descriptor'], $name), $arguments);
                    break;
                case 'select':
                case 'countSelect':
                case 'listTables':
                case 'insert':
                case 'update':
                case 'delete':
                case 'createTable':
                case 'createTableReference':
                    return $this->query(call_user_func_array(array($this->_adapters['descriptor'], $name), $arguments), $name);
                    break;
                case 'highLight':
                    return call_user_func_array(array($this->_adapters['descriptor'], $name), $arguments);
                    break;
                default:
                    throw new DatabaseException('call to undefined function "' . $name . '"');
            }
        }
    }
    class DatabaseException extends \CRUDsader\Exception {
        
    }
}