<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Query {
    /**
     * @category   Query
     * @package    CRUDsader
     */
    class Pagination extends \CRUDsader\Query {
        protected $_index;
        protected $_query;
        protected $_args;
        protected $_options;

        public function __construct(parent $query, array $options, $args=null) {
            $this->_index = $options['index'];
            $this->_options = $options;
            $this->_query = $query;
            $this->_args = $args;
            $session = \CRUDsader\Session::useNamespace('\\CRUDsader\\Query\\Pagination\\' . $this->_index);
             if(!isset($session->max)){
                $session->max = \CRUDsader\Database::getInstance()->countSelect($query->_sql);
            }
            if (isset($_REQUEST[$this->_index]))
                $session->start = $_REQUEST[$this->_index] > 0 ? $_REQUEST[$this->_index] : 0;
        }

        public function getObjects() {
            $this->_query->_sql['limit']=array('from'=>isset($session->start)?$session->start:0,'count'=>isset($this->_options['count'])?$this->_options['count']:$this->_query->_configuration->limit);
            $results = \CRUDsader\Database::getInstance()->select($this->_query->_sql,$this->_args);
            return new \CRUDsader\Object\Collection\Initialised($this->_query->_class, $results, $this->_query->_mapFields);
        }
        
        
    }
    class PaginationException extends \CRUDsader\Exception {
        
    }
}