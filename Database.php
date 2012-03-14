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
     * @package CRUDsader
     */
    class Database extends MetaClass{
        /**
         * identify the class
         * @var string
         */
        protected $_classIndex='database';
        
        /**
         * the list of dependencies
         * @var array
         */
        protected $_hasDependencies = array('connector','descriptor','profiler');
        
        /**
         * @var bool wether or not to profile
         */
        protected $_profile=false;

        /**
         * @var string
         */
        protected $_sql = false;

        /**
         * @param \CRUDsader\Block $block 
         */
        public function setConfiguration(\CRUDsader\Block $block=null){
            parent::setConfiguration($block);
            if (\CRUDsader\instancer::getInstance()->debug->getConfiguration()->databaseProfiler)
                $this->_profile=true;
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
         * @return \CRUDsader\Database\Rows
         */
        public function query($sql, $type='') {
            $this->_sql = $sql;
            if (!$this->_profile)
                return $this->_dependencies['connector']->query($sql, $type);
            $this->_dependencies['profiler']->startQuery($sql, $type);
            try {
                $results = $this->_dependencies['connector']->query($sql, $type);
                if ($results instanceof \CRUDsader\Database\Rows) {
                    $this->_dependencies['profiler']->stopQuery($results->count(), $results->toArray());
                    $results->rewind();
                }else
                    $this->_dependencies['profiler']->stopQuery($results);
                return $results;
            } catch (Exception $e) {
                $this->_dependencies['profiler']->stopQueryWithException($e->getMessage());
                throw $e;
            }
        }

        /**
         * prepare a SQL statment
         * @param string $sql
         * @param string $type
         * @return \CRUDsader\Database\Rows
         */
        public function prepareQueryStatement($sql, $type='') {
            $this->_sql = $sql;
            if (!$this->_profile)
                return $this->_dependencies['connector']->prepareQueryStatement($sql, $type);;
            $this->_dependencies['profiler']->startQueryStatement($sql, $type);
            try {
                return $this->_dependencies['connector']->prepareQueryStatement($sql, $type);
            } catch (Exception $e) {
                $this->_dependencies['profiler']->stopQueryWithException($e->getMessage());
                throw $e;
            }
        }

        /**
         * execute query using an array of arguments 
         * @param array $args
         * @return array|int|bool 
         */
        public function executeQueryStatement(array $args) {
            if (!$this->_profile)
                return $this->_dependencies['connector']->executeQueryStatement($args);
            try {
                $results = $this->_dependencies['connector']->executeQueryStatement($args);
                if (is_array($results))
                    $this->_dependencies['profiler']->stopQueryStatement(count($results), $results, $args);
                else
                    $this->_dependencies['profiler']->stopQueryStatement($results, null, $args);
                return $results;
            } catch (Exception $e) {
                $this->_dependencies['profiler']->stopQueryStatementWithException($e->getMessage());
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
                case 'last_insert_id':
                    return call_user_func_array(array($this->_dependencies['connector'], $name), $arguments);
                    break;
                case 'quote':
                case 'quoteIdentifier':
                    return call_user_func_array(array($this->_dependencies['descriptor'], $name), $arguments);
                    break;
                case 'select':
                case 'countSelect':
                case 'listTables':
                case 'insert':
                case 'update':
                case 'delete':
                case 'createTable':
                case 'createTableReference':
                    return $this->query(call_user_func_array(array($this->_dependencies['descriptor'], $name), $arguments), $name);
                    break;
                case 'highLight':
                    return call_user_func_array(array($this->_dependencies['descriptor'], $name), $arguments);
                    break;
                default:
                    throw new DatabaseException('call to undefined function "' . $name . '"');
            }
        }
    }
    class DatabaseException extends \CRUDsader\Exception {
        
    }
}