<?php
/**
 * DB results adapter, DO NOT extend Art_Adapter_Abstract as it is not singletoned!
 *
 * LICENSE: see Art/license.txt
 *
 * @author Jean-Baptiste Verrey <jeanbaptiste.verrey@gmail.com>
 * @copyright  2010 Jean-Baptiste Verrey
 * @license    http://www.Art.com/license/2.txt
 * @version    $Id$
 * @link       http://www.Art.com/manual/
 * @since      2.0
 */
/**
 * @category   Adapter,Database
 * @package    Art2
 */
abstract class Art_Adapter_Database_Result_Abstract implements Iterator{
    /**
     * @abstract
     * @param ressource|object $results
     * @param string $sql
     * @param int $count number of OBJECTS (and not rows)
     */
    abstract public function __construct($results,$sql,$count);
    /**
     * @abstract
     * @return the number of OBJECTS (and not rows)
     */
    abstract public function count();
    /**
     * @abstract
     * @return array
     */
    abstract public function toArray();

}
?>