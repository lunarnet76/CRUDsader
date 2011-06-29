<?php
/**
 * generate random values
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
 * @category   Data
 * @package    Art2
 */
class Art_Data_Generator_Boolean {
    public static function generate(){
        return (bool)rand(0,1);
    }
}
?>
