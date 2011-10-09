<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Adapter {
    /**
     * return a unique Object identifier
     * @package CRUDsader\Adapter
     */
    abstract class Identifier extends \CRUDsader\Adapter{
        /**
         * return a unique OID
         */
        abstract public function getOID($classInfos);
    }
}
