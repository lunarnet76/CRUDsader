<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Interfaces {
    /**
     * object has helper?
     * @package CRUDsader\Interfaces
     */
    interface Helpable {

        public static function hasHelper($name);

        public static function getHelper($name);
    }
}