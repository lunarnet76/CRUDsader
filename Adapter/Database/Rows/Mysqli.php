<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Adapter\Database\Rows {
    /**
     * DB results adapter for MySQL
     * @package    CRUDsader/Adapter/Database/Rows
     */
    class Mysqli extends \CRUDsader\Adapter\Database\Rows {
        /**
         * @var array|bool current row
         */
        protected $_current = null;
        protected $_fields = array();

        /**
         *
         * @var bool wether the result set has been iterated
         */
        protected $_iterated = false;
        protected $_rowIterator = 0;

        public function setResource($ressource, $count=false) {
            parent::setResource($ressource, $count);
            $fields = $this->_ressource->fetch_fields();
            foreach ($fields as $field) {
                $this->_fields[] = $field->name;
            }
        }

        public function getFields() {
            return $this->_fields;
        }

        public function rewind() {
            if (!$this->_count)
                return;
            if (!$this->_iterated)
                $this->_iterated = true;
            else {
                $this->_ressource->data_seek(0);
                $this->_rowIterator = 0;
            }
            $this->_current = $this->_ressource->fetch_row();
        }

        public function valid() {
            return NULL !== $this->_current;
        }

        public function current() {
            if (!$this->_iterated) {
                $this->_iterated = true;
                $this->_current = $this->_ressource->fetch_row();
            }
            return $this->_current;
        }

        public function key() {
            return $this->_rowIterator;
        }

        public function next() {
            $this->_current = $this->_ressource->fetch_row();
            $this->_rowIterator++;
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