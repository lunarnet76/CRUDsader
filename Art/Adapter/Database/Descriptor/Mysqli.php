<?php
/**
 * LICENSE: see Art/license.txt
 *
 * @author Jean-Baptiste Verrey <jeanbaptiste.verrey@gmail.com>
 * @copyright  2011 Jean-Baptiste Verrey
 * @license    http://www.Art.com/license/2.txt
 * @version    $Id$
 * @link       http://www.Art.com/manual/
 * @since      2.0
 *
 * @package    Art\Adapter\Database
 */
namespace Art\Adapter\Database\Descriptor {
    /**
     * @todo put engine in adapter configuration
     * descriptor for MySQL
     */
    class Mysqli extends \Art\Adapter\Database\Descriptor {

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
         * quote a field value, do not quote Database\Expression object
         * @static
         * @param string $value
         * @return string
         */
        public function quote($value) {
            return ($value instanceof Database\Expression) ? $value->get() : '"' . \Art\Database::getInstance()->getConnector()->escape($value) . '"';
        }

        /**
         * return a field/table alias if not empty
         * @param string $alias
         * @return string
         */
        private function _getAlias($alias) {
            return!empty($alias) ? ' AS ' . $this->quoteIdentifier($alias) : '';
        }

        /**
         * select and join tables
         * @param \Art\Database_Sql_Select $select
         */
        public function select(\Art\Database\Select $select, array $args=null,$count=false) {
            $infos = $select->getAttributes();
            $fromTable = $infos['from']['table'];
            $fromAlias = $infos['from']['alias'];

            $fields = '';
            // FROM fields
            if (!$count && isset($infos['fields'][$fromAlias]))
                foreach ($infos['fields'][$fromAlias] as $alias => $field)
                    $fields.=$this->quoteIdentifier($fromAlias) . '.' . $this->quoteIdentifier($field) . $this->_getAlias($alias) . ',';
            else
                $fields = $this->quoteIdentifier($fromAlias) . '.*,';

            // JOINS
            $join = '';
            if (!empty($infos['join']))
                foreach ($infos['join'] as $info) {
                    $joinTable = $this->quoteIdentifier($info['toTable']);
                    $joinAlias = $this->quoteIdentifier($info['toAlias']);
                    if (!empty($infos['fields'][$info['toAlias']]) && !$count)
                        foreach ($infos['fields'][$info['toAlias']] as $alias => $field)
                            $fields.=$joinAlias . '.' . $this->quoteIdentifier($field) . $this->_getAlias($alias) . ',';
                    else
                        $fields.=$joinAlias . '.*,';
                    $join.=' ' . (isset($info['type']) ? strtoupper($info['type']) : 'LEFT') . ' JOIN ' . $this->quoteIdentifier($info['toTable']) . ' AS ' . $this->quoteIdentifier($info['toAlias']) . ' ON ' . $this->quoteIdentifier($info['fromAlias']) . '.' . $this->quoteIdentifier($info['fromColumn']) . '=' . $this->quoteIdentifier($info['toAlias']) . '.' . $this->quoteIdentifier($info['toColumn']);
                }
            if (!$count)
                $fields[strlen($fields) - 1] = ' ';
            else {
                $fields = 'COUNT(DISTINCT ' . $fromAlias . '.' . $this->quoteIdentifier($idField) . ') AS ' . parent::$FIELD_COUNTING_ALIAS . ' ';
                $infos['orderBy'] = false;
            }

            // SPECIAL FOR LIMIT WITH JOINS
            $from = $this->quoteIdentifier($fromTable) . ' AS ' . $this->quoteIdentifier($fromAlias);
            if (!empty($infos['join']) && !empty($infos['limit'])) {
                $joinTablesFrom = '(SELECT DISTINCT(' . $this->quoteIdentifier($fromAlias) . '.' . $this->quoteIdentifier(parent::$ID_FIELD) . ') AS ' . $this->quoteIdentifier(parent::$ID_FIELD) . ' FROM ' . $from . $join;
                if (!empty($infos['where']))
                    $joinTablesFrom.=' WHERE ' . $infos['where'];
                $joinTablesFrom.=' LIMIT ' . $infos['limit']['results'] . (isset($infos['limit']['offset']) ? ' OFFSET ' . $infos['limit']['offset'] : '') . ' )' . ' AS ' . $this->quoteIdentifier(parent::$TABLE_LIMIT_ALIAS) . ' LEFT JOIN ' . $fromTable . ' AS ' . $fromAlias . ' ON ' . $this->quoteIdentifier(parent::$TABLE_LIMIT_ALIAS) . '.' . $this->quoteIdentifier(parent::$ID_FIELD) . '=' . $fromAlias . '.' . $this->quoteIdentifier(parent::$ID_FIELD);
                $from = $joinTablesFrom . $join;
                $join = false;
                $infos['limit'] = false;
                $infos['where'] = false;
            }

            $sql = 'SELECT ' . $fields . 'FROM ' . $from . $join;
            // WHERE + ORDER BY + LIMIT
            if (!empty($infos['where']))
                $sql.=' WHERE ' . $infos['where'];
            if (!empty($infos['orderBy']))
                $sql.=' ORDER BY ' . $infos['orderBy'];
            if (!empty($infos['limit']))
                $sql.=' LIMIT ' . $infos['limit']['results'] . (isset($infos['limit']['offset']) ? ' OFFSET ' . $infos['limit']['offset'] : '');
            // ARGS
            if(!empty($args)){
                $i=0;
                $unLexicalThis=$this;
                $sql=preg_replace_callback('|=\s*\?|', function ($infos) use ($args,$i,$unLexicalThis){
                    $var=$args[$i++];
                    return '='.($var instanceof \Art\Expression?$var:$unLexicalThis->quote($var));
                }, $sql);
            }
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
                throw new MysqliException('INSERT query cannot be without params');
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
        public function update($table, array $values, $where=false) {
            if (!count($values))
                throw new MysqliException('UPDATE query cannot be without params');
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

        protected function _highLightCallback($matches) {
            $newLines = array('SELECT', 'FROM', 'WHERE', 'DISTINCT', 'NULL', ',', 'AND', 'LIMIT');
            $newLine = array('JOIN', 'OR', '(', ')');
            $newLineBefore = array('LEFT');
            $tabAfterAndBefore = array('AS');

            if (in_array($matches[0], $newLines))
                return "\n" . $matches[0] . "\n";
            if (in_array($matches[0], $newLine))
                return $matches[0] . "\n";
            if (in_array($matches[0], $newLineBefore))
                return "\n" . $matches[0];
            if (in_array($matches[0], $tabAfterAndBefore))
                return '    ' . $matches[0] . "    ";
            return '    ' . $matches[0];
        }

        /**
         * split a SQL string into colored parts
         * @param string $sql
         * @return string
         */
        public function highLight($sql, $cli=false) {
            if ($cli)
                return preg_replace_callback('/([\w\`\']+|\,|\(|\))/', array($this, '_highLightCallback'), $sql);
            return '<div style="border-top: 1px solid black; background-color: rgb(233, 233, 233); margin-bottom: 5px;">' . str_replace(array(
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
         * @param array $fields array('col1'=>array('null'=>$bool,'type'=>$type,'length'=>$intOrFloatOrFalse))
         * @param array $identity array('col1','col2')
         * @param string $surrogateKey array('type'=>$type,'length'=>$int,'name'=>$name)
         * @param array $foreignKeys=array('col1'=>array('table'=>$table,'field'=>$field,'onUpdate'=>$clause,'onDelete'=>$clause),'col2');
         * @param array $indexes array('index1'=>array('col1','col2'))
         * @return bool 
         */
        public function createTable($name, array $fields, array $identity=array(), array $surrogateKey=array(), array $indexes=array()) {
            $sql = 'CREATE TABLE ' . $this->quoteIdentifier($name) . '(';
            if (!empty($surrogateKey)) {
                $sql.=$this->quoteIdentifier($surrogateKey['name']) . ' ' . $surrogateKey['type'] . (isset($surrogateKey['length']) ? '(' . $surrogateKey['length'] . ')' : '') . ' NOT NULL AUTO_INCREMENT,';
            }
            foreach ($fields as $name => $field) {
                $sql.=$this->quoteIdentifier($name) . ' ' . $field['type'] . (isset($field['length']) ? '(' . $field['length'] . ')' : '') . (isset($field['null']) && $field['null'] ? ' NULL' : ' NOT NULL').',';
            }
            $sql[strlen($sql)-1]= ' ';
            if (!empty($identity)) {
                $sql.=', UNIQUE KEY ' . $this->quoteIdentifier('unicity') . '(';
                foreach ($identity as $key)
                    $sql.= $this->quoteIdentifier($key) . ',';
                $sql[strlen($sql)-1]= ')';
            }
            foreach ($indexes as $name => $keys) {
                if (!empty($keys))
                    continue;
                $sql.=',KEY ' . $this->quoteIdentifier($name) . ' (';
                foreach ($keys as $key)
                    $sql.=$this->quoteIdentifier($key) . ',';
                $sql.= ')';
            }
            if(!empty($surrogateKey)){
                $sql.=', PRIMARY KEY (' . $this->quoteIdentifier($surrogateKey['name']) . ')';
            }
            return $sql.') ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin';
        }
        
        /**
         * create a reference between 2 fields of 2 tables
         * @param type $infos=array('fromTable','toTable','fromField','toField','onUpdate','onDelete') 
         */
        public function createTableReference($infos){
            return 'ALTER TABLE '.$this->quoteIdentifier($infos['fromTable']).' ADD FOREIGN KEY ('.$this->quoteIdentifier($infos['fromField']).') REFERENCES '.$this->quoteIdentifier($infos['toTable']).'('.$this->quoteIdentifier($infos['toField']).')'.(!empty($infos['onUpdate'])?' ON UPDATE '.$infos['onUpdate']:'').(!empty($infos['onDelete'])?' ON DELETE '.$infos['onDelete']:'');
        }

        /**
         * list all the available fields type possible
         * @return string
         */
        public function getAvailableFieldType() {
            return array('INT', 'VARCHAR', 'TEXT', 'DATE', 'TINYINT', 'SMALLINT', 'MEDIUMINT', 'INT', 'BIGINT', 'DECIMAL', 'FLOAT', 'DOUBLE', 'REAL', 'BIT', 'BOOL', 'SERIAL', 'DATE', 'DATETIME', 'TIMESTAMP', 'TIME', 'YEAR', 'STRING', 'CHAR', 'VARCHAR', 'TINYTEXT', 'TEXT', 'MEDIUMTEXT', 'LONGTEXT', 'BINARY', 'VARBINARY', 'TINYBLOB', 'TINYBLOB', 'MEDIUMBLOB', 'BLOB', 'LONGBLOB', 'ENUM', 'SET', 'GEOMETRY', 'POINT', 'LINESTRING', 'POLYGON', 'MULTIPOINT', 'MULTILINESTRING', 'MULTIPOLYGON', 'GEOMETRYCOLLECTION');
        }
    }
    class MysqliException extends \Art\Exception {
        
    }
}