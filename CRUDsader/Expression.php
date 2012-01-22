<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader {
    /**
     * tools for handling common use of NULL and date
     * @package CRUDsader
     */
    class Expression {
        /**
         * the expression in itself
         * @var string
         */
        protected $_expression;
        /**
         * wether to quote or not when saving to database
         * @var bool
         */
        protected $_quote = false;

        /**
         * @param string $expression
         */
        public function __construct($expression = false) {
            $this->_expression = $expression;
        }

        /**
         * return the expression
         * @return string
         */
        public function __toString() {
            return $this->_expression;
        }
        
        /**
         * @return bool
         */
        public function isToBeQuoted() {
            return $this->_quote;
        }
        
        /**
         * return if a var is empty or an expression that is null
         * @static
         * @param mix $var
         * @return bool 
         */
        public static function isEmpty($var){
            return empty($var) || $var instanceof Expression\Nil;
        }
    }
}
