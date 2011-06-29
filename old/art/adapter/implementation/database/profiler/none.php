<?php

/**
 * DB profiler firephp
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
class Art_Adapter_Implementation_Database_Profiler_None extends Art_Adapter_Database_Profiler_Abstract {

    /**
     * the actual SQL logged
     * @var string
     */
    protected $_query;
    /**
     * the list of all logged queries
     * @var array
     */
    protected $_log = array();
    /**
     * total elapsed time between displays
     * @var float
     */
    protected $_totalTime = 0;

    /**
     * @static
     * @return self
     */
    public static function getInstance() {
        return parent::getInstanceOf(__CLASS__);
    }

    /**
     * start logging query
     * @param string $sql
     * @param string $type
     */
    public function startQuery($sql, $type) {
       
    }

    /**
     * stop logging query
     * @param int $count number of objects
     * @param null|array $results display the results
     */
    public function stopQuery($count=false, $results=null) {
        
    }

    /**
     * stop logging a query, display exception
     * @param string $message the exception message
     */
    public function stopQueryWithException($message) {
       
    }

    /**
     * start logging a query statement
     * @param string $sql
     * @param string $type
     */
    public function startQueryStatement($sql, $type='') {
        
    }

    /**
     * stop logging a query statement
     * @param int $count number of objects
     * @param array|null  $results number of rows or true
     * @param array $args
     */
    public function stopQueryStatement($count=false, $results, array $args) {
        
    }

    /**
     * stop logging a query statement, display exception
     * @param string $message the exception message
     */
    public function stopQueryStatementWithException($message) {
        
    

   
    }

    public function display(){}

}
?>