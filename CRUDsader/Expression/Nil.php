<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Expression {
    /**
     * NULL wrapper
     * @package    CRUDsader\Expression
     */
    class Nil extends \CRUDsader\Expression {
        /**
         * the expression in itself
         * @var string
         */
        protected $_expression = 'NULL';

        public function __construct() {
            
        }
    }
}