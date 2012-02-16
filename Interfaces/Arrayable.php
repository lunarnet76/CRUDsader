<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Interfaces {
    /**
     * object can be transformed to array ?
     * @package CRUDsader\Interfaces
     */
    interface Arrayable {
        /**
         * @return array
         */
        public function toArray();
    }
}