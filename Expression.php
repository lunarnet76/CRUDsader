<?php
/**
 * LICENSE: see CRUDsader/license.txt
 *
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     http://www.CRUDsader.com/license/1.txt
 * @version     $Id$
 * @link        http://www.CRUDsader.com/manual/
 * @since       1.0
 */
namespace CRUDsader {
    /**
     * @package    CRUDsader
     */
    class Expression {
        /**
         * the expression in itself
         * @var string
         */
        protected $_expression;

        /**
         * @param string $expression
         */
        public function __construct($expression) {
            $this->_expression = $expression;
        }

        /**
         * return the expression
         * @return string
         */
        public function __toString() {
            return $this->_expression;
        }
        
        public static function isEmpty($var){
            return empty($var) || (string)$var=='NULL';
        }
    }
}
