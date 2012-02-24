<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Expression {
    /**
     * NOW wrapper
     * @package CRUDsader\Expression
     */
    class Now extends \CRUDsader\Expression {
        /**
         * the expression in itself
         * @var string
         */
        protected $_expression = 'NOW()';
        /**
         * wether to quote or not when saving to database
         * @var bool
         */
        protected $_quote = true;

        public function __construct() {
            $this->_expression=date('Y-m-d H:i:s');
        }
    }
}