<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader {
    /**
     * @package CRUDsader
     */
    class Query implements Interfaces\Configurable {
        protected $_oql;
        protected $_oqlSelect = false;
        protected $_db;
        protected $_fetched = false;
        protected $_extraColumns = false;
        protected $_sql;
        protected $_class;
        protected $_matches;
        protected $_mapFields;
        protected $_mapFieldsAlias;
        protected $_alias2class;
        protected $_syntaxValidated = false;
        protected $_tmpAliases = 'z9a';
        protected $_infos = false;
        //const REGEXP_SELECT = '(?:(?:\s*(SELECT)\s+((?:(?:[\*\?])|(?:\w+\.(?:\w+|\*)\,?)\s*)*))?)'; // ?: means we dont want the back reference
        const REGEXP_FROM = '\s*(FROM)\s+(\w+)(?:\s+(\w+))?((\s*,\s*\w+(\s+\w+)?(\s+ON\s+\w+)?)*)?';
        const REGEXP_WHERE = '(?:\s+(WHERE)\s+((?:\(*?(?:\?|(?:[\w$]+\.[\w$]+=\?)\)*?(?:\s+(?:AND|OR)\s+)?))*)\s*)?';
        const REGEXP_WHERE_INSIDE = '((?:\(*?(?:\?|(?:\w+\.\w+=\?)\)*?(?:\s+(?:AND|OR)\s+)?))*)\s*';
        const REGEXP_GROUPBY = '(?:\s+(GROUP BY)\s+((?:\s*(?:\w+\.\w+)\s*\,?)*))?';
        const REGEXP_GROUPBY_INSIDE = '\s*(\w+)\.(\w+)\s*\,?';
        const REGEXP_ORDERBY = '(?:\s+(ORDER BY)\s+((?:\s*(?:\w+\.\w+|\?)\s*(DESC|ASC)?\,?)*))?';
        const REGEXP_LIMIT = '(?:\s+(LIMIT)\s+([0-9]*)(?:\s*\,\s*([0-9]*))?)?';
        const REGEXP_FROM_JOINS = '\,\s*(\w+)(?:\s+(\w+))?(?:\s+ON\s+(\w+))?';

        public function __construct($oql) {
            $this->_oql = $oql;
            $this->_db = \CRUDsader\Instancer::getInstance()->database;
            $this->setConfiguration(\CRUDsader\Instancer::getInstance()->configuration->query);
        }

        /**
         * @param \CRUDsader\Block $block 
         */
        public function setConfiguration(\CRUDsader\Block $block = null) {
            $this->_configuration = $block;
        }

        /**
         * @return  \CRUDsader\Block $block 
         */
        public function getConfiguration() {
            return $this->_configuration;
        }

        public function getOQL() {
            return $this->_oql;
        }

        public function getInfos() {
            if ($this->_infos)
                return $this->_infos;
            $this->_splitOql();
            $this->_map = \CRUDsader\Instancer::getInstance()->map;
            // FROM
            $this->_class = $className = $this->_matches[2];
            if (!$this->_map->classExists($this->_class))
                throw new QueryException($this, 'error in FROM : class "' . $this->_class . '" does not exist');
            $alias = $lastAlias = !empty($this->_matches[3]) ? $this->_matches[3] : ++$this->_tmpAliases;
            // init vars
            $classToParentAlias = array();
            $alias2class = array($alias => $className);
            $sql = array('joins' => array());
            $sql['from'] = array('table' => $this->_map->classGetDatabaseTable($className), 'alias' => $alias, 'id' => $this->_map->classGetDatabaseTableField($className, 'id'));
            // joins
            $countFieldsFrom = $countFields = $this->_map->classGetAttributeCount($className) + 1;
            $mapFieldsAlias[$alias] = $className;
            $mapFields = array($className => array('from' => 1, 'to' => $countFields));
            if (!empty($this->_matches[4])) {
                preg_match_all('/' . self::REGEXP_FROM_JOINS . '/', $this->_matches[4], $matchesJoin);
                foreach ($matchesJoin[1] as $index => $associationName) {
                    $fromAlias = !empty($matchesJoin[3][$index]) ? $matchesJoin[3][$index] : $alias;
                    if (!isset($alias2class[$fromAlias]))
                        throw new QueryException($this, 'error in JOIN : alias "' . $fromAlias . '" does not exist');
                    $joinedAlias = !empty($matchesJoin[2][$index]) ? $matchesJoin[2][$index] : ++$this->_tmpAliases;
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
                                $sql['joins'][] = ($join['association']);
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
                                    $classToParentAlias[$fromClass] = $joinedAlias = ++$this->_tmpAliases;
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
            /*  WHERE, GROUP BY, ORDER BY, LIMIT */
            // where
            if (!empty($this->_matches[9]))
                $sql['where'] = $this->_matches[9];
            // group by
            if (!empty($this->_matches[11])) {
                preg_match_all('/' . self::REGEXP_GROUPBY_INSIDE . '/', $this->_matches[11], $groupBy);
                $sql['group'] = $groupBy;
            }
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
            $this->_countFields = $countFieldsFrom;
            $this->_mapFields = $mapFields;
            $this->_mapFieldsAlias = $mapFieldsAlias;
            $this->_sql = $sql;
            return $this->_infos = array('sql' => $this->_sql, 'oql' => $this->_oql, 'mapFields' => $this->_mapFields, 'alias2class' => $this->_alias2class);
        }

        /**
         * @todo this is a hack, include in query...
         * @param type $fields 
         */
        public function groupBy($fields) {
            if (!is_array($fields))
                $fields = array($fields);
            $this->getInfos();
            $group = array();
            foreach ($fields as $clause) {
                $ex = explode('.', $clause);
                $group[] = array($ex[0], $ex[1]);
                $this->_mapFields[self::MAPFIELD_ALIAS_GROUPBY] = ++$this->_countFields;
            }
            $this->_sql['group'] = $group;
        }

        public function getSQL() {
            return $this->_sql;
        }

        public function fetchAll($args = NULL) {
            $this->_prefetch($args);
            $results = $this->_db->select($this->_sql);
            $collection = \CRUDsader\Instancer::getInstance()->{'object.collection.initialised'}($this->_class, $results, $this->_mapFields, $this->_extraColumns);
            return $collection;
        }

        public function fetch($args = NULL) {
            $this->_prefetch($args, false);
            $results = $this->_db->select($this->_sql);
            $collection = \CRUDsader\Instancer::getInstance()->{'object.collection.initialised'}($this->_class, $results, $this->_mapFields, $this->_extraColumns);
            return $collection->count() ? $collection[0] : false;
        }

        public function paginate(array $options, $args = NULL) {
            if (!isset($options['index']))
                throw new QueryException('you must specify the index option');
            $session = \CRUDsader\Session::useNamespace('\\CRUDsader\\Query\\' . $options['index']);
            $this->_prefetch($args, true);
            return new \CRUDsader\Query\Pagination($this, $options, $args);
        }

        protected function _prefetch($args = NULL, $all = true) {
            $this->_argsIndex = -1;
            $this->getInfos();
            if (!is_array($args))
                $args = array($args);
            $alias2class = $this->_alias2class;
            $map = \CRUDsader\Instancer::getInstance()->map;
            if (!$this->_fetched) {
                $unlexicalThis = $this;
                $db = $this->_db;
                if (!empty($this->_sql['where']))
                    $this->_sql['where'] = preg_replace_callback('|([\w$]+)\.([\w$]+)=\?|', function($p) use($alias2class, $map, $db, $args, $unlexicalThis) {
                                if ($p[1] == '$')
                                    return $args[++$unlexicalThis->_argsIndex];
                                $calculation = (is_array($args[++$unlexicalThis->_argsIndex]) ? key($args[$unlexicalThis->_argsIndex]) . ' ' . $db->quote(current($args[$unlexicalThis->_argsIndex])) : '=' . $db->quote($args[$unlexicalThis->_argsIndex]));
                                return $db->quoteIdentifier($p[1]) . '.' . $db->quoteIdentifier($map->classGetDatabaseTableField($alias2class[$p[1]], $p[2])) . $calculation;
                            }, $this->_sql['where']);
                if (!empty($this->_sql['order']))
                    $this->_sql['order'] = preg_replace_callback('|\?|', function($p) use($alias2class, $db, $map, $args, $unlexicalThis) {
                                return $args[++$unlexicalThis->_argsIndex];
                            }, $this->_sql['order']);
            }
            if (!$all)
                $this->_sql['limit'] = array('count' => 1);


            if ($this->_oqlSelect) {
                $newMapFields = array();
                $index = 1;
                $aliases = $this->_mapFieldsAlias;
                $extraColumns = array();
                $this->_sql['select'] = preg_replace_callback('|([\w]+)\.(\?)?([\w\*]+)|', function($p) use($alias2class, $map, $aliases, &$newMapFields, &$index, &$sql, &$extraColumns) {
                            $indexPlus = ($p[3] == '*' ? $map->classGetAttributeCount($alias2class[$p[1]]) : 1);
                            if (!isset($newMapFields[$aliases[$p[1]]]))
                                $newMapFields[$aliases[$p[1]]] = array('from' => $index, 'to' => $index + $indexPlus);
                            else {
                                $newMapFields[$aliases[$p[1]]]['to'] = $index + $indexPlus;
                            }
                            $index+=$indexPlus;
                            
                            if (empty($p[2]))
                                return $p[1] . \CRUDsader\Database\Descriptor::$TABLE_ALIAS_SUBQUERY . '.' . $p[3];
                            else {
                                $extraColumns[$index-1] = $p[3];
                                return 'extra_' . $p[1] . '_' . $p[3];
                            }
                        }, $this->_oqlSelect);
                if (!empty($newMapFields)) {
                    $this->_extraColumns = $extraColumns;
                    $this->_mapFields = $newMapFields;
                    $this->_countFields = $index;
                }
            }

            $this->_fetched = true;
        }

        /**
         * return if the syntax is valid or not
         * @return bool
         */
        protected function _splitOql() {
            $this->_syntaxValidated = true;
            $posFrom = strpos($this->_oql, 'FROM');
            if (false !== $posSelect = strpos($this->_oql, 'SELECT')) {
                $l = strlen('SELECT');
                $this->_oqlSelect = substr($this->_oql, $posSelect + $l, $posFrom - $l);
                $this->_oql = substr($this->_oql, $posFrom);
            }
            return preg_match('/^' . self::REGEXP_FROM . self::REGEXP_WHERE . self::REGEXP_GROUPBY . self::REGEXP_ORDERBY . self::REGEXP_LIMIT . '\s*$/', $this->_oql, $this->_matches) === 1;
        }

        // better error than just "bad syntax"
        /**
         * @todo to be done ... deport functionality, too complex to be only here
         */
        public function explainError() {
            $this->_oql;
        }
    }
    class QueryException extends \CRUDsader\Exception {

        public function __construct($query, $error) {
            $this->message = $error;
            $this->query = $query;
        }

        public function getQuery() {
            return $this->query;
        }
    }
}