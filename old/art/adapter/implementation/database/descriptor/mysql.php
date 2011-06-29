<?php

/**
 * descriptor for MySQL
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
 * @category   ------------------
 * @package    Art2
 */
class Art_Adapter_Implementation_Database_Descriptor_Mysql extends Art_Adapter_Database_Descriptor_Abstract {
    const TABLE_LIMIT_ALIAS='limittable';
    /**
     * @static
     * @return self
     */
    public static function getInstance() {
        return parent::getInstanceOf(__CLASS__);
    }

    /**
     * quote table name or table field
     * @static
     * @param string $identifier
     * @return string
     */
    public function quoteIdentifier($identifier) {
        return '`' . $identifier . '`';
    }

    /**
     * quote a field value, do not quote Art_Database_Expression object
     * @static
     * @param string $value
     * @return string
     */
    public function quote($value) {
        Art_Database::getInstance()->getConnector()->connect();
        return ($value instanceof Art_Database_Expression) ? $value->get() : '"' . mysql_real_escape_string($value) . '"';
    }

    /**
     * return a field/table alias if not empty
     * @param string $alias
     * @return string
     */
    private function _getAlias($alias){
        return !empty($alias)?' AS '.$this->quoteIdentifier($alias):'';
    }

    /**
     * select and join tables
     * @param array $from
     * @param string $where
     * @param array $joins
     * @param string $orderBy
     * @param int $limit
     * @param int $start
     * @return string
     * $from = Array($alias=>$table,'fields'=>array($fieldAlias=>$fieldName))
     * $joins=array(array($table=>$alias,'fields'=>array($fieldAlias=>$fieldName),'join'=>array($table1,$tablefield1,$table2,$tableField2),'type'=>$type)));
     * LIMIT and START are NOT the number of rows BUT the number of rows with distinct $from.id
     */
    public function select($from, $where=false, $joins=false, $limit=false,$orderBy=false, $start=false, $count=false,&$args=false,$idField='id') {
        $table=each($from);
        $fromTable = $this->quoteIdentifier($table['value']);
        $fromAlias = is_int($table['key'])? $fromTable : $this->quoteIdentifier($table['key']);
        $fields='';

        // FROM
        if(!$count && current($from)!==false){
            $fieldsArray=current($from);
            foreach($fieldsArray as $alias=>$field)
                $fields.=$fromAlias.'.'.$this->quoteIdentifier($field).$this->_getAlias($alias).',';
        }else
            $fields=$fromAlias.'.*,';

        // JOINS
        $join='';
        if($joins){
            if(!is_int(key($joins)))
                $joins=array($joins);
            foreach($joins as $infos){
                $table=each($infos);
                $joinTable= $this->quoteIdentifier($table['value']);
                $joinAlias= is_int($table['key'])? $fromTable : $this->quoteIdentifier($table['key']);
                if(isset($infos['fields']) && !$count)
                    foreach($infos['fields'] as $alias=>$field)
                        $fields.=$joinAlias.'.'.$this->quoteIdentifier($field).$this->_getAlias($alias).',';
                else
                    $fields.=$joinAlias.'.*,';
                if(isset($infos['join'])){
                    $join.=' '.(isset($infos['type'])?strtoupper($infos['type']):'LEFT').' JOIN '.$joinTable.' AS '.$joinAlias.' ON '.$this->quoteIdentifier($infos['join'][0]).'.'.$this->quoteIdentifier($infos['join'][1]).'='.$this->quoteIdentifier($infos['join'][2]).'.'.$this->quoteIdentifier($infos['join'][3]);
                }else
                    $join.=','.$joinTable.' AS '.$joinAlias;
            }
        }
        if(!$count)
            $fields[strlen($fields)-1]=' ';
        else{
            $fields='COUNT(DISTINCT '.$fromAlias.'.'.$this->quoteIdentifier($idField).') AS '.Art_Adapter_Database_Descriptor_Abstract::FIELD_COUNTING_ALIAS.' ';
            $orderBy=false;
            }

        // SPECIAL FOR LIMIT WITH JOINS
        $from=$fromTable.' AS '.$fromAlias;
        if ($joins && $limit) {
            $joinTablesFrom = '(SELECT DISTINCT(' . $fromAlias . '.'.$this->quoteIdentifier($idField).') AS '.$this->quoteIdentifier($idField).' FROM '.$from.$join;
            if($where)
                $joinTablesFrom.='WHERE '.$where;
            $joinTablesFrom.=' LIMIT ' . $limit . ($start ? ' OFFSET ' . $start : '') . ' )' . ' AS '.$this->quoteIdentifier(self::TABLE_LIMIT_ALIAS).' left JOIN ' . $fromTable . ' AS ' . $fromAlias . ' ON '.$this->quoteIdentifier(self::TABLE_LIMIT_ALIAS).'.'.$this->quoteIdentifier($idField).'=' . $fromAlias . '.'.$this->quoteIdentifier($idField);
            $from=$joinTablesFrom.$join;
            $join=false;
            $limit=false;
            $where=false;
            if ($args)
                $args = array_merge($args, $args);
        }

        $sql='SELECT '.$fields.'FROM '.$from.$join;
        // WHERE + ORDER BY + LIMIT
         if($where)
            $sql.=' WHERE '.$where;
        if($orderBy)
            $sql.=' ORDER BY '.$orderBy;
        if($limit)
            $sql.=' LIMIT ' . $limit . ($start ? ' OFFSET ' . $start : '');
        return $sql;
    }

