<?php
/**
 * LICENSE: see Art/license.txt
 *
 * @author     Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     http://www.Art.com/license/2.txt
 * @version     $Id$
 * @link        http://www.Art.com/manual/
 * @since       1.0
 */
namespace Art\Interfaces {
    /**
     * @category   Interfaces
     * @package    Art
     */
    interface Configurable{
        public function setConfiguration(\Art\Block $block=null);
        public function getConfiguration();
    }   
}