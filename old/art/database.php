<?php
 /**
 * DBAL, connection are made only when there is a query on the database
 * This DBAL is exclusively intended to be used by Art itself, the function to ease the use of SQL might not work otherwise (eg. with a complex SELECT, the descriptor::select() might not work)
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
 * @category   Database
 * @package    Art2
 */
class Art_Database{
    protected $_connectorAdapter;
    protected $_descriptorAdapter;
    protected $_profilerAdapter;
    protected $_resultSetAdapter;
    protected $_configuration;

    /**
     * @staticvar singletoned instance
     */
    protected static $_instance;

    /**
     * @static
     * @return self
     */
    public static function getInstance() {
        if (!isset(self::$_instance))
            self::$_instance = new self();
        return self::$_instance;
    }
    /**
     * singletoned instance
     * @access protected
     */
    protected function __construct() {
        $this->_connectorAdapter=Art_Adapter_Factory::getInstance(array('database'=>'connector'));
        $this->_descriptorAdapter=Art_Adapter_Factory::getInstance(array('database'=>'descriptor'));
        if(Art_Debug::isActivated())
            $this->_profilerAdapter=Art_Adapter_Factory::getInstance(array('database'=>'profiler'));
    }

    public function getConnector(){
        return $this->_connectorAdapter;
    }

    public function getDescriptor(){
        return $this->_descriptorAdapter;
    }

    public function hasProfiler(){
        return isset($this->_profilerAdapter);
    }
    
    public function getProfiler(){
        return $this->_profilerAdapter;
    }

    public function beginTransaction(){
        $this->_connectorAdapter->beginTransaction();
    }

    public function commit(){
        $this->_connectorAdapter->commit();
    }

    public function rollBack(){
        $this->_connectorAdapter->rollBack();
    }

    public function query($sql,$type=''){
        if(!$this->hasProfiler())
                return $this->_connectorAdapter->query($sql,$type);
        $this->_profilerAdapter->startQuery($sql,$type);
        try{
            $results=$this->_connectorAdapter->query($sql,$type);
            if($results instanceof Art_Adapter_Database_Result_Abstract)
                $this->_profilerAdapter->stopQuery($results->count(),$results->toArray());
            else
                $this->_profilerAdapter->stopQuery($results);
            return $results;
        }catch(Exception $e){
            $this->_profilerAdapter->stopQueryWithException($e->getMessage());
            if($e->getCode()==1054)pre((string)$sql);
            throw $e;
        }
    }

    public function quote($string){
        if($string instanceof Art_Database_Expression && $string->__toString()=='NOW()'){
            $string=date('Y-m-d');
        }
        return $this->_descriptorAdapter->quote($string);
    }

    public function quoteIdentifier($string){
        return $this->_descriptorAdapter->quoteIdentifier($string);
    }
    
    public function prepareQueryStatement($sql,$type=''){
        if(!$this->hasProfiler())
                return $this->_connectorAdapter->prepareQueryStatement($sql,$type);;
        $this->_profilerAdapter->startQueryStatement($sql,$type);
        try{
            return $this->_connectorAdapter->prepareQueryStatement($sql,$type);
        }catch(Exception $e){
            $this->_profilerAdapter->stopQueryWithException($e->getMessage());
            throw $e;
        }
    }

    /**
     * @todo to implement
     */
    public function executeQueryStatement(array $args){
        if(!$this->hasProfiler())
                return $this->_connectorAdapter->executeQueryStatement($args);
        try{
            $results=$this->_connectorAdapter->executeQueryStatement($args);
            if($results instanceof Art_Adapter_Database_Result_Abstract)
                $this->_profilerAdapter->stopQueryStatement($results->count(),$results->toArray(),$args);
            else
                $this->_profilerAdapter->stopQueryStatement($results,null,$args);
            return $results;
        }catch(Exception $e){
            $this->_profilerAdapter->stopQueryStatementWithException($e->getMessage());
            throw $e;
        }
    }

    
    public function insert($table,array $values){
        return $this->query($this->_descriptorAdapter->insert($table,$values),'insert');
    }

    public function select($from, $where=false, $joins=false, $limit=false,$orderBy=false, $start=false, $count=false,&$args=false,$idField='id'){
         return $this->query($this->_descriptorAdapter->select($from, $where, $joins, $limit,$orderBy, $start, $count,$args,$idField),'select');
    }

    public function update($table,array $values,$where=false){
        return $this->query($this->_descriptorAdapter->update($table,$values,$where),'insert');
    }
    
    public function delete($table,$where){
        return $this->query($this->_descriptorAdapter->delete($table,$where),'delete');
    }

    public function createTable($name,array $fields,array $primaryKeys,array $indexes,$ifNotExists=true) {
        return $this->query($this->_descriptorAdapter->createTable($name,$fields,$primaryKeys,$indexes,$ifNotExists),'createTable');
    }
}
?>