    /**
     * insert values in a table
     * @param string $table
     * @param array $values
     * @return string
     */
    public function insert($table, array $values) {
        if (!count($values))
            throw new Art_Adapter_Database_Descriptor_Abstract_Exception('INSERT query cannot be without params');
        $sql = 'INSERT INTO ' . $this->quoteIdentifier($table);
        $fields = '(';
        $fieldValues = '(';
        foreach ($values as $key => $value) {
            $fields.=$this->quoteIdentifier($key) . ',';
            $fieldValues.=$this->quote($value === false ? 0 : $value) . ',';
        }
        $fields[strlen($fields) - 1] = ')';
        $fieldValues[strlen($fieldValues) - 1] = ')';
        $sql.=$fields . ' VALUES ' . $fieldValues;
        return $sql;
    }

    /**
     * update values in a table
     * @param string $table
     * @param array $values
     * @param string $where
     * @return string
     */
    public function update($table,array $values,$where=false){
        if (!count($values))
            throw new Art_Adapter_Database_Descriptor_Abstract_Exception('UPDATE query cannot be without params');
        $sql = 'UPDATE ' . $this->quoteIdentifier($table) . ' SET ';
        foreach ($values as $key => $value) {
            $sql.=$this->quoteIdentifier($key) . '=' . $this->quote($value === false ? 0 : $value) . ',';
        }
        $sql[strlen($sql) - 1] = ' ';
        if ($where)
            $sql.=' WHERE ' . $where;
        return $sql;
    }

     /**
     * delete from a table
     * @param string $tabl
     * @param string $where
     * @return string
     */
    public function delete($table, $where) {
        return 'DELETE FROM ' . $this->quoteIdentifier($table) . ' WHERE ' . $where;
    }

     /**
     * split a SQL string into colored parts
     * @param string $sql
     * @return string
     */
    public function highLight($sql){
        return '<div style="border-top: 1px solid black; background-color: rgb(233, 233, 233); margin-bottom: 5px;">' .str_replace(array(
            'CREATE TABLE',
            'SELECT',
            'FROM',
            'IF NOT EXISTS',
            'WHERE',
            ',',
            'LEFT',
            'JOIN',
            'AND',
            ' OR ',
            'ON',
            'ORDER BY',
            ' AS ',
            'NULL'
            ), array(
               
                '<span style="color:#ae1414;font-weight:bold">CREATE TABLE</span><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                 '<span style="color:#ae1414;font-weight:bold">SELECT</span><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                '<br/><span style="color:#ae1414;font-weight:bold">FROM</span><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                '<span style="color:#ae1414;font-weight:bold">IF NOT EXISTS</span><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                '<br/><span style="color:#ae1414;font-weight:bold">WHERE</span><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                ',<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#ae1414;font-weight:bold">LEFT</span> ',
                '<span style="color:#ae1414;font-weight:bold">JOIN</span>',
                '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#ae1414;font-weight:bold">AND</span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#ae1414;font-weight:bold"> OR </span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                '<span style="color:#ae1414;font-weight:bold">ON</span>',
                '<br><span style="color:#ae1414;font-weight:bold">ORDER BY</span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                '<span style="font-weight:bold"> AS </span>',
                '<span style="font-weight:bold"> NULL </span>'
          ), $sql) . '</div>';
    }

    /**
     * list all the tables
     * @return string
     */
    public function listTable() {
        return 'SHOW TABLES';
    }

    /**
     * delete a table
     * @param string $tableName
     * @return string
     */
    public function deleteTable($tableName) {
        return 'DROP TABLE ' . $this->quoteIdentifier($tableName);
    }

    /**
     * create a table
     * @param string $name
     * @param array $fields an array of ('name'=>array('null'=>$bool,'type'=>$type,'autoincrement'=>$bool))
     * @param array $primaryKeys
     * @param array $indexes
     * @param bool $ifNotExists create only if not exists
     * @return string
     */
    public function createTable($name, array $fields, array $primaryKeys, array $indexes, $ifNotExists=false) {
        $sql = 'CREATE TABLE ' . ($ifNotExists ? 'IF NOT EXISTS' : '') . ' ' . $this->quoteIdentifier($name) . '(';
        foreach ($fields as $name => $field) {
            $sql.=$this->quoteIdentifier($name) . ' ' . $field['type'] . ' ' . (isset($field['null']) ? ($field['null'] ? 'NULL' : 'NOT NULL') : 'NOT NULL') . ' ' . (isset($field['autoincrement']) ? 'AUTO_INCREMENT' : '') . ',';
        }
        if (count($primaryKeys)) {
            $sql.=' PRIMARY KEY ( ';
            foreach ($primaryKeys as $key)
                $sql.= $this->quoteIdentifier($key) . ',';
            $sql[strlen($sql) - 1] = ')';
        }else
            $sql[strlen($sql) - 1] = ' ';
        foreach ($indexes as $name=>$keys){
            if(!count($keys))continue;
            $sql.=', '.($name=='uniqueness' || $name=='composition'?'UNIQUE':'').' KEY '.$this->quoteIdentifier($name).' (';
            foreach($keys as $key)
                $sql.=$this->quoteIdentifier($key).',';
            $sql[strlen($sql)-1]=')';
        }
        return $sql . ')ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin';
    }

    /**
     * list all the available fields type possible
     * @return string
     */
    public function getAvailableFieldType() {
        return array('INT', 'VARCHAR', 'TEXT', 'DATE', 'TINYINT', 'SMALLINT', 'MEDIUMINT', 'INT', 'BIGINT', 'DECIMAL', 'FLOAT', 'DOUBLE', 'REAL', 'BIT', 'BOOL', 'SERIAL', 'DATE', 'DATETIME', 'TIMESTAMP', 'TIME', 'YEAR', 'STRING', 'CHAR', 'VARCHAR', 'TINYTEXT', 'TEXT', 'MEDIUMTEXT', 'LONGTEXT', 'BINARY', 'VARBINARY', 'TINYBLOB', 'TINYBLOB', 'MEDIUMBLOB', 'BLOB', 'LONGBLOB', 'ENUM', 'SET', 'GEOMETRY', 'POINT', 'LINESTRING', 'POLYGON', 'MULTIPOINT', 'MULTILINESTRING', 'MULTIPOLYGON', 'GEOMETRYCOLLECTION');
    }

}
?>
