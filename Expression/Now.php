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
namespace Art\Expression {
    /**
     * NOW wrapper
     * @package Art\Expression
     */
    class Now extends \Art\Expression {
        /**
         * the expression in itself
         * @var string
         */
        protected $_expression = 'NOW()';

        public function __construct() {
            
        }

        public function __toString() {
            return date('Y-m-d');
        }
    }
}