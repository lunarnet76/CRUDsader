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
class Art_Adapter_Implementation_Database_Profiler_Html extends Art_Adapter_Database_Profiler_Abstract {

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
        $this->_query = (string)$sql;
        Art_Debug::chrono_start('PHDP');
    }

    /**
     * stop logging query
     * @param int $count number of objects
     * @param null|array $results display the results
     */
    public function stopQuery($count=false, $results=null) {
        $time = Art_Debug::chrono_time('PHDP');
        $this->_log[] = array('query' => $this->_query, 'time' => $time . '  [' . $count . ' results]', 'results' => $results);
        $this->_totalTime+=$time;
    }

    /**
     * stop logging a query, display exception
     * @param string $message the exception message
     */
    public function stopQueryWithException($message) {
        $time = Art_Debug::chrono_time('PHDP');
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
        Art_Debug::chrono_start('PHDPS');
    }

    /**
     * stop logging a query statement
     * @param int $count number of objects
     * @param array|null  $results number of rows or true
     * @param array $args
     */
    public function stopQueryStatement($count=false, $results, array $args) {
        $time = Art_Debug::chrono_time('PHDPS');
        $this->_log[] = array('query' => $this->_query, 'time' => $time . '  [' . $count . ' results]', 'results' => $results, 'args' => array($args));
        $this->_totalTime+=$time;
    }

    /**
     * stop logging a query statement, display exception
     * @param string $message the exception message
     */
    public function stopQueryStatementWithException($message) {
        $time = Art_Debug::chrono_time('PHDPS');
        $this->_log[] = array('query' => $this->_query, 'time' => $time, 'exception' => $message);
        $this->_totalTime+=$time;
        $this->display();
    }

    /**
     * display the logs
     */
    public function display() {
?>
<style type="text/css">
            .profiler{
                font-size: 12px;
                border:2px solid black;
            }
            .log{
                padding: 15px;
                margin-bottom: 20px;
                border:2px solid black;
            }
            .timeTotal{
                padding: 15px;
                border:2px solid black;
                margin-bottom: 20px;
            }
            .exception{
                font-weight: bold;
                color:#ae1414;
            }
            div.results{
                border: 1px solid #CCC;
            }
            div.results table{
                padding: 2px;
                width: 100%;
            }
            div.results tr:hover{
                background-color: #CCC;
            }
            div.results td{
                border: 1px solid #CCC;
                padding: 1px;
                font-size:11px;
            }
            div.results td.header{
                font-weight: bold;
            }
        </style>
<?php

        $highlighter = Art_Adapter_Factory::getInstance(array('database' => 'descriptor'));
        echo '<div class="profiler">';
        $this->_log = array_reverse($this->_log);
        echo '<div class="timeTotal">Time used :' . $this->_totalTime . '</div>';
        foreach ($this->_log as $key => $log) {
            if (isset($log['displayed']))
                continue;
            echo '<div class="log">';

            echo '<div class="container query">';
            Art_Debug::pre($highlighter->highLight($log['query']), 'SQL '.(isset($log['time'])?$log['time']:'not executed'));
            echo '</div>';
            if (isset($log['args'])) {
                echo '<div class="container">';
                foreach ($log['args'] as $argarray)
                    foreach ($argarray as $arg)
                        echo $arg . ',';
                echo '</div>';
            }
            if (isset($log['exception']))
                echo '<div class="container exception">EXCEPTION:' . $log['exception'] . '</div>';
            else {
                if (isset($log['results'])) {
                    echo '<div class="container results"><table>';
                    $first=true;
                    if(!count($log['results']))echo '<tr><td class="header">no results</td></tr>';
                    foreach($log['results'] as $r){
                        if($first){
                            $first=false;
                            foreach($r as $k=>$o)echo '<td class="header">'.$k.'</td>';
                        }
                        echo '<tr>';
                        foreach($r as $k=>$o)echo '<td>'.$o.'</td>';
                        echo '</tr>';
                    }
                    echo '</table></div>';
                }else
                    echo '<div class="container time">' . $log['time'] . '</div>';
            }

            echo '</div>';
            $this->_log[$key]['displayed'] = true;
        }
    }

    public function getLogs() {
        return $this->_log;
    }

}
?>