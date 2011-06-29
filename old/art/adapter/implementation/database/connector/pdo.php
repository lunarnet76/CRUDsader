<?php
/**
 * PDO DB connector adapter
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
class Art_Adapter_Implementation_Database_Connector_Pdo extends Art_Adapter_Database_Connector_Abstract{
    protected $_connection=false;
    /**
     * @static
     * @return self
     */
    public static function getInstance(){
        return parent::getInstanceOf(__CLASS__);
    }
    
    /**
     * connect to the DB
     * @abstract
     */
    public function connect(){
        if(!$this->_connection){
            $configuration=Art_Configuration::getInstance()->database;
            $this->_connection=new PDO($configuration->vendor.':host='.$configuration->host.';dbname='.$configuration->name,$configuration->user,$configuration->password,isset($configuration->options)?$configuration->options:null );
        }
        return $this->_connection;
    }
    
    /**
     * execute a pure SQL string
     * @abstract
     * @param string $sql
     * @param string $type optional, the type of query like SELECT or UPDATE
     */
    public function query($sql,$type=''){
        if(!$this->_connection)
                $this->_connect();
        switch($type){
            case 'insert':
            case 'update':
            case 'delete':
                $query=$this->_connection->exec($sql);
                break;
             case 'select':
             default:
                $query=$this->_connection->query($sql);
                break;
        }
        if($query===false){
            $errors=$this->_connection->errorInfo();
            throw new Art_Adapter_Database_Connector_Exception($this->_connection->errorCode().':'.$errors[0].' '.$errors[1].' '.$errors[2]);
        }
        return $query;
    }
    /**
     * execute a SQL prepared statement
     * @abstract
     * @param string $sql
     * @param array $args 
     * @param string $type optional, the type of query like SELECT or UPDATE
     */
    public function queryStatement($sql,array $args,$type=''){
        if(!$this->_connection)
                $this->_connect();
        switch($type){
            case 'insert':
            case 'update':
            case 'delete':
                $query=$this->_connection->exec($sql);
                break;
             case 'select':
             default:
                $query=$this->_connection->query($sql);
                break;
        }
        if($query===false){
            $errors=$this->_connection->errorInfo();
            throw new Art_Adapter_Database_Connector_Exception($this->_connection->errorCode().':'.$errors[0].' '.$errors[1].' '.$errors[2]);
        }
        return $query;
    }
    
}
?>