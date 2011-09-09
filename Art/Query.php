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
        protected $_sql;
        protected $_class;
        protected $_matches;
        protected $_mapFields;
        protected $_syntaxValidated = false;
        protected $_tmpAliases = 'z9a';

        const REGEXP_SELECT='(?:(?:\s*(SELECT)\s+((?:(?:\*)|(?:\w+\.(?:\w+|\*)\,?)\s*)*))?)'; // ?: means we dont want the back reference
        const REGEXP_FROM='\s*(FROM)\s+(\w+)(?:\s+(\w+))?((\s*,\s*\w+(\s+\w+)?(\s+ON\s+\w+)?)*)?';
        const REGEXP_WHERE='(?:\s+(WHERE)\s+((?:\(*?(?:\?|(?:\w+\.\w+=\?)\)*?(?:\s+(?:AND|OR)\s+)?))*)\s*)?';
        const REGEXP_WHERE_INSIDE='((?:\(*?(?:\?|(?:\w+\.\w+=\?)\)*?(?:\s+(?:AND|OR)\s+)?))*)\s*';
        const REGEXP_ORDERBY='(?:\s+(ORDER BY)\s+((?:\s*\w+\.\w+\s*\,?)*))?';
        const REGEXP_LIMIT='(?:\s+(LIMIT)\s+([0-9]*)(?:\s*\,\s*([0-9]*))?)?';
        const REGEXP_FROM_JOINS='\,\s*(\w+)(?:\s+(\w+))?(?:\s+ON\s+(\w+))?';

        public function __construct($oql) {
            $this->_oql = $oql;
        }

        public function getInfos() {
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
            $sql = new Database\Select();
            $sql->from(array('table' => $this->_map->classGetDatabaseTable($className), 'alias' => $alias));
            // joins
            $countFieldsFrom = $countFields = $this->_map->classGetAttributeCount($className);
            $mapFieldsAlias[$alias] = $className;
            $mapFields = array($className => array('from' => 0, 'to' => $countFields));
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
                        $sql->join($join['table']);
                        // aliases
                        $alias2class[$joinedAlias] = $join['table']['toClass'];
                        $classToParentAlias[$fromClass] = $joinedAlias;
                        // map fields
                        $countFieldsFrom+=$countFields = $this->_map->classGetAttributeCount($join['table']['toClass']);
                        $mapFieldsAlias[$joinedAlias] = $mapFieldsAlias[$fromAlias] . '_'.$associationName;
                        $mapFields[$mapFieldsAlias[$joinedAlias]] = array('from' => $countFieldsFrom-$countFields, 'to' => $countFieldsFrom);
                    } else {
                        try {
                            // join
                            $join = $this->_map->classGetJoin($fromClass, $associationName, $fromAlias, $joinedAlias);
                            if (isset($join['association'])) {
                                // join
                                $sql->join($join['association']);
                                // map fields
                                $countFieldsFrom+=$countFields = 3;
                                $mapFieldsAlias[$joinedAlias] = $mapFieldsAlias[$fromAlias] . '_'.$associationName.'_association';
                                $mapFields[$mapFieldsAlias[$joinedAlias]] = array('from' => $countFieldsFrom-$countFields, 'to' => $countFieldsFrom);
                            }
                            $sql->join($join['table']);
                            // aliases
                            $alias2class[$joinedAlias] = $join['table']['toClass'];
                            // map fields
                            $countFieldsFrom+=$countFields = $this->_map->classGetAttributeCount($join['table']['toClass']);
                            $mapFieldsAlias[$joinedAlias] = $mapFieldsAlias[$fromAlias] . '_'.$associationName;
                            $mapFields[$mapFieldsAlias[$joinedAlias]] = array('from' => $countFieldsFrom-$countFields, 'to' => $countFieldsFrom);
                        } catch (MapException $e) {
                            $found = false;
                            while ($this->_map->classHasParent($fromClass)) {
                                // join parent table
                                if (!isset($classToParentAlias[$fromClass])) {
                                    $classToParentAlias[$fromClass] = $joinedAlias = $this->_tmpAliases++;
                                    $join = $this->_map->classGetJoin($fromClass, 'parent', $fromAlias, $joinedAlias);
                                    $sql->join($join['table']);
                                    // map fields
                                    $countFieldsFrom+=$countFields = $this->_map->classGetAttributeCount($join['table']['toClass']);
                                    $mapFieldsAlias[$joinedAlias] = $mapFieldsAlias[$fromAlias] . '_'.$join['table']['toClass'];
                                    $mapFields[$mapFieldsAlias[$joinedAlias]] = array('from' => $countFieldsFrom-$countFields, 'to' => $countFieldsFrom);
                                }else
                                    $fromAlias=$classToParentAlias[$fromClass];
                                // association of the parent
                                $fromClass = $this->_map->classGetParent($fromClass);
                                if ($this->_map->classHasAssociation($fromClass, $associationName)) {
                                    $found = true;
                                    $join = $this->_map->classGetJoin($fromClass, $associationName, $fromAlias, $joinedAlias);
                                    if (isset($join['association'])) {
                                        $sql->join($join['association']);
                                        // map fields
                                        $countFieldsFrom+=$countFields = 3;
                                        $mapFieldsAlias[$joinedAlias] = $mapFieldsAlias[$fromAlias] . '_'.$associationName.'_association';
                                        $mapFields[$mapFieldsAlias[$joinedAlias]] = array('from' => $countFieldsFrom-$countFields, 'to' => $countFieldsFrom);
                                    }
                                    $sql->join($join['table']);
                                    $alias2class[$joinedAlias] = $join['table']['toClass'];
                                    // map fields
                                    $countFieldsFrom+=$countFields = $this->_map->classGetAttributeCount($join['table']['toClass']);
                                    $mapFieldsAlias[$joinedAlias] = $mapFieldsAlias[$fromAlias] . '_'.$associationName;
                                    $mapFields[$mapFieldsAlias[$joinedAlias]] = array('from' => $countFieldsFrom-$countFields, 'to' => $countFieldsFrom);
                                }
                                $fromAlias = $joinedAlias;
                                $alias2class[$joinedAlias] = $join['table']['toClass'];
                            }
                            if (!$found)
                                throw $e;
                        }
                    }
                    $lastAlias = $joinedAlias;
                }
            }
            /* TODO : WHERE, ORDER BY, LIMIT */
            $this->_mapFields = $mapFields;
            $this->_sql = $sql;
            return array('sql'=>$sql,'oql'=>$this->_oql,'mapFields'=>$mapFields);
        }

        public function execute($args=NULL) {
            $this->getInfos();
            if (!is_array($args))
                $args = array($args);
            $results = \Art\Database::getInstance()->select($this->_sql, $args);
            $collection = new \Art\Object\Collection\Initialised($this->_class, $results, $this->_mapFields);
            return $collection;
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