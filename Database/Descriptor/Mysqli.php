<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Database\Descriptor {
	/**
	 * @todo put engine in adapter configuration
	 * descriptor for MySQL
	 * @package CRUDsader\Database\Descriptor
	 */
	class Mysqli extends \CRUDsader\Database\Descriptor {

		/**
		 * quote table name or table field
		 * @static
		 * @param string $identifier
		 * @return string
		 */
		public function quoteIdentifier($identifier)
		{
			return '`' . $identifier . '`';
		}

		/**
		 * quote a field value, do not quote Database\Expression object
		 * @static
		 * @param string $value
		 * @return string
		 */
		public function quote($value)
		{
			return $value instanceof \CRUDsader\Expression ? ($value->isToBeQuoted() ? '"' . $value->__toString() . '"' : $value->__toString()) : '"' . $this->_dependencies['connector']->escape($value) . '"';
		}

		/**
		 * return a field/table alias if not empty
		 * @param string $alias
		 * @return string
		 */
		private function _getAlias($alias)
		{
			return !empty($alias) ? ' AS ' . $this->quoteIdentifier($alias) : '';
		}

		/**
		 * insert values in a table
		 * @param string $table
		 * @param array $values
		 * @return string
		 */
		public function insert($table, array $values, \CRUDsader\Database\Connector $connector = null)
		{
			if (!count($values))
				throw new MysqliException('INSERT query cannot be without params');
			$sql = 'INSERT INTO ' . $this->quoteIdentifier($table, $connector);
			$fields = '(';
			$fieldValues = '(';
			foreach ($values as $key => $value) {
				$fields.=$this->quoteIdentifier($key) . ',';

				$fieldValues.=($value === null ? 'NULL' : $this->quote($value === false ? 0 : $value, $connector)) . ',';
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
		public function update($table, array $values, $where = false)
		{
			if (!count($values))
				throw new MysqliException('UPDATE query cannot be without params');
			$sql = 'UPDATE ' . $this->quoteIdentifier($table) . ' SET ';
			foreach ($values as $key => $value) {
				$sql.=$this->quoteIdentifier($key) . '=' . ($value === null ? 'NULL' : $this->quote($value === false ? 0 : $value)) . ',';
			}
			$sql[strlen($sql) - 1] = ' ';
			if ($where)
				$sql.='WHERE ' . $where;
			return $sql;
		}

		/**
		 * delete from a table
		 * @param string $tabl
		 * @param string $where
		 * @return string
		 */
		public function delete($table, $where)
		{
			return 'DELETE FROM ' . $this->quoteIdentifier($table) . ' WHERE ' . $where;
		}

		/**
		 * list all the tables
		 * @return string
		 */
		public function listTables()
		{
			return 'SHOW TABLES';
		}

		/**
		 * delete a table
		 * @param string $tableName
		 * @return string
		 */
		public function deleteTable($tableName)
		{
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
		public function createTable($name, array $fields, array $identity = array(), array $surrogateKey = array(), array $indexes = array())
		{
			$sql = 'CREATE TABLE ' . $this->quoteIdentifier($name) . '(';
			$fieldsAdded = array();
			if (!empty($surrogateKey)) {
				if (isset($fieldsAdded[$surrogateKey['name']]))
					continue;
				$fieldsAdded[$surrogateKey['name']] = true;
				$sql.=$this->quoteIdentifier($surrogateKey['name']) . ' ' . $surrogateKey['type'] . (isset($surrogateKey['length']) ? '(' . $surrogateKey['length'] . ')' : '') . ' NOT NULL AUTO_INCREMENT,';
			}

			foreach ($fields as $name => $field) {
				if (isset($fieldsAdded[$name]))
					continue;
				$fieldsAdded[$name] = true;
				$sql.=$this->quoteIdentifier($name) . ' ' . $field['type'] . ($field['length'] ? '(' . $field['length'] . ')' : '') . (isset($field['null']) && $field['null'] ? ' NULL' : ' NOT NULL') . ',';
			}
			$sql[strlen($sql) - 1] = ' ';
			if (!empty($identity)) {
				$sql.=',UNIQUE KEY ' . $this->quoteIdentifier('unicity') . '(';
				foreach ($identity as $key)
					$sql.= $this->quoteIdentifier($key) . ',';
				$sql[strlen($sql) - 1] = ')';
			}
			foreach ($indexes as $name => $keys) {
				if (!empty($keys))
					continue;
				$sql.=',KEY ' . $this->quoteIdentifier($name) . ' (';
				foreach ($keys as $key)
					$sql.=$this->quoteIdentifier($key) . ',';
				$sql.= ')';
			}
			if (!empty($surrogateKey)) {
				$sql.=',PRIMARY KEY (' . $this->quoteIdentifier($surrogateKey['name']) . ')';
			}
			return $sql . ') ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin';
		}

		/**
		 * create a reference between 2 fields of 2 tables
		 * @param type $infos=array('fromTable','toTable','fromField','toField','onUpdate','onDelete') 
		 */
		public function createTableReference($infos)
		{
			return 'ALTER TABLE ' . $this->quoteIdentifier($infos['fromTable']) . ' ADD FOREIGN KEY (' . $this->quoteIdentifier($infos['fromField']) . ') REFERENCES ' . $this->quoteIdentifier($infos['toTable']) . '(' . $this->quoteIdentifier($infos['toField']) . ')' . (!empty($infos['onUpdate']) ? ' ON UPDATE ' . $infos['onUpdate'] : '') . (!empty($infos['onDelete']) ? ' ON DELETE ' . $infos['onDelete'] : '');
		}

		/**
		 *  count object in a SELECT statment
		 * @param array $select
		 */
		public function countSelect($select, $args = null)
		{
			$sql = 'SELECT count(`' . $select['from']['alias'] . '`.`' . $select['from']['id'] . '`)  AS `' . self::$OBJECT_ID_FIELD_ALIAS . '` FROM `' . $select['from']['table'] . '` AS `' . $select['from']['alias'] . '`';
			if (!empty($select['joins'])) {
				foreach ($select['joins'] as $join) {
					$sql.=' LEFT JOIN `' . $join['table'] . '` AS `' . $join['alias'] . '` ON `' . $join['alias'] . '`.`' . $join['field'] . '`=`' . $join['joinAlias'] . '`.`' . $join['joinField'] . '`';
				}
			}


			if (!empty($select['where']))
				$sql.=' WHERE ' . preg_replace_callback('|`?([@\w]+)`?\.`?([\w]+)`?|', function($p) {

							// special case : email
							if (strpos($p[1], '@') !== false)
								return $p[0];
							// special case : floats
							if (ctype_digit($p[1])) {
								return $p[0];
							}
							return '`' . $p[1] . '`.`' . $p[2] . '`';
						}, $select['where']);

			// replace args
			if ($args != null) {

				$this->i = 0;

				$unLexicalThis = $this;

				$sql = preg_replace_callback('|(\=\?)|', function($p) use($args, $unLexicalThis) {
						$arg = $args[$unLexicalThis->i];
						$unLexicalThis->i++;
						return (is_array($arg) ? key($arg) . ' ' . $unLexicalThis->quote(current($arg)) : '=' . $unLexicalThis->quote($arg));
					}, $sql);
			}

			return $sql;
		}

		/**
		 * SELECT statment
		 * @param array $select
		 */
		public function select($select, $args = null, $classAliases = null)
		{
			$sql = 'SELECT ';

			// object
			$unLexicalThis = $this;
			$fields = array();
			if (!empty($select['fields'])) {
				foreach ($select['fields'] as $field) {
					$fields[] = '`' . $field['tableAlias'] . self::$TABLE_ALIAS_SUBQUERY . '`.' . ($field['field'] == '*' ? '*' : '`' . $field['field'] . '`') . '' . (isset($field['alias']) ? ' AS `' . $field['alias'] . '`' : '');
				}
				$sql.=implode(',', $fields);
			} else if (!empty($select['select'])) {
				$sql.=self::$OBJECT_TMP_TABLE_ALIAS . '.' . self::$OBJECT_ID_FIELD_ALIAS . ',' . $select['select'];
			}else
				$sql.='*';
			$sql.=' FROM (SELECT `' . $select['from']['alias'] . '`.`' . $select['from']['id'] . '` AS `' . self::$OBJECT_ID_FIELD_ALIAS . '` FROM `' . $select['from']['table'] . '` AS `' . $select['from']['alias'] . '`';
			$joins = '';
			if (!empty($select['joins'])) {
				foreach ($select['joins'] as $join) {
					$sql.=' LEFT JOIN `' . $join['table'] . '` AS `' . $join['alias'] . '` ON `' . $join['alias'] . '`.`' . $join['field'] . '`=`' . $join['joinAlias'] . '`.`' . $join['joinField'] . '`';
					$joins.=' LEFT JOIN `' . $join['table'] . '` AS `' . $join['alias'] . self::$TABLE_ALIAS_SUBQUERY . '` ON `' . $join['alias'] . self::$TABLE_ALIAS_SUBQUERY . '`.`' . $join['field'] . '`=`' . $join['joinAlias'] . self::$TABLE_ALIAS_SUBQUERY . '`.`' . $join['joinField'] . '`';
				}
			}
			if (!empty($select['where'])) {
				$sql.=' WHERE ' . preg_replace_callback('|`?([@\w]+)`?\.`?([\w]+)`?|', function($p) {

							// special case : email
							if (strpos($p[1], '@') !== false)
								return $p[0];
							// special case : floats
							if (ctype_digit($p[1])) {
								return $p[0];
							}
							return '`' . $p[1] . '`.`' . $p[2] . '`';
						}, $select['where']);
			}
			$sql.=' GROUP BY `' . $select['from']['alias'] . '`.`' . $select['from']['id'] . '`';

			if (!empty($select['order'])) {
				$map = \CRUDsader\Instancer::getInstance()->map;
				$aliasSubquery = self::$TABLE_ALIAS_SUBQUERY;
				
				$order = preg_replace_callback('_([\w]+)\.([\w]+)\s*(DESC|ASC)?_', function($p) use($map, $classAliases, $aliasSubquery) {
						return '`' . $p[1]  . '`.`'  . $map->classGetAttributeDatabaseName($classAliases[$p[1]], $p[2]) . '`'.' '.(isset($p[3])?$p[3]:'');
					}, $select['order']);
					
				if ($args != null) {
					$this->i = 0;
					$unLexicalThis = $this;

					$order = preg_replace_callback('|(\?)|', function($p) use($args, $unLexicalThis) {

							$arg = $args[$unLexicalThis->i];
							$unLexicalThis->i++;
							if ($unLexicalThis->i % 2 == 0)
								$unLexicalThis->t++;
							return $unLexicalThis->quote($arg);
						}, $order);
				}
				$sql.=' ORDER BY ' . $order;
			}

			if (!empty($select['limit']))
				$sql.=' LIMIT ' . (isset($select['limit']['from']) ? $select['limit']['from'] . ',' : '') . $select['limit']['count'];
			$sql.=') AS `' . self::$OBJECT_TMP_TABLE_ALIAS . '` JOIN `' . $select['from']['table'] . '` AS `' . $select['from']['alias'] . self::$TABLE_ALIAS_SUBQUERY . '` ON `' . self::$OBJECT_TMP_TABLE_ALIAS . '`.`' . self::$OBJECT_ID_FIELD_ALIAS . '`=' . $select['from']['alias'] . self::$TABLE_ALIAS_SUBQUERY . '.`' . $select['from']['id'] . '`';
			$sql.=$joins;


			$restrictiveWhere = !empty($select['where']) && $this->_configuration->restrictiveWhere;
			if ($restrictiveWhere) {
				$sql.=' WHERE ' . preg_replace_callback('|`?([@\w]+)`?\.`?([\w]+)`?|', function($p) {

							// special case : email
							if (strpos($p[1], '@') !== false)
								return $p[0];
							// special case : floats
							if (ctype_digit($p[1])) {
								return $p[0];
							}
							return '`' . $p[1] . Mysqli::$TABLE_ALIAS_SUBQUERY . '`.`' . $p[2] . '`';
						}, $select['where']);
			}
			// replace args
			if ($args != null) {

				$this->i = 0;

				$unLexicalThis = $this;

				if ($restrictiveWhere)
					$args = array_merge($args, $args);

				$sql = preg_replace_callback('|(\=\?)|', function($p) use($args, $restrictiveWhere, $unLexicalThis) {
					if(!isset($args[$unLexicalThis->i])){
						pre($args);
						throw new \Exception('missing arg $'.$unLexicalThis->i);
					}
						$arg = $args[$unLexicalThis->i];
						$unLexicalThis->i++;
						return (is_array($arg) ? key($arg) . ' ' . $unLexicalThis->quote(current($arg)) : '=' . $unLexicalThis->quote($arg));
					}, $sql);
			}



			if (!empty($select['order'])) {
				$map = \CRUDsader\Instancer::getInstance()->map;

				$aliasSubquery = self::$TABLE_ALIAS_SUBQUERY;
				$order = preg_replace_callback('_([\w]+)\.([\w]+)\s*(DESC|ASC)?_', function($p) use($map, $classAliases, $aliasSubquery) {
					
						return '`' . $p[1]  .$aliasSubquery. '`.`'  . $map->classGetAttributeDatabaseName($classAliases[$p[1]], $p[2]) . '`'.' '.(isset($p[3])?$p[3]:'');
					}, $select['order']);
				if ($args != null) {
					$this->i = 0;
					$unLexicalThis = $this;

					$order = preg_replace_callback('|(\?)|', function($p) use($args, $unLexicalThis) {

							$arg = $args[$unLexicalThis->i];
							$unLexicalThis->i++;
							if ($unLexicalThis->i % 2 == 0)
								$unLexicalThis->t++;
							return $unLexicalThis->quote($arg);
						}, $order);
				}
				$sql.=' ORDER BY ' . $order;
			}


			if (!empty($select['group'])) {// array(1=>array('u'),2=>array('id'));
				$sql.=' GROUP BY ';
				$groups = array();
				foreach ($select['group'][1] as $k => $v)
					$groups[] = $v . self::$TABLE_ALIAS_SUBQUERY . '.' . $select['group'][2][$k];
				$sql.=implode(',', $groups);
			}

			return $sql;
		}

		/**
		 * split a SQL string into colored Parts
		 * @param string $sql
		 * @return string
		 */
		public function highLight($sql, $cli = false)
		{
			if ($cli)
				return preg_replace_callback('/([\w\`\']+|\,|\(|\))/', function($matches) {
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
						}, $sql);
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
				    'NULL',
				    'LIMIT'
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
				    '<span style="font-weight:bold"> NULL </span>',
				    '<br><span style="color:#ae1414;font-weight:bold">LIMIT</span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
					), $sql) . '</div>';
		}

		/**
		 * list all the available fields type possible
		 * @return string
		 */
		public function getAvailableFieldType()
		{
			return array('INT', 'VARCHAR', 'TEXT', 'DATE', 'TINYINT', 'SMALLINT', 'MEDIUMINT', 'INT', 'BIGINT', 'DECIMAL', 'FLOAT', 'DOUBLE', 'REAL', 'BIT', 'BOOL', 'SERIAL', 'DATE', 'DATETIME', 'TIMESTAMP', 'TIME', 'YEAR', 'STRING', 'CHAR', 'VARCHAR', 'TINYTEXT', 'TEXT', 'MEDIUMTEXT', 'LONGTEXT', 'BINARY', 'VARBINARY', 'TINYBLOB', 'TINYBLOB', 'MEDIUMBLOB', 'BLOB', 'LONGBLOB', 'ENUM', 'SET', 'GEOMETRY', 'POINT', 'LINESTRING', 'POLYGON', 'MULTIPOINT', 'MULTILINESTRING', 'MULTIPOLYGON', 'GEOMETRYCOLLECTION');
		}
	}
	class MysqliException extends \CRUDsader\Exception {
		
	}
}