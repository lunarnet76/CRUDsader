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
        protected static $_associationTableAlias = '__ASSOC';
        protected $_oql;

        const REGEXP_SELECT='(?:(?:\s*(SELECT)\s+((?:(?:\*)|(?:\w+\.(?:\w+|\*)\,?)\s*)*))?)';
        const REGEXP_FROM='\s*(FROM)\s+(\w+)\s+(\w+)((?:\s+JOIN\s+\w+(?:\(\w+\))?\s+\w+\s+ON\s+\w+(?:\s+ASSOCIATION\s+\w+)?)*)?';
        const REGEXP_WHERE='(?:\s+(WHERE)\s+((?:\(*?(?:\?|(?:\w+\.\w+=\?)\)*?(?:\s+(?:AND|OR)\s+)?))*)\s*)?';
        const REGEXP_ORDERBY='(?:\s+(ORDER BY)\s+((?:\w+\.\w+\,?)*))?';
        const REGEXP_LIMIT='(?:\s+(LIMIT)\s+([0-9]*)(?:,([0-9]*))?)?';
        const REGEXP_FROM_JOINS='JOIN\s+(\w+)(?:\((\w+)\))?\s+(\w+)\s+ON\s+(\w+)(?:\s+ASSOCIATION\s+(\w+))?';

        public function __construct($oql) {
            pre($oql);
            $this->_oql = $oql;
            $this->_map = Map::getInstance();
            $matches = $this->_validateSyntax();
            /*
              [1] => SELECT
              [2] => fields
              [3] => FROM
              [4] => employee
              [5] => e
              [6] =>  JOIN tabled(name) td  ON e ASSOCIATION b
              [7] => WHERE
              [8] => (e.t=? AND e.v=?) OR ?
              [9] => ORDER BY
              [10] => e.t,v.g
              [11] => LIMIT
              [12] => 10
             */

            if (empty($matches[4])) {
                $class = $alias = $matches[5];
            } else {
                $class = $matches[4];
                $alias = $matches[5];
            }
            if (empty($class))
                throw new QueryException('error in FROM : no class specified');
            if (!$alias && (!empty($matches[1]) || !empty($matches[7]) || !empty($matches[9])))
                throw new QueryException('error in FROM : no alias specified');
            $tableAliases = array($alias => $class);
            // from
            if (!$this->_map->classExists($class))
                throw new QueryException('error in FROM : class "' . $class . '" does not exist');
            $sql = new Database\Select(array('table' => $this->_map->classGetTable($class), 'alias' => $alias));

            // joins
            if (!empty($matches[6])) {
                preg_match_all('/' . self::REGEXP_FROM_JOINS . '/', $matches[6], $joinInfos);
                /* $joinInfos);
                  [0] => JOIN table(name)  ON e   ASSOCIATION  b
                  [1] table
                  [2] name
                  [3] t
                  [4] e
                  [5] b */
                foreach ($joinInfos[1] as $index => $joinedClass) {
                    $table = $joinInfos[1][$index];
                    $associationName = !empty($joinInfos[2][$index]) ? $joinInfos[2][$index] : $joinedClass;
                    $joinedAlias = $joinInfos[3][$index];
                    $on = $joinInfos[4][$index];
                    $associationClassAlias = $joinInfos[5][$index];
                    if (!$this->_map->classHasAssociation($class, $associationName))
                        throw new QueryException('error in JOIN : class "' . $class . '" has no association "' . $associationName . '"');
                    $association = $this->_map->classGetAssociation($class, $associationName);
                    /* $associationInfos
                      ARRAY(9) {
                      ['to']=>
                      STRING(7) "address"
                      ['name']=>
                      STRING(10) "hasAddress"
                      ['class']=>
                      STRING(15) "employeeAddress"
                      ['cardinality']=>
                      STRING(10) "one-to-one"
                      ['composition']=>
                      BOOL(true)
                      ['min']=>
                      int(0)
                      ['max']=>
                      int(1)
                      ['reference']=>
                      BOOL(false)
                      ['scenario']=>
                      int(1)
                      }
                     */
                    switch ($association['scenario']) {
                        case '1':
                            $sql->join(array(
                                'fromAlias' => $on,
                                'fromColumn' => 'id',
                                'toAlias' => $associationClassAlias,
                                'toColumn' => $class,
                                'toTable' => $association['databaseTable'],
                                'type' => 'left'
                            ));

                            break;/*
                            if ($association['class']) {
                                if (empty($associationClassAlias))
                                    $associationClassAlias = ++self::$_associationTableAlias;
                                // association table

                                $sql->join(array(
                                    'fromAlias' => $associationClassAlias,
                                    'fromColumn' => $joinedClass,
                                    'toAlias' => $joinedAlias,
                                    'toColumn' => 'id',
                                    'toTable' => $association['toTable'],
                                    'type' => 'left'
                                ));
                            }
                            else if ($association['reference'] == 'internal')
                                $sql->join(array(
                                    'fromAlias' => $on,
                                    'fromColumn' => $joinedClass,
                                    'toAlias' => $alias,
                                    'toColumn' => 'id',
                                    'toTable' => $association['table'],
                                    'type' => 'left'
                                ));
                            else // external
                                $sql->join(array(
                                    'fromAlias' => $alias,
                                    'fromColumn' => 'id',
                                    'toAlias' => $on,
                                    'toColumn' => $joinedClass,
                                    'toTable' => $association['table'],
                                    'type' => 'left'
                                ));
                            break;*/
                    }
                }
            }
            pre($sql);
            exit;
        }

        protected function _validateSyntax() {
            if (!preg_match('/^' . self::REGEXP_SELECT . self::REGEXP_FROM . self::REGEXP_WHERE . self::REGEXP_ORDERBY . self::REGEXP_LIMIT . '$/', $this->_oql, $matches))
                throw new QueryException('bad syntax');
            return $matches;
        }

        // better error than just "bad syntax"
        public function explainError() {
            $this->_oql;
        }
    }
    class QueryException extends \Art\Exception {
        
    }
}