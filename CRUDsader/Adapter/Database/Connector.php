<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Adapter\Database {
    /**
     * DB Connector adapter
     * @package    CRUDsader\Adapter\Database
     */
    abstract class Connector extends \CRUDsader\Adapter{
        /**
         * @var ressource
         */
        protected $_inTransaction = false;
        
        /**
         * check if connector is in transaction
         * @return bool
         */
        public function isInTransaction(){
            return $this->_transaction;
        }
        
        /**
         * connect to the DB
         * @abstract
         */
        abstract public function connect();

        /**
         * disconnect from the DB
         * @abstract
         */
        abstract public function disconnect();
        
        /**
         * wether we are connected to the DB
         * @abstract
         * @return bool
         */
        abstract public function isConnected();
        
        /**
         * real_escape_string
         * @abstract
         */
        abstract public function escape($string);

        /**
         * execute a pure SQL string
         * @abstract
         * @param string $sql
         * @param string $type optional, the type of query like SELECT or UPDATE
         * @return CRUDsader_Database_Result
         */
        abstract public function query($sql, $type='select');

        /**
         * prepare a SQL prepared statement
         * @abstract
         * @param string $sql
         * @param string $type optional, the type of query like SELECT or UPDATE
         */
        abstract public function prepareQueryStatement($sql, $type='select');

        /**
         * execute a SQL prepared statement
         * @abstract
         * @param array $args the values to replaced the ? in the prepared statement
         * @return CRUDsader_Database_Result
         */
        abstract public function executeQueryStatement(array $args);

        /**
         * wether to check or not for foreign keys
         * @abstract
         */
        abstract public function setForeignKeyCheck($bool=true);
        
        /**
         * begin a transaction
         * @abstract
         */
        abstract public function beginTransaction();

        /**
         * end a transaction with a commit
         * @abstract
         */
        abstract public function commit();

        /**
         * end a transaction with a rollbak
         * @abstract
         */
        abstract public function rollBack();
    }
    class ConnectorException extends \CRUDsader\Exception {
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