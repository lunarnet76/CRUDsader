<?php
error_reporting(-1);
function preCallback(){
    print_r(func_get_args());
}
function pre($v,$title=false){
    echo ($title?  strtoupper($title):'').'****************************************************************************************************************************************************************************************************************************************<br/>';
    ob_start();
    $out = var_dump($v);
    $out=ob_get_clean();
    
    $out = str_replace(array('":protected',']=>','["','"]','array(',"   ",'string','bool','NULL','object','"\''),array('<i>(protected)</i>','\'</font>]=>','[<font color="ae1414">\'','\'</font>]','<font color="green">ARRAY</font>(',"       ",'<font color="green">STRING</font>','<font color="green">BOOL</font>','<font color="green">NULL</font>','<font color="RED"><b>OBJECT</b></font>','\''),$out);

    print_r($out);
}

require_once('../Autoload.php');
spl_autoload_register(array('\Art\Autoload', 'autoLoad'));
\Art\Autoload::registerNameSpace('Art', '../');

require(dirname(__FILE__).'/configuration.ini');

\Art\Configuration::getInstance()->database->password='87428742';

// database
class Database_Config extends \Art\Block {
    public static $configuration = array(
        'host' => DATABASE_HOST,
        'user' => DATABASE_USER,
        'password' => DATABASE_PASSWORD,
        'name' => DATABASE_NAME
    );

    public function __construct() {
        parent::__construct(self::$configuration);
    }
}
class Database_BadHostConfig extends \Art\Block {
    public static $configuration = array(
        'host' => 'nohost',
        'user' => DATABASE_USER,
        'password' => DATABASE_PASSWORD,
        'name' => DATABASE_NAME
    );

    public function __construct() {
        parent::__construct(self::$configuration);
    }
}
class Database_BadNameConfig extends \Art\Block {
    public static $configuration = array(
        'host' => DATABASE_HOST,
        'user' => DATABASE_USER,
        'password' => DATABASE_PASSWORD,
        'name' => 'completelyandsurelyunexistant'
    );

    public function __construct() {
        parent::__construct(self::$configuration);
    }
}
// random
class Class_Test_1 {

}

class Class_Test_2 {

}

class Class_Test_3 {

}

class Class_Test_4 {

}


?>
