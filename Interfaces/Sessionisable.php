<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Interfaces {
    /**
     * object is using session ?
     * @package CRUDsader\Interfaces
     */
    interface Sessionisable {
        function useSession($bool);
        function getSession();
        function resetSession();
    }
}