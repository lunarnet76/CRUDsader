<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Interfaces {
    /**
     * object has dependencies ???
     * @pacakge CRUDsader\Interfaces
     */
    interface Dependent {
        public function setDependency($index, $instancerIndex);
        public function getDependency($index);
        public function hasDependency($index);
    }
}