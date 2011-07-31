<?php
/**
 * LICENSE: see Art/license.txt
 *
 * @author     Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     http://www.Art.com/license/2.txt
 * @version     $Id$
 * @link        http://www.Art.com/manual/
 * @since       1.0
 */
namespace Art {
    /**
     * @package Art
     */
    class Query {
        protected static $_associationClassAlias = 'assoc';
        protected $_oql;
        protected $_sql;

        const REGEXP_SELECT='(?:(?:\s*(SELECT)\s+((?:(?:\*)|(?:\w+\.(?:\w+|\*)\,?)\s*)*))?)';
        const REGEXP_FROM='\s*(FROM)\s+(\w+)\s+(\w+)((?:\s+JOIN\s+\w+(?:\(\w+\))?\s+\w+\s+ON\s+\w+(?:\s+ASSOCIATION\s+\w+)?)*)?';
        const REGEXP_WHERE='(?:\s+(WHERE)\s+((?:\(*?(?:\?|(?:\w+\.\w+=\?)\)*?(?:\s+(?:AND|OR)\s+)?))*)\s*)?';
        const REGEXP_ORDERBY='(?:\s+(ORDER BY)\s+((?:\w+\.\w+\,?)*))?';
        const REGEXP_LIMIT='(?:\s+(LIMIT)\s+([0-9]*)(?:,([0-9]*))?)?';
        const REGEXP_FROM_JOINS='JOIN\s+(\w+)(?:\((\w+)\))?\s+(\w+)\s+ON\s+(\w+)(?:\s+ASSOCIATION\s+(\w+))?';
        public function __construct($oql) {
            $this->_oql = $oql;
            $this->_map = Map::getInstance();
            $matches = $this->_validateSyntax();
            if (empty($matches[4])) {
                $className = $alias = $matches[5];
            } else {
                $className = $matches[4];
                $alias = $matches[5];
            }
            if (empty($className))
                throw new QueryException('error in FROM : no class specified');
            if (!$alias && (!empty($matches[1]) || !empty($matches[7]) || !empty($matches[9])))
                throw new QueryException('error in FROM : no alias specified');
            $tableAliases = array($alias => $className);
            // from
            if (!$this->_map->classExists($className))
                throw new QueryException('error in FROM : class "' . $className . '" does not exist');
            $sql = new Database\Select();
            $sql->from(array('table' => $this->_map->classGetDatabaseTable($className), 'alias' => $alias));

            // joins
            if (!empty($matches[6])) {
                preg_match_all('/' . self::REGEXP_FROM_JOINS . '/', $matches[6], $joinInfos);
                foreach ($joinInfos[1] as $index => $joinedClass) {
                    $table = $joinInfos[1][$index];
                    $associationName = !empty($joinInfos[2][$index]) ? $joinInfos[2][$index] : $joinedClass;
                    $joinedAlias = $joinInfos[3][$index];
                    $fromAlias = $joinInfos[4][$index];
                    $associationClassAlias = empty($joinInfos[5][$index]) ? self::$_associationClassAlias++ : $joinInfos[5][$index];
                    $join = $this->_map->classGetJoin($className, $associationName, $fromAlias, $joinedAlias, $associationClassAlias);
                    if (isset($join['association']))
                        $sql->join($join['association']);
                    $sql->join($join['table']);
                }
            }
            $this->_sql = $sql;
        }
        public function execute($args=NULL) {
            if (!is_array($args))
                $args = array($args);
            pre($this->_sql);
            return \Art\Database::getInstance()->select($this->_sql, $args);
        }
        protected function _validateSyntax() {
            if (!preg_match('/^' . self::REGEXP_SELECT . self::REGEXP_FROM . self::REGEXP_WHERE . self::REGEXP_ORDERBY . self::REGEXP_LIMIT . '$/', $this->_oql, $matches))
                throw new QueryException('bad syntax');
            return $matches;
        }
        
        // better error than just "bad syntax"
        /**
         * @todo to be done ... deport functionality, to complex to be only here
         */
        public function explainError() {
            $this->_oql;
        }
    }
    class QueryException extends \Art\Exception {
        
    }
}