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
     * @todo : attributecounts for ref that are not internal
     */
    class Query {
        protected $_oql;
        protected $_fetched = false;
        protected $_sql;
        protected $_class;
        protected $_matches;
        protected $_mapFields;
        protected $_alias2class;
        protected $_syntaxValidated = false;
        protected $_tmpAliases = 'z9a';
        protected $_infos = false;

        const REGEXP_SELECT='(?:(?:\s*(SELECT)\s+((?:(?:\*)|(?:\w+\.(?:\w+|\*)\,?)\s*)*))?)'; // ?: means we dont want the back reference
        const REGEXP_FROM='\s*(FROM)\s+(\w+)(?:\s+(\w+))?((\s*,\s*\w+(\s+\w+)?(\s+ON\s+\w+)?)*)?';
        const REGEXP_WHERE='(?:\s+(WHERE)\s+((?:\(*?(?:\?|(?:\w+\.\w+=\?)\)*?(?:\s+(?:AND|OR)\s+)?))*)\s*)?';
        const REGEXP_WHERE_INSIDE='((?:\(*?(?:\?|(?:\w+\.\w+=\?)\)*?(?:\s+(?:AND|OR)\s+)?))*)\s*';
        const REGEXP_ORDERBY='(?:\s+(ORDER BY)\s+((?:\s*\w+\.\w+\s*(DESC|ASC)?\,?)*))?';
        const REGEXP_LIMIT='(?:\s+(LIMIT)\s+([0-9]*)(?:\s*\,\s*([0-9]*))?)?';
        const REGEXP_FROM_JOINS='\,\s*(\w+)(?:\s+(\w+))?(?:\s+ON\s+(\w+))?';

        public function __construct($oql) {
            $this->_oql = $oql;
            $this->_configuration = \Art\Configuration::getInstance()->query;
        }

        public function getInfos() {
            if ($this->_infos)
                return $this->_infos;
            if (!$this->_syntaxValidated)
                if (!$this->validateSyntax())
                    throw new QueryException('bad syntax');
            $this->_map = Map::getInstance();
            // FROM
            $this->_class = $className = $this->_matches[4];
            if (!$this->_map->classExists($this->_class))
                throw new QueryException('error in FROM : class "' . $this->_class . '" does not exist');
            $alias = $lastAlias = !empty($this->_matches[5]) ? $this->_matches[5] : $this->_tmpAliases++;
            // init vars
            $classToParentAlias = array();
            $alias2class = array($alias => $className);
            $sql = array('joins' => array());
            $sql['from'] = array('table' => $this->_map->classGetDatabaseTable($className), 'alias' => $alias, 'id' => $this->_map->classGetDatabaseTableField($className, 'id'));
            // joins
            $countFieldsFrom = $countFields = $this->_map->classGetAttributeCount($className) + 1;
            $mapFieldsAlias[$alias] = $className;
            $mapFields = array($className => array('from' => 1, 'to' => $countFields));
            if (!empty($this->_matches[6])) {
                preg_match_all('/' . self::REGEXP_FROM_JOINS . '/', $this->_matches[6], $matchesJoin);
                foreach ($matchesJoin[1] as $index => $associationName) {
                    $fromAlias = !empty($matchesJoin[3][$index]) ? $matchesJoin[3][$index] : $alias;
                    if (!isset($alias2class[$fromAlias]))
                        throw new QueryException('error in JOIN : alias "' . $fromAlias . '" does not exist');
                    $joinedAlias = !empty($matchesJoin[2][$index]) ? $matchesJoin[2][$index] : $this->_tmpAliases++;
                    $fromClass = isset($alias2class[$fromAlias]) ? $alias2class[$fromAlias] : $className;
                    $alias2class[$fromAlias] = $fromClass;
                    if ($associationName == 'parent') {
                        // join
                        $join = $this->_map->classGetJoin($fromClass, $associationName, $fromAlias, $joinedAlias);
                        $sql['joins'][] = $join['table'];
                        // aliases
                        $alias2class[$joinedAlias] = $join['table']['class'];
                        $classToParentAlias[$fromClass] = $joinedAlias;
                        // map fields
                        $countFieldsFrom+=$countFields = $this->_map->classGetAttributeCount($join['table']['class']);
                        $mapFieldsAlias[$joinedAlias] = $mapFieldsAlias[$fromAlias] . '_' . $associationName;
                        $mapFields[$mapFieldsAlias[$joinedAlias]] = array('from' => $countFieldsFrom - $countFields, 'to' => $countFieldsFrom);
                    } else {
                        try {
                            // join
                            $join = $this->_map->classGetJoin($fromClass, $associationName, $fromAlias, $joinedAlias);
                            if (isset($join['association'])) {
                                // join
                                $sql['join'] = ($join['association']);
                                // map fields
                                $countFieldsFrom+=$countFields = 3;
                                $mapFieldsAlias[$joinedAlias] = $mapFieldsAlias[$fromAlias] . '_' . $associationName . '_association';
                                $mapFields[$mapFieldsAlias[$joinedAlias]] = array('from' => $countFieldsFrom - $countFields, 'to' => $countFieldsFrom);
                            }
                            $sql['joins'][] = ($join['table']);
                            // aliases
                            $alias2class[$joinedAlias] = $join['table']['class'];
                            // map fields
                            $countFieldsFrom+=$countFields = $this->_map->classGetAttributeCount($join['table']['class']);
                            $mapFieldsAlias[$joinedAlias] = $mapFieldsAlias[$fromAlias] . '_' . $associationName;
                            $mapFields[$mapFieldsAlias[$joinedAlias]] = array('from' => $countFieldsFrom - $countFields, 'to' => $countFieldsFrom);
                        } catch (MapException $e) {
                            $found = false;
                            while ($this->_map->classHasParent($fromClass)) {
                                // join parent table
                                if (!isset($classToParentAlias[$fromClass])) {
                                    $classToParentAlias[$fromClass] = $joinedAlias = $this->_tmpAliases++;
                                    $join = $this->_map->classGetJoin($fromClass, 'parent', $fromAlias, $joinedAlias);
                                    $sql['joins'][] = ($join['table']);
                                    // map fields
                                    $countFieldsFrom+=$countFields = $this->_map->classGetAttributeCount($join['table']['class']);
                                    $mapFieldsAlias[$joinedAlias] = $mapFieldsAlias[$fromAlias] . '_' . $join['table']['class'];
                                    $mapFields[$mapFieldsAlias[$joinedAlias]] = array('from' => $countFieldsFrom - $countFields, 'to' => $countFieldsFrom);
                                }else
                                    $fromAlias = $classToParentAlias[$fromClass];
                                // association of the parent
                                $fromClass = $this->_map->classGetParent($fromClass);
                                if ($this->_map->classHasAssociation($fromClass, $associationName)) {
                                    $found = true;
                                    $join = $this->_map->classGetJoin($fromClass, $associationName, $fromAlias, $joinedAlias);
                                    if (isset($join['association'])) {
                                        $sql['joins'][] = ($join['association']);
                                        // map fields
                                        $countFieldsFrom+=$countFields = 3;
                                        $mapFieldsAlias[$joinedAlias] = $mapFieldsAlias[$fromAlias] . '_' . $associationName . '_association';
                                        $mapFields[$mapFieldsAlias[$joinedAlias]] = array('from' => $countFieldsFrom - $countFields, 'to' => $countFieldsFrom);
                                    }
                                    $sql['joins'][] = ($join['table']);
                                    $alias2class[$joinedAlias] = $join['table']['class'];
                                    // map fields
                                    $countFieldsFrom+=$countFields = $this->_map->classGetAttributeCount($join['table']['class']);
                                    $mapFieldsAlias[$joinedAlias] = $mapFieldsAlias[$fromAlias] . '_' . $associationName;
                                    $mapFields[$mapFieldsAlias[$joinedAlias]] = array('from' => $countFieldsFrom - $countFields, 'to' => $countFieldsFrom);
                                }
                                $fromAlias = $joinedAlias;
                                $alias2class[$joinedAlias] = $join['table']['class'];
                            }
                            if (!$found)
                                throw $e;
                        }
                    }
                    $lastAlias = $joinedAlias;
                }
            }
            /* TODO : WHERE, ORDER BY, LIMIT */
            // where
            if (!empty($this->_matches[11]))
                $sql['where'] = $this->_matches[11];
            // order by
            if (!empty($this->_matches[13]))
                $sql['order'] = $this->_matches[13];
            // limit
            if (!empty($this->_matches[16])) {
                $sql['limit'] = array('count' => $this->_matches[16]);
                if (!empty($this->_matches[17]))
                    $sql['limit']['from'] = $this->_matches[17];
            }else
                $sql['limit'] = array('count' => $this->_configuration->limit);
            $this->_alias2class = $alias2class;
            $this->_mapFields = $mapFields;
            $this->_sql = $sql;
            return $this->_infos = array('sql' => $this->_sql, 'oql' => $this->_oql, 'mapFields' => $this->_mapFields, 'alias2class' => $this->_alias2class);
        }

        public function fetchAll($args=NULL) {
            $results = $this->_fetch($args);
            $collection = new \Art\Object\Collection\Initialised($this->_class, $results, $this->_mapFields);
            return $collection;
        }

        public function fetch($args=NULL) {
            $results = $this->_fetch($args, false);
            $collection = new \Art\Object\Collection\Initialised($this->_class, $results, $this->_mapFields);
            return $collection;
        }

        protected function _fetch($args=NULL, $all=true) {
            $this->getInfos();
            if (!is_array($args))
                $args = array($args);
            $db = \Art\Database::getInstance();
            $alias2class = $this->_alias2class;
            $map = Map::getInstance();
            $argsIndex = -1;
            if (!$this->_fetched) {
                if (!empty($this->_sql['where']))
                    $this->_sql['where'] = preg_replace_callback('|(\w*)\.(\w*)=\?|', function($p) use($alias2class, $map, $db, $args) {
                                static $argsIndex = -1;
                                return $db->quoteIdentifier($p[1]) . '.' . $db->quoteIdentifier($map->classGetDatabaseTableField($alias2class[$p[1]], $p[2])) . (is_array($args[++$argsIndex]) ? key($args[$argsIndex]) . ' ' . $db->quote(current($args[$argsIndex])) : '=' . $db->quote($args[$argsIndex]));
                            }, $this->_sql['where']);
                if (!empty($this->_sql['order']))
                    $this->_sql['order'] = preg_replace_callback('|(\w*)\.(\w*)\s*(\w*)?|', function($p) use($alias2class, $db, $map) {
                                return $db->quoteIdentifier($p[1]) . '.' . $db->quoteIdentifier($map->classGetDatabaseTableField($alias2class[$p[1]], $p[2])) . (isset($p[3]) && $p[3] == 'DESC' ? 'DESC' : 'ASC');
                            }, $this->_sql['order']);
            }
            if (!$all)
                $this->_sql['limit'] = array('count' => 1);
            $this->_fetched = true;
            return $db->select($this->_sql);
        }

        /**
         * return if the syntax is valid or not
         * @return bool
         */
        public function validateSyntax() {
            $this->_syntaxValidated = true;
            return preg_match('/^' . self::REGEXP_SELECT . self::REGEXP_FROM . self::REGEXP_WHERE . self::REGEXP_ORDERBY . self::REGEXP_LIMIT . '\s*$/', $this->_oql, $this->_matches) === 1;
        }

        // better error than just "bad syntax"
        /**
         * @todo to be done ... deport functionality, too complex to be only here
         */
        public function explainError() {
            $this->_oql;
        }
    }
    class QueryException extends \Art\Exception {
        
    }
}