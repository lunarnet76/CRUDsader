<?php
/**
 * return adapter in function of parameters
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
 * @category   Adapter
 * @package    Art2
 */
class Art_Adapter_Factory {
    /**
     * return an adapter of the specified type, with the implementation configured in Art_Configuration->adapter
     * @static
     * @param string|array $type type of adapter wanted e.g.: configuration | array('database'=>'connector')
     * @return Art_Adapter_Abstract
     */
    public static function getInstance($type){
        $class=self::getClass($type);
        return call_user_func_array(array($class,'getInstance'),array());
    }

    /**
     * return the class of an adapter of the specified type, with the implementation configured in Art_Configuration->adapter
     * @static
     * @param string|array $type type of adapter wanted e.g.: configuration | array('database'=>'connector')
     * @return Art_Adapter_Abstract
     */
    public static function getClass($type){
        $configuration=Art_Configuration::getInstance();
        if(is_array($type)){
            if(!isset($configuration->adapter->{key($type)}->{current($type)}))
                throw new Art_Adapter_Factory_Exception('adapter <b>'.key($type).'</b>/<b>'.current($type).'</b> does not exist');
            $parameter=$configuration->adapter->{key($type)}->{current($type)};
            $type=key($type).'_'.ucfirst(current($type));
        }else{
            if(!isset($configuration->adapter->$type))
                throw new Art_Adapter_Factory_Exception('adapter <b>'.($type).'</b> does not exist');
            $parameter=$configuration->adapter->{$type};
        }
        return $configuration->adapter->classNameSpace.'_'.ucfirst($type).'_'.ucfirst($parameter);
    }
}
?>
