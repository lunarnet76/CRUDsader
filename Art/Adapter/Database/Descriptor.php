<?php
/**
 * LICENSE: see Art/license.txt
 *
 * @author     Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     http://www.Art.com/license/2.txt
 * @version     $Id$
 * @link        http://www.Art.com/manual/
 * @since       1.0
 */
namespace Art\Adapter\Database {
    /**
     * DB Descriptor adapter
     *
     * @package    Art\Adapter\Database
     */
    abstract class Descriptor extends \Art\Adapter {
        public static $FIELD_COUNTING_ALIAS = 'counting';
        public static $TABLE_LIMIT_ALIAS = 'limittable';
        public static $ID_FIELD = 'id';

        /**
         * quote table name or table field
         * @abstract
         * @static
         * @param string $identifier
         * @return string
         */
        abstract public function quoteIdentifier($identifier);

        /**
         * quote a field value, does not quote \Art\Database\Expression object
         * @abstract
         * @static
         * @param string $value
         * @return string
         */
        abstract public function quote($value);

        /**
         * @abstract
         * @param \Art\Database\Sql\Select $select $select
         */
        abstract public function select(\Art\Database\Select $select);

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
         * split a SQL string into colored parts
         * @param string $sql
         * @return string
         */
        abstract public function highLight($sql);

        /**
         * list all the tables
         * @return string
         */
        abstract public function listTable();

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
    }
}