<?php

/**
 * DB connector adapter
 *
 * LICENSE: see Art/license.txt
 *
 * @author Jean-Baptiste Verrey <jeanbaptiste.verrey@gmail.com>
 * @copyright  2010 Jean-Baptiste Verrey
 * @license    http://www.Art.com/license/2.txt
 * @version    $Id$
 * @link       http://www.Art.com/manual/
 * @since      2.0
 */

/**
 * @category   Adapter
 * @package    Art2
 */
abstract class Art_Adapter_Database_Descriptor_Abstract extends Art_Adapter_Abstract {
    const FIELD_COUNTING_ALIAS='counting';
    /**
     * quote table name or table field
     * @abstract
     * @static
     * @param string $identifier
     * @return string
     */
    abstract public  function quoteIdentifier($identifier);

    /**
     * quote a field value, do not quote Art_Database_Expression object
     * @abstract
     * @static
     * @param string $value
     * @return string
     */
    abstract public function quote($value);

    /**
     * select and join tables
     * @abstract
     * @param array $from
     * @param string $where
     * @param array $joins
     * @param string $orderBy
     * @param int $limit
     * @param int $start
     * @return string
     * $from = Array($alias=>$table,'fields'=>array($fieldAlias=>$fieldName))
     * $joins=array(array($table=>$alias,'fields'=>array($fieldAlias=>$fieldName),'join'=>array($table1,$tablefield1,$table2,$tableField2),'type'=>$type)));
     * LIMIT and START are NOT the number of rows BUT the number of rows with distinct $from.id
     */
    abstract public function select($from, $where=false, $joins=false, $limit=false, $orderBy=false, $start=false, $count=false, &$args=false, $idField='id');

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
     * create a table
     * @abstract
     * @param string $name
     * @param array $fields an array of ('name'=>array('null'=>$bool,'type'=>$type,'autoincrement'=>$bool))
     * @param array $primaryKeys
     * @param array $indexes
     * @param bool $ifNotExists create only if not exists
     * @return string
     */
    abstract public function createTable($name, array $fields, array $primaryKeys, array $indexes, $ifNotExists=false);

    /**
     * list all the available fields type possible
     * @abstract
     * @return string
     */
    abstract public function getAvailableFieldType();
}
?>