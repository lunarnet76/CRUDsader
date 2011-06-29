<?php
/**
 * Utility to simplify $database->select();
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
 * @category   Database
 * @package    Art2
 */
class Art_Database_Select {
    protected $_from;
    protected $_where=false;
    protected $_args=false;
    protected $_joins=false;
    protected $_limit=false;
    protected $_start=false;
    protected $_orderBy=false;
    protected $_count=false;
    protected $_idField='id';

    public function __construct($from,$alias=false,$fields=false){
        $this->_from=array($alias=>$from,'fields'=>$fields);
    }

    public function specifySelectFromTableFields($fields=false){
         $this->_from['fields']=$fields;
    }

    public function where($where,$args=false){$this->_where=$where;$this->_args=$args;return $this;}

   
    public function join($infos=array('alias'=>'table','fields'=>array('fieldAlias'=>'fieldName'),'join'=>array('table1','id','table2','id'),'type'=>'LEFT')){
        $this->_joins[]=$infos;return $this;
    }
    public function limit($limit,$start=false){$this->_limit=$limit;$this->_start=$start;return $this;}
    public function count($count){$this->_count=$count;return $this;}
    public function idField($idField){$this->_idField=$id;return $this;}
    public function orderBy($orderBy){$this->_orderBy=$orderBy;return $this;}

    public function __toString(){
        return Art_Database::getInstance()->getDescriptor()->select($this->_from,$this->_where,$this->_joins,$this->_limit,$this->_orderBy,$this->_start,$this->_count,$this->_args,$this->_idField);
    }
}
?>
