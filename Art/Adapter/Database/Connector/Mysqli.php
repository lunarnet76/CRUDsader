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
namespace Art\Adapter\Database\Connector {
    /**
     * MySQL connector
     * @package     Art
     * @abstract
     */
    class Mysqli extends \Art\Adapter\Database\Connector {
        /**
         * @var ressource
         */
        protected $_connection = NULL;
        /**
         * @var string
         */
        protected $_preparedStatement = NULL;

        /**
         * connect to the DB
         */
        public function connect($force=false) {
            if (!isset($this->_connection) || $force) {
                $instanceConnection = @new \mysqli($this->_configuration->host, $this->_configuration->user, $this->_configuration->password, $this->_configuration->name);
                if ($instanceConnection->connect_error)
                    throw new MysqliException('Connection failed: ' . $instanceConnection->connect_error);
                $this->_connection = $instanceConnection;
            }
        }

        /**
         * disconnect from the DB
         */
        public function disconnect() {
            if (!isset($this->_connection))
                return;
            if (false === $this->_connection->close())
                throw new MysqliException('can\'t disconnect from database');
        }

        /**
         * real_escape_string
         * @abstract
         */
        public function escape($string) {
            if (!isset($this->_connection))
                $this->connect();
            return $this->_connection->real_escape_string($string);
        }

        /**
         * execute a pure SQL string
         * @abstract
         * @param string $sql
         * @param string $type optional, the type of query like SELECT or UPDATE
         * @return Database\Result
         */
        public function query($sql, $type='select') {
            if (!isset($this->_connection))
                $this->connect();
            $resource = $this->_connection->query($sql);
            if (false === $resource)
                throw new MysqliException($this->_connection->error, $sql, $this->_connection->errno);
            switch ($type) {
                case 'select':
                case 'listTable':
                    $rows = array();
                    $rowSet = \Art\Adapter::Factory(array('database' => 'rows'));
                    $rowSet->setResource($resource, $resource->num_rows);
                    $ret = $rowSet;
                    break;
                case 'delete':
                case 'update':
                    $ret = $this->_connection->affected_rows;
                    break;
                default:
                    $ret = true;
            }
            return $ret;
        }

        /**
         * wether to check or not for foreign keys
         */
        public function setForeignKeyCheck($bool=true) {
            $this->query('SET FOREIGN_KEY_CHECKS = ' . ($bool ? '1' : '0'), 'set');
        }

        /**
         * prepare a SQL prepared statement, the native MySQL driver does not support it, so we just simulate it
         * @abstract
         * @param string $sql
         * @param string $type optional, the type of query like SELECT or UPDATE
         */
        public function prepareQueryStatement($sql, $type='select') {
            $statment = $this->_connection->prepare($sql);
            if (!$statment)
                throw new MysqliException('preparing statment failed', $sql);
            $this->_preparedStatement = array('sql' => $sql, 'type' => $type, 'statment' => $statment);
        }

        /**
         * execute a SQL prepared statement
         * @abstract
         * @param array $args the values to replaced the ? in the prepared statement
         * @return \Art\Database\Result
         */
        public function executeQueryStatement(array $args) {
            if (!isset($this->_connection))
                $this->connect();
            if (!isset($this->_preparedStatement))
                throw new MysqliException('you must define a prepared statement before executing it');
            $stmt = $this->_preparedStatement['statment'];

            $ref = new \ReflectionClass('mysqli_stmt');
            $method = $ref->getMethod('bind_param');

            $args2 = array(0 => '');
            foreach ($args as $k => $v) {
                $args2[$k + 1] = (string) $v;
                $args2[0].='s';
            }

            $method->invokeArgs($stmt, $args2);

            $execute = $stmt->execute();
            if (!$execute)
                throw new MysqliException($stmt->error);
            switch ($this->_preparedStatement['type']) {
                case 'select':
                    $array = array();


                    $stmt->store_result();

                    $variables = array();
                    $data = array();
                    $meta = $stmt->result_metadata();

                    while ($field = $meta->fetch_field())
                        $variables[] = &$data[$field->name];

                    call_user_func_array(array($stmt, 'bind_result'), $variables);

                    $i = 0;
                    while ($stmt->fetch()) {
                        $array[$i] = array();
                        foreach ($data as $k => $v)
                            $array[$i][$k] = $v;
                        $i++;
                    }
                    return $array;
                case 'insert':
                case 'delete':
                    return $stmt->affected_rows;
            }
        }

        /**
         * begin a transaction
         * @abstract
         */
        public function beginTransaction() {
            if (!isset($this->_connection))
                $this->connect();
            $this->query('START TRANSACTION', 'transaction');
            $this->query('BEGIN', 'transaction');
        }

        /**
         * end a transaction with a commit
         * @abstract
         */
        public function commit() {
            $this->query('COMMIT', 'transaction');
        }

        /**
         * end a transaction with a rollbak
         * @abstract
         */
        public function rollBack() {
            $this->query('ROLLBACK', 'transaction');
        }
    }
    class MysqliException extends \Art\Exception {
        protected $_sql = false;

        public function __construct($message, $sql=false, $errorNo=false) {
            $this->message = $message;
            $this->_sql = $sql;
            $this->code = $errorNo;
        }

        public function getSQL() {
            return $this->_sql;
        }
    }
}