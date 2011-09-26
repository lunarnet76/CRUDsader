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
     * NULL wrapper
     * @package    Art
     */
    class Nil extends \Art\Expression {
        /**
         * the expression in itself
         * @var string
         */
        protected $_expression = 'NULL';

        public function __construct() {
            
        }
    }
}