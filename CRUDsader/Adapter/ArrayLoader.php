<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Adapter {
    /**
     * create an array from a source
     * @package CRUDsader\Adapter
     */
    abstract class ArrayLoader extends \CRUDsader\Adapter {
        /**
         * @return array
         */
        abstract public function __construct($options=null);
    }
}
