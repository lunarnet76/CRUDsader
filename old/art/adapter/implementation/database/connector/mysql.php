<?php

/**
 * MySQL Connector Adapter
 * build SQL with the descriptor
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
 * @category   Database,Adapter
 * @package    Art2
 */
class Art_Adapter_Implementation_Database_Connector_Mysql extends Art_Adapter_Database_Connector_Abstract {

    /**
     * @var ressource
     */
    protected $_connection = NULL;
    /**
     * @var string
     */
    protected $_preparedStatement = NULL;

    /**
     * @static
     * @return self
     */
    public static function getInstance(){
        return parent::getInstanceOf(__CLASS__);
    }
    
    /**
     * connect to the DB
     */
    public function connect() {
        $configuration = Art_Configuration::getInstance()->database;
        $this->_connection = @mysql_connect($configuration->host, $configuration->user, $configuration->password);
        if (!$this->_connection)
            throw new Art_Database_Exception(mysql_errno() . ' : ' . mysql_error());
        $select = @mysql_select_db($configuration->name, $this->_connection);
        if (!$select)
            throw new Art_Database_Exception(mysql_errno() . ' : ' . mysql_error());
    }

     /**
     * execute a pure SQL string
     * @param string $sql
     * @param string $type optional, the type of query like SELECT or UPDATE
     * @return Art_Database_Result
     */
    public function query($sql, $type='') {
        if(!isset($this->_connection))
            $this->connect ();
        $query = mysql_query($sql);
        $count=false;
         if (!$query)
            throw new Art_Database_Exception(mysql_errno() . ' : ' . mysql_error().' in sql : '.$sql,mysql_errno());
        switch ($type) {
            case 'select':
            case 'listTable':
                $count = mysql_num_rows($query);
                $classResult=Art_Adapter_Factory::getClass(array('database'=>'result'));
                return new $classResult($query,$sql,$count);
                break;
            case 'delete':
            case 'update':
                return (int)mysql_affected_rows();
                break;
            default:
                return true;
        }
        
    }

    /**
     * virtualize a SQL prepared statement (it does not exist without php_mysqli)
     * @abstract
     * @param string $sql
     * @param string $type optional, the type of query like SELECT or UPDATE
     */
    public function prepareQueryStatement($sql, $type='') {
        $this->_preparedStatement=array('sql'=>$sql,'type'=>$type);
    }

    /**
     * execute a SQL prepared statement
     * @param array $args the values to replaced the ? in the prepared statement
     * @return Art_Database_Result
     */
    public function executeQueryStatement(array $args){
        if(!isset($this->_connection))
            $this->connect ();
        if(!isset($this->_preparedStatement))
            throw new Art_Database_Exception('you must define a prepared statement before executing it');
        $descriptor = Art_Database::getInstance()->getDescriptor();
        $sql=$this->_preparedStatement['sql'];
        $tmp = '';
        $len = strlen($sql);
        $j = 0;
        for ($i = 0; $i < $len; $i++)
            if ($sql[$i] == '?')
                $tmp.=$descriptor->quote($args[$j++]);
            else
                $tmp.=$sql[$i];
        return $this->query($tmp, $this->_preparedStatement['type']);
    }

    /**
     * begin a transaction
     * @abstract
     */
    public function beginTransaction(){
        if(!isset($this->_connection))
            $this->connect();
        $this->query('START TRANSACTION','transaction');
        $this->query('BEGIN','transaction');
    }

    /**
     * end a transaction with a commit
     * @abstract
     */
    public function commit(){
        $this->query('COMMIT','transaction');
    }
    
    /**
     * end a transaction with a rollbak
     * @abstract
     */
    public function rollBack(){
        $this->query('ROLLBACK','transaction');
    }
}
?>