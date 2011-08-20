<?php
/**
 * LICENSE:     see Art/license.txt
 *
 * @author      Jean-Baptiste Verrey <jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     http://www.Art.com/license/1.txt
 * @version     $Id$
 * @link        http://www.Art.com/manual/
 * @since       1.0
 */
namespace Art\Database {
    /**
     * 
     * Method chaining for SQL
     * @package     Database
     */
    class Select {
        protected static $_attributePossibilities = array(
            'from' => array(
                'table',
                'alias'
            ),
            'join' => array(
                'fromAlias',
                'fromColumn',
                'toAlias',
                'toColumn',
                'toTable',
                'type'
            ),
            'fields' => array(
                'tableAlias',
                'field',
                'fieldAlias'
            ),
            'limit' => array(
                'results',
                'offset'
            )
        );
        protected $_attributes = array(
            'fields' => array(),
            'from' => array(),
            'join' => array(),
            'limit' => array(),
            'where' => false,
            'orderBy' => false,
            'args' => array()
        );

        public function __call($name, $arguments) {
            if (isset($arguments[0]))
                $arguments = $arguments[0];
            if (!isset(self::$_attributePossibilities[$name]))
                throw new SelectException ('method "' . $name . '" does not exist');
            if (!is_array($arguments))
                throw new SelectException ('arguments must be an array for "' . $name . '"');
            $tmp = array();
            foreach (self::$_attributePossibilities[$name] as $key => $argumentName) {
                if (!isset($arguments[$argumentName]))
                    throw new SelectException ('argument "' . $argumentName . '" must be specified for "' . $name . '"');
                $tmp[$argumentName] = $arguments[$argumentName];
            }
            if ($name == 'fields') {
                $this->_attributes[$name][$tmp['tableAlias']][$tmp['fieldAlias']] = $tmp['field'];
            } else if ($name == 'from' || $name == 'limit')
                $this->_attributes[$name] = $tmp;
            else
                $this->_attributes[$name][] = $tmp;
            return $this;
        }

        public function args(array $args) {
            $this->_attributes['args'] = $args;
        }

        public function where($where, $append=false) {
            if ($append)
                $this->_attributes['where'].=$where;
            else
                $this->_attributes['where'] = $where;
            return $this;
        }

        public function orderBy($where, $append=false) {
            if ($append)
                $this->_attributes['orderBy'].=$where;
            else
                $this->_attributes['orderBy'] = $where;
            return $this;
        }

        public function getAttributes() {
            return $this->_attributes;
        }
        
        public function __toString(){
            $descriptor=\Art\Database::getInstance()->getDescriptor();
            return $descriptor->highLight($descriptor->select($this));
        }
    }
    class SelectException extends \Art\Exception {
        
    }
}