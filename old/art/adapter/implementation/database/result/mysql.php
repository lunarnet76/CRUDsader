<?php
/**
 * DB results adapter for MySQL
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
class Art_Adapter_Implementation_Database_Result_Mysql extends Art_Adapter_Database_Result_Abstract {
    protected $_current = false;
    protected $_iterated = false;
    protected $_results;
    protected $_count;
    protected $_sql;

    /**
     * @param ressource $results a mysql_query ressource
     * @param string $sql
     * @param int $count number of OBJECTS (and not rows)
     */
    public function __construct($results,$sql,$count=false){
        $this->_results=$results;
        $this->_count=$count;
        $this->_sql=$sql;
    }

     /**
     * @return the number of OBJECTS (and not rows)
     */
    public function count(){
        return $this->_count;
    }
    
    public function rewind() {
        if(!$this->_count)return;
        if (!$this->_iterated) {
            $this->_iterated = true;
            $this->_current = mysql_fetch_assoc($this->_results);
        } else {
            mysql_data_seek($this->_results, 0);
            $this->_current = mysql_fetch_assoc($this->_results);
        }
    }

    public function valid() {
        return $this->_current !== false;
    }

    public function current() {
        return $this->_current;
    }

    public function key() {
        return key($this->_current);
    }

    public function next() {
        $this->_current = mysql_fetch_assoc($this->_results);
        return $this->_current;
    }

    public function toArray(){
        $array = array();
        foreach($this as $v)
            $array[]=$v;
        return $array;
    }
}
?>