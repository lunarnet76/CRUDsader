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
abstract class Art_Adapter_Database_Connector_Abstract extends Art_Adapter_Abstract{
    /**
     * connect to the DB
     * @abstract
     */
    abstract public function connect();
    /**
     * execute a pure SQL string
     * @abstract
     * @param string $sql
     * @param string $type optional, the type of query like SELECT or UPDATE
     * @return Art_Database_Result
     */
    abstract public function query($sql,$type='');
    /**
     * prepare a SQL prepared statement
     * @abstract
     * @param string $sql
     * @param string $type optional, the type of query like SELECT or UPDATE
     */
    abstract public function prepareQueryStatement($sql,$type='');
    /**
     * execute a SQL prepared statement
     * @abstract
     * @param array $args the values to replaced the ? in the prepared statement
     * @return Art_Database_Result
     */
    abstract public function executeQueryStatement(array $args);
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
?>