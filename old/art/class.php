<?php
/**
 * utility to include classes that follows the naming convention My_Class mapped to My/Class.php
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
 * @category   Class
 * @package    Art2
 */
class Art_Class {
    /**
     * @var array the loaded classes, array($className=>$includedFile), public to allow manual loading of class
     */
    public static $loaded=array();
    
    /**
     * @var string all the classes are included from this directory
     */
    protected static $_nameSpaces=array(
        'Art'=>'library/'
    );

    public static function registerNameSpace($name,$folder){
        self::$_nameSpaces[$name]=$folder;
    }

    public static function unregisterNameSpace($name){
        unset(self::$_nameSpaces[$name]);
    }

    public static function getRegisteredNamespace(){
        return self::$_nameSpaces;
    }

    public static $tries=array();

    /**
     * return whether or not the class can be used and loaded, meaning its class file can be included and it has the good name
     * @param string $className
     * @return bool|string false or the the path of the file that contains the class
     */
    public static function isLoadable($className) {
         if(isset(self::$loaded[$className]))
            return self::$loaded[$className];
        $file=str_replace('_','/',strtolower($className)).'.php';
        $folder= '';
        $ret=false;
        if(false!==$pos=strpos($className,'_')){
            $nameSpace=substr($className,0,$pos);
            if(isset(self::$_nameSpaces[$nameSpace])){
                $folder=self::$_nameSpaces[$nameSpace];
                if(is_array($folder)){
                    foreach($folder as $try){
                        $ret=file_exists($try.$file)?$try.$file:false;
            }
                }else{
                 
                    $ret=file_exists($folder.$file)?$folder.$file:false;
                }
            }else{
                $ret=file_exists(self::$_nameSpaces['Art'].$file)?self::$_nameSpaces['Art'].$file:false;
            }
        }else{
            if(file_exists($file))$ret='library/'.$file;
        }
        return $ret;
    }

    /**
     * load the class
     * @param string $className
     * @throws Art_Class_Exception
     */
    public static function load($className) {
        if(isset(self::$loaded[$className]) || class_exists($className))return;
        $path=self::isLoadable($className);
        if($path===false)
            throw new Art_Class_Exception('class "'.$className.'" could not be loaded');
        self::$loaded[$className]=$path;
        require($path);
    }

    /**
     * autoloader class for spl_autoload_register or __autoload, create class inheriting from Exception if finishes by _Exception and do not exists
     * @param string $className
     * @return string the path of the file that contains the class
     * @throws Art_Class_Exception
     */
    public static function autoload($className) {
        try{
            self::load($className);
        }catch(Art_Class_Exception $e){
            if(strrpos($className,'_Exception')!==false){
               eval('class '.$className.' extends Exception{}');
            }
            else{
                echo '<pre>';print_r(self::$tries);echo '</pre>';
                throw new Art_Class_Exception('class "'.$className.'" not found');
            }
        }
    }
}
class Art_Class_Exception extends Exception{}
?>