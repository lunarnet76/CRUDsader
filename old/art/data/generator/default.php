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
 * @category   Configuration
 * @package    Art2
 */
class Art_Data_Generator_Default {
    public static $_session;
    public static function generate($value=false,$options=array()){
        if(!isset(self::$_session)){
            $session=Art_Session::useNamespace ('randomize');
            self::$_session=$session;
            if(!isset(self::$_session->string))self::$_session->string='a';
        }
        $g=ltrim(self::$_session->string++,'a');
        return $g==''?ltrim(self::$_session->string++,'a'):$g;
    }
}
?>
