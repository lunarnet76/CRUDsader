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
        protected static $_associationClassAlias = 'assoc';
        protected $_oql;
        protected $_sql;
        protected $_class;
        protected $_mapFields;

        const REGEXP_SELECT='(?:(?:\s*(SELECT)\s+((?:(?:\*)|(?:\w+\.(?:\w+|\*)\,?)\s*)*))?)';
        const REGEXP_FROM='\s*(FROM)\s+(\w+)\s+(\w+)((?:\s+JOIN\s+\w+(?:\(\w+\))?\s+\w+\s+ON\s+\w+(?:\s+ASSOCIATION\s+\w+)?)*)?';
        const REGEXP_WHERE='(?:\s+(WHERE)\s+((?:\(*?(?:\?|(?:\w+\.\w+=\?)\)*?(?:\s+(?:AND|OR)\s+)?))*)\s*)?';
        const REGEXP_WHERE_INSIDE='((?:\(*?(?:\?|(?:\w+\.\w+=\?)\)*?(?:\s+(?:AND|OR)\s+)?))*)\s*';
        const REGEXP_ORDERBY='(?:\s+(ORDER BY)\s+((?:\w+\.\w+\,?)*))?';
        const REGEXP_LIMIT='(?:\s+(LIMIT)\s+([0-9]*)(?:,([0-9]*))?)?';
        const REGEXP_FROM_JOINS='JOIN\s+(\w+)(?:\((\w+)\))?\s+(\w+)\s+ON\s+(\w+)(?:\s+ASSOCIATION\s+(\w+))?';
        public function __construct($oql) {
            $this->_oql = $oql;
            $this->_map = Map::getInstance();
            $matches = $this->_validateSyntax();
            if (empty($matches[4])) {
                $this->_class=$className = $alias = $matches[5];
            } else {
                $this->_class=$className = $matches[4];
                $alias = $matches[5];
            }
            if (empty($className))
                throw new QueryException('error in FROM : no class specified');
            if (!$alias && (!empty($matches[1]) || !empty($matches[7]) || !empty($matches[9])))
                throw new QueryException('error in FROM : no alias specified');
            $tableAliases = array($alias => $className);
            $mapFields=array($className=>$this->_map->classGetAttributeCount($className));
            // from
            if (!$this->_map->classExists($className))
                throw new QueryException('error in FROM : class "' . $className . '" does not exist');
            $sql = new Database\Select();
            $sql->from(array('table' => $this->_map->classGetDatabaseTable($className), 'alias' => $alias));

            // joins
            $aliasToClass=array($alias=>$className);
            $countFields=$this->_map->classGetAttributeCount($className);
            $countFieldsFromIndex=$countFields;
            $mapFields=array($className=>array('from'=>0,'to'=>$countFields));
            $mapFieldsAlias[$alias]=$className;
            if (!empty($matches[6])) {
                preg_match_all('/' . self::REGEXP_FROM_JOINS . '/', $matches[6], $joinInfos);
                foreach ($joinInfos[1] as $index => $joinedClass) {
                    $associationName = !empty($joinInfos[2][$index]) ? $joinInfos[2][$index] : $joinedClass;
                    $joinedAlias = $joinInfos[3][$index];
                    $fromAlias = $joinInfos[4][$index];
                    $associationClassAlias = empty($joinInfos[5][$index]) ? self::$_associationClassAlias++ : $joinInfos[5][$index];
                    $join = $this->_map->classGetJoin(isset($aliasToClass[$fromAlias])?$aliasToClass[$fromAlias]:$className, $associationName, $fromAlias, $joinedAlias, $associationClassAlias);
                    $aliasToClass[$joinedAlias]=$join['table']['toClass'];
                    if (isset($join['association'])){
                        $sql->join($join['association']);
                        $countFields=$this->_map->classGetAttributeCount($join['association']['toClass']);
                        $mapFields[$mapFieldsAlias[$fromAlias].'_'.$joinedClass.'_association']=array('from'=>$countFieldsFromIndex,'to'=>$countFieldsFromIndex+$countFields);
                        $countFieldsFromIndex+=$countFields;
                    }
                    $countFields=$this->_map->classGetAttributeCount($join['table']['toClass']);
                    $mapFieldsAlias[$joinedAlias]=$mapFieldsAlias[$fromAlias].'_'.$joinedClass;
                    $mapFields[$mapFieldsAlias[$fromAlias].'_'.$joinedClass]=array('from'=>$countFieldsFromIndex,'to'=>$countFieldsFromIndex+$countFields);
                    $countFieldsFromIndex+=$countFields;
                    $sql->join($join['table']);
                }
            }
            // where
             if (!empty($matches[7])) {
                 $map=$this->_map;
                 $sql->where(preg_replace_callback('|(\w+)\.(\w+)|',function ($infos)use($tableAliases,$map){
                    return $infos[1].'.'.$map->classGetDatabaseTableField($tableAliases[$infos[1]],$infos[2]);;
                },$matches[8]));
             }
             // fields
             /*
              * $matches[2];
              */
            $this->_mapFields=$mapFields;
            $this->_sql = $sql;
        }
        public function execute($args=NULL) {
            if (!is_array($args))
                $args = array($args);
            $results=\Art\Database::getInstance()->select($this->_sql, $args);
            $collection=new \Art\Object\Collection\Initialized($this->_class,$results,$this->_mapFields);
            return $collection;
        }
        protected function _validateSyntax() {
            if (!preg_match('/^' . self::REGEXP_SELECT . self::REGEXP_FROM . self::REGEXP_WHERE . self::REGEXP_ORDERBY . self::REGEXP_LIMIT . '\s*$/', $this->_oql, $matches))
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