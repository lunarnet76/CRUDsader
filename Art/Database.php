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
    class Database extends Singleton implements Interfaces\Adaptable{
        protected $_adapters=array();
        
        /**
         * @param string $name
         * @return bool
         */
        public function hasAdapter($name=false){
            return isset($this->_adapters[$name]);
        }

        /**
         * @param string $name
         * @return \Art\Adapter
         */
        public function getAdapter($name=false){
            return $this->_adapters[$name];
        }

        /**
         * singletoned constructor
         * @access protected
         */
        public function init() {
            $configuration = Configuration::getInstance();
            $this->_configuration = $configuration->database;
            $this->_adapters['connector'] = Adapter::factory(array('database' => 'connector'));
            $this->_adapters['connector']->setConfiguration($this->_configuration);
            $this->_adapters['descriptor'] = Adapter::factory(array('database' => 'descriptor'));
            if ($configuration->debug->database->profiler)
                $this->_adapters['profiler'] = Adapter::factory(array('database' => 'profiler'));
        }
        
        /**
         * wether to check or not for foreign keys
         */
        public function setForeignKeyCheck($bool=true){
            $this->_adapters['connector']->setForeignKeyCheck($bool);
        }

        /**
         * quote a value
         * @param string $string
         * @return string 
         */
        public function quote($string) {
            return $this->_adapters['descriptor']->quote($string);
        }

        /**
         * quote an identifier (e.g. field name)
         * @param string $string
         * @return string 
         */
        public function quoteIdentifier($string) {
            return $this->_adapters['descriptor']->quoteIdentifier($string);
        }
        

        /**
         * execute a SQL query, return is dependant of the $type
         * @param string $sql
         * @param string $type
         * @return \Art\Adapter\Database\Rows
         */
        public function query($sql, $type='') {
            if (!$this->hasAdapter('profiler'))
                return $this->_adapters['connector']->query($sql, $type);
            $this->_adapters['profiler']->startQuery($sql, $type);
            try {
                $results = $this->_adapters['connector']->query($sql, $type);
                if ($results instanceof \Art\Adapter\Database\Rows)
                    $this->_adapters['profiler']->stopQuery($results->count(), $results->toArray());
                else
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
         * @return \Art\Adapter\Database\Rows
         */
        public function prepareQueryStatement($sql, $type='') {
            if (!$this->hasProfiler())
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
            if (!$this->hasProfiler())
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
         * start a transaction
         */
        public function beginTransaction() {
            $this->_adapters['connector']->beginTransaction();
        }

        /**
         * ends a transaction with a commit
         */
        public function commit() {
            $this->_adapters['connector']->commit();
        }

        /**
         * ends a transaction with a rollback
         */
        public function rollBack() {
            $this->_adapters['connector']->rollBack();
        }

        public function insert($table, array $values) {
            return $this->query($this->_adapters['descriptor']->insert($table, $values), 'insert');
        }

        public function select(\Art\Database\Select $select,array $args=null) {
            return $this->query($this->_adapters['descriptor']->select($select,$args), 'select');
        }

        public function update($table, array $values, $where=false) {
            return $this->query($this->_adapters['descriptor']->update($table, $values, $where), 'insert');
        }

        public function delete($table, $where) {
            return $this->query($this->_adapters['descriptor']->delete($table, $where), 'delete');
        }

        /**
         * CREATE TABLE
         * @param string $name
         * @param array $fields array('col1'=>array('null'=>$bool,'type'=>$type,'length'=>$intOrFloatOrFalse))
         * @param array $identity array('col1','col2')
         * @param string $surrogateKey array('type'=>$type,'length'=>$int,'name'=>$name)
         * @param array $indexes array('index1'=>array('col1','col2'))
         * @return bool 
         */
        public function createTable($name, array $fields,array $identity=array(),array $surrogateKey=array(),array $indexes=array()) {
            return $this->query($this->_adapters['descriptor']->createTable($name,$fields, $identity,$surrogateKey,$indexes), 'createTable');
        }
        
        /**
         * create a reference between 2 fields of 2 tables
         * @param type $infos=array('fromTable','toTable','fromField','toField','onUpdate','onDelete') 
         */
        public function createTableReference($infos){
            return $this->query($this->_adapters['descriptor']->createTableReference($infos), 'createTableReference');
        }
    }
}