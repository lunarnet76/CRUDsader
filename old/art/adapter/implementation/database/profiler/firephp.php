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
class Art_Adapter_Implementation_Database_Profiler_Firephp extends Art_Adapter_Database_Profiler_Abstract {

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
     * constructor
     */
    public function init() {
        $path = Art_Configuration::getInstance()->debug->oarams->firephp->Path;
        require_once($path . 'fb.php');
        Fb::setOptions(array('includeLineNumbers' => true));
    }

    /**
     * start logging query
     * @param string $sql
     * @param string $type
     */
    public function startQuery($sql, $type) {
        $this->_query = $sql;
        Art_Debug::chrono_start('FPDP');
    }

    /**
     * stop logging query
     * @param int $count number of objects
     * @param null|array $results display the results
     */
    public function stopQuery($count=false, $results=null) {
        $time = Art_Debug::chrono_time('FPDP');
        $this->_log[] = array('query' => $this->_query, 'time' => $time . '  [' . $count . ' results]', 'results' => $results);
        $this->_totalTime+=$time;
    }

    /**
     * stop logging a query, display exception
     * @param string $message the exception message
     */
    public function stopQueryWithException($message) {
        $time = Art_Debug::chrono_time('FPDP');
        $this->_log[] = array('query' => $this->_query, 'exception' => $message);
        $this->display();
    }

    /**
     * start logging a query statement
     * @param string $sql
     * @param string $type
     */
    public function startQueryStatement($sql, $type='') {
        $this->_query = $sql;
        Art_Debug::chrono_start('FPDPS');
    }

    /**
     * stop logging a query statement
     * @param int $count number of objects
     * @param array|null  $results number of rows or true
     * @param array $args
     */
    public function stopQueryStatement($count=false, $results, array $args) {
        $time = Art_Debug::chrono_time('FPDPS');
        $this->_log[] = array('query' => $this->_query, 'time' => $time . '  [' . $count . ' results]', 'results' => $results, 'args' => array($args));
        $this->_totalTime+=$time;
    }

    /**
     * stop logging a query statement, display exception
     * @param string $message the exception message
     */
    public function stopQueryStatementWithException($message) {
        $time = Art_Debug::chrono_time('FPDPS');
        $this->_log[] = array('query' => $this->_query, 'time' => $time, 'exception' => $message);
        $this->_totalTime+=$time;
        $this->display();
    }

    /**
     * display the logs
     */
    public function display() {
        $this->_log = array_reverse($this->_log);
        Fb::log($this->_totalTime, 'Time used');
        foreach ($this->_log as $key => $log) {
            if (isset($log['displayed']))
                continue;
            Fb::log('________________________________________________________________________________________________________________________________________');
            if (isset($log['args'])) {
                Fb::table('ARGUMENTS', $log['args']);
            }
            Fb::log($log['query']);
            if (isset($log['exception']))
                Fb::log($log['exception'], 'EXCEPTION');
            else {
                if (isset($log['results']))
                    Fb::table($log['time'], $log['results']);
                else
                    Fb::log($log['time']);
            }
            $this->_log[$key]['displayed'] = true;
        }
    }

    public function getLogs() {
        return $this->_log;
    }

}
?>