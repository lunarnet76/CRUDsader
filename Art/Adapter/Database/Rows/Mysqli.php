<?php
/**
 * LICENSE: see Art/license.txt
 *
 * @author Jean-Baptiste Verrey <jeanbaptiste.verrey@gmail.com>
 * @copyright  2010 Jean-Baptiste Verrey
 * @license    http://www.Art.com/license/2.txt
 * @version    $Id$
 * @link       http://www.Art.com/manual/
 * @since      2.0
 */
namespace Art\Adapter\Database\Rows {
    /**
     * DB results adapter for MySQL
     * @category   Adapter,Database
     * @package    Art2
     */
    class Mysqli extends \Art\Adapter\Database\Rows {
        /**
         * @var array|bool current row
         */
        protected $_current = false;
        protected $_fields = NULL;
        /**
         *
         * @var bool wether the result set has been iterated
         */
        protected $_iterated = false;

        public function setResource($ressource, $count=false) {
            parent::setResource($ressource,$count);
            $this->_fields=$this->_ressource->fetch_fields();
        }
        
        public function getFields(){
            return $this->_fields;
        }
        
        public function rewind() {
            if (!$this->_count)
                return;
            if (!$this->_iterated)
                $this->_iterated = true;
            else
                $this->_ressource->data_seek(0);
            $this->_current = $this->_ressource->fetch_row();
        }

        public function valid() {
            return NULL !== $this->_current;
        }

        public function current() {
            return $this->_current;
        }

        public function key() {
            return key($this->_current);
        }

        public function next() {
            $this->_current = $this->_ressource->fetch_row();
            return $this->_current;
        }

        public function toArray() {
            $array = array();
            foreach ($this as $v)
                $array[] = $v;
            return $array;
        }
    }
}