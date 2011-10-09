<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Adapter\Database {
    /**
     * DB Descriptor adapter
     * @package    CRUDsader\Adapter\Database
     */
    abstract class Descriptor extends \CRUDsader\Adapter {
        public static $FIELD_COUNTING_ALIAS = 'counting';
        public static $TABLE_LIMIT_ALIAS = 'limittable';
        public static $OBJECT_ID_FIELD_ALIAS = 'distinctId';
        public static $OBJECT_TMP_TABLE_ALIAS = 'object';
        public static $TABLE_ALIAS_SUBQUERY = '___sq';

        /**
         * @var \CRUDsader\Adapter\Database\Connector 
         */
        protected $_connector;
        
        /**
         * set the connector
         * @param \CRUDsader\Adapter\Database\Connector $connector 
         */
        public function setConnector(\CRUDsader\Adapter\Database\Connector $connector){
            $this->_connector=$connector;
        }
        
        /**
         * quote table name or table field
         * @abstract
         * @static
         * @param string $identifier
         * @return string
         */
        abstract public function quoteIdentifier($identifier);

        /**
         * quote a field value, does not quote \CRUDsader\Database\Expression object
         * @abstract
         * @static
         * @param string $value
         * @param \CRUDsader\Adapter\Database\Connector $connector=null $connector
         * @return string
         */
        abstract public function quote($value);

        /**
         * insert values in a table
         * @abstract
         * @param string $table
         * @param array $values
         * @return string
         */
        abstract public function insert($table, array $values);

        /**
         * update values in a table
         * @abstract
         * @param string $table
         * @param array $values
         * @param string $where
         * @return string
         */
        abstract public function update($table, array $values, $where=false);

        /**
         * delete from a table
         * @abstract
         * @param string $tabl
         * @param string $where
         * @return string
         */
        abstract public function delete($table, $where);

        /**
         * list all the tables
         * @return string
         */
        abstract public function listTables();

        /**
         * delete a table
         * @abstract
         * @param string $tableName
         * @return string
         */
        abstract public function deleteTable($tableName);

        /**
         * CREATE TABLE
         * @param string $name
         * @param array $fields array('col1'=>array('null'=>$bool,'type'=>$type,'length'=>$intOrFloatOrFalse))
         * @param array $identity array('col1','col2')
         * @param string $surrogateKey array('type'=>$type,'length'=>$int,'name'=>$name)
         * @param array $indexes array('index1'=>array('col1','col2'))
         * @return bool 
         */
        abstract public function createTable($name, array $fields,array $identity=array(),array $surrogateKey=array(),array $indexes=array());
        
         /**
         * create a reference between 2 fields of 2 tables
         * @param type $infos=array('fromTable','toTable','fromField','toField','onUpdate','onDelete') 
         */
        abstract public function createTableReference($infos);
        
         /**
         * @abstract
         * @param array $select 
         */
        abstract public function select($select);
        
        /**
         * @abstract
         * @param array $select 
         */
        abstract public function countSelect($select);
        
        /**
         * split a SQL string into colored Parts
         * @param string $sql
         * @return string
         */
        abstract public function highLight($sql);
    }
}