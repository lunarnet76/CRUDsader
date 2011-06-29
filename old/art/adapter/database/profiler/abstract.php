<?php

/**
 * DB profiler adapter
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
 * @category   Adapter,Database
 * @package    Art2
 */
abstract class Art_Adapter_Database_Profiler_Abstract extends Art_Adapter_Abstract {

    /**
     * start logging query
     * @abstract
     * @param string $sql
     * @param string $type
     */
    abstract public function startQuery($sql, $type);

    /**
     * stop logging query
     * @abstract
     * @param int $count number of objects
     * @param null|array $results display the results
     */
    abstract public function stopQuery($count=false, $results=null);

    /**
     * stop logging a query, display exception
     * @abstract
     * @param string $message the exception message
     */
    abstract public function stopQueryWithException($message);

    /**
     * start logging a query statement
     * @abstract
     * @param string $sql
     * @param string $type
     */
    abstract public function startQueryStatement($sql, $type='');

    /**
     * stop logging a query statement
     * @param int $count number of objects
     * @param array|null $results number of rows or true
     * @param array $args
     */
    abstract public function stopQueryStatement($count=false, $results=null, array $args);
    
    /**
     * stop logging a query statement, display exception
     * @abstract
     * @param string $message the exception message
     */
    abstract public function stopQueryStatementWithException($message);

    /**
     * display the logs
     * @abstract
     */
    abstract public function display();
}
?>