<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Adapter\Database {
    /**
     * row set
     * @package    CRUDsader
     */
    abstract class Rows extends \CRUDsader\Adapter implements \Iterator {
        /**
         * @var ressource
         */
        protected $_ressource;
        /**
         * number of rows
         * @var int 
         */
        protected $_count;

        /**
         * @abstract
         * @param ressource $results
         * @param int $count number of objects
         */
        public function setResource($ressource, $count=false) {
            $this->_ressource = $ressource;
            $this->_count = $count;
        }

        /**
         * @return the number of OBJECTS (and not rows)
         */
        public function count() {
            return $this->_count;
        }
        
        /**
         * @return array indexed of fields
         */
        abstract public function getFields();
    }
}