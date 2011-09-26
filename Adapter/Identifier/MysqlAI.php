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
namespace Art\Adapter\Identifier {
    /**
     * @package    Art\Adapter\Identifier
     */
    class MysqlAI extends \Art\Adapter\Identifier {
        public function getOID($classInfos){
            return new \Art\Expression\Nil();
        }
    }
}