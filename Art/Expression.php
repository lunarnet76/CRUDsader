<?php
/**
 * LICENSE: see Art/license.txt
 *
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     http://www.Art.com/license/1.txt
 * @version     $Id$
 * @link        http://www.Art.com/manual/
 * @since       1.0
 */
namespace Art {
    /**
     * @package    Art
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
            return empty($var) || $var instanceof \Art\Expression\Void;
        }
    }
}
