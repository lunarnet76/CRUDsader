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

        public function __construct() {
            
        }

        public function __toString() {
            return date('Y-m-d');
        }
    }
}