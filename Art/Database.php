<?php
/**
 * LICENSE:     see Art/license.txt
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
     * @package     Art
     */
    class Database extends Singleton {
        protected $_connectorAdapter;
        protected $_descriptorAdapter;
        protected $_profilerAdapter;
        protected $_resultSetAdapter;
        protected $_configuration;

        /**
         * singletoned constructor
         * @access protected
         */
        public function init() {
            $configuration = Configuration::getInstance();
            $this->_configuration = $configuration->database;
            $this->_connectorAdapter = Adapter::factory(array('database' => 'connector'));
            $this->_connectorAdapter->setConfiguration($this->_configuration);
            $this->_descriptorAdapter = Adapter::factory(array('database' => 'descriptor'));
            if ($configuration->debug->database->profiler)
                $this->_profilerAdapter = Adapter::factory(array('database' => 'profiler'));
        }

        /**
         * @return \Art\Adapter\Database\Connector 
         */
        public function getConnector() {
            return $this->_connectorAdapter;
        }

        /**
         * @return \Art\Adapter\Database\Descriptor 
         */
        public function getDescriptor() {
            return $this->_descriptorAdapter;
        }

        /**
         * @return bool 
         */
        public function hasProfiler() {
            return isset($this->_profilerAdapter);
        }

        /**
         *
         * @return \Art\Adapter\Database\Profiler  
         */
        public function getProfiler() {
            return $this->_profilerAdapter;
        }

        /**
         * quote a value
         * @param string $string
         * @return string 
         */
        public function quote($string) {
            return $this->_descriptorAdapter->quote($string);
        }

        /**
         * quote an identifier (e.g. column name)
         * @param string $string
         * @return string 
         */
        public function quoteIdentifier($string) {
            return $this->_descriptorAdapter->quoteIdentifier($string);
        }
        

        /**
         * execute a SQL query, return is dependant of the $type
         * @param string $sql
         * @param string $type
         * @return \Art\Adapter\Database\Rows
         */
        public function query($sql, $type='') {
            if (!$this->hasProfiler())
                return $this->_connectorAdapter->query($sql, $type);
            $this->_profilerAdapter->startQuery($sql, $type);
            try {
                $results = $this->_connectorAdapter->query($sql, $type);
                if ($results instanceof \Art\Adapter\Database\Rows)
                    $this->_profilerAdapter->stopQuery($results->count(), $results->toArray());
                else
                    $this->_profilerAdapter->stopQuery($results);
                return $results;
            } catch (Exception $e) {
                $this->_profilerAdapter->stopQueryWithException($e->getMessage());
                throw $e;
            }
        }

         /**
         * prepare a SQL statment
         * @param string $sql
         * @param string $type
         * @return \Art\Adapter\Database\Rows
         */
        public function prepareQueryStatement($sql, $type='') {
            if (!$this->hasProfiler())
                return $this->_connectorAdapter->prepareQueryStatement($sql, $type);;
            $this->_profilerAdapter->startQueryStatement($sql, $type);
            try {
                return $this->_connectorAdapter->prepareQueryStatement($sql, $type);
            } catch (Exception $e) {
                $this->_profilerAdapter->stopQueryWithException($e->getMessage());
                throw $e;
            }
        }

        /**
         * execute query using an array of arguments 
         * @param array $args
         * @return array|int|bool 
         */
        public function executeQueryStatement(array $args) {
            if (!$this->hasProfiler())
                return $this->_connectorAdapter->executeQueryStatement($args);
            try {
                $results = $this->_connectorAdapter->executeQueryStatement($args);
                if (is_array($results))
                    $this->_profilerAdapter->stopQueryStatement(count($results), $results, $args);
                else
                    $this->_profilerAdapter->stopQueryStatement($results, null, $args);
                return $results;
            } catch (Exception $e) {
                $this->_profilerAdapter->stopQueryStatementWithException($e->getMessage());
                throw $e;
            }
        }

        /**
         * start a transaction
         */
        public function beginTransaction() {
            $this->_connectorAdapter->beginTransaction();
        }

        /**
         * ends a transaction with a commit
         */
        public function commit() {
            $this->_connectorAdapter->commit();
        }

        /**
         * ends a transaction with a rollback
         */
        public function rollBack() {
            $this->_connectorAdapter->rollBack();
        }

        public function insert($table, array $values) {
            return $this->query($this->_descriptorAdapter->insert($table, $values), 'insert');
        }

        public function select(\Art\Database\Select $select) {
            return $this->query($this->_descriptorAdapter->select($select), 'select');
        }

        public function update($table, array $values, $where=false) {
            return $this->query($this->_descriptorAdapter->update($table, $values, $where), 'insert');
        }

        public function delete($table, $where) {
            return $this->query($this->_descriptorAdapter->delete($table, $where), 'delete');
        }

        /**
         * CREATE TABLE
         * @param string $name
         * @param array $fields array('col1'=>array('null'=>$bool,'type'=>$type,'length'=>$intOrFloatOrFalse))
         * @param array $identity array('col1','col2')
         * @param string $surrogateKey array('type'=>$type,'length'=>$int,'name'=>$name)
         * @param array $foreignKeys=array('col1'=>array('table'=>$table,'field'=>$field,'onUpdate'=>$up,'onDelete'=>$del),'col2');
         * @param array $indexes array('index1'=>array('col1','col2'))
         * @return bool 
         */
        public function createTable($name, array $fields,array $identity=array(),array $surrogateKey=array(),array $foreignKeys=array(),array $indexes=array()) {
            return $this->query($this->_descriptorAdapter->createTable($name,$fields, $identity,$surrogateKey,$foreignKeys,$indexes), 'createTable');
        }
    }
}