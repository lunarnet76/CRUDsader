<?php
/**
 * LICENSE: see CRUDsader/license.txt
 *
 * @author     Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     http://www.CRUDsader.com/license/2.txt
 * @version     $Id$
 * @link        http://www.CRUDsader.com/manual/
 * @since       1.0
 */
namespace CRUDsader\Adapter {
    abstract class Identifier extends \CRUDsader\Adapter{
        abstract public function getOID($classInfos);
    }
}
