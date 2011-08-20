<?php
class Bootstrap{
    public static $loadedSqlDatabase=false;
    public static $loadedSqlOrmDatabase=false;
    public static function loadSQLDatabase(){
        if(self::$loadedSqlDatabase)return;
        $db=\Art\Database::getInstance();
        $q=$db->query('show tables','select');
        $db->query('SET FOREIGN_KEY_CHECKS = 0;');
        foreach($q as $table){
            $db->query('DROP TABLE '.current($table));
        }
        $db->query('SET FOREIGN_KEY_CHECKS = 1;');
        $sqlFile=explode(';',file_get_contents(dirname(__FILE__).'/database.sql'));
        foreach($sqlFile as $sql){
            $sql=trim($sql);
            if(!empty($sql)){
                //pre($sql);
                $db->query($sql,'update');
            }
        }
        self::$loadedSqlDatabase=true;
    }
    
    public static function unloadSQLDatabase(){
        $db=\Art\Database::getInstance();
        $q=$db->query('show tables','select');
        $db->query('SET FOREIGN_KEY_CHECKS = 0;');
        foreach($q as $table){
            $db->query('DROP TABLE '.current($table));
        }
        $db->query('SET FOREIGN_KEY_CHECKS = 1;');
        self::$loadedSqlDatabase=false;
        
    }
    
    public static function loadSQLORMDatabase(){
        if(self::$loadedSqlOrmDatabase)return;
        $db=\Art\Database::getInstance();
        $q=$db->query('show tables','select');
        $db->query('SET FOREIGN_KEY_CHECKS = 0;');
        foreach($q as $table){
            $db->query('DROP TABLE '.current($table));
        }
         \Art\Configuration::getInstance()->adapter->map->loader->xml->file = dirname(__FILE__) . '/map.xml';
        \Art\Map::getInstance();
        $sqlFile=explode(';',file_get_contents(dirname(__FILE__).'/databaseOrm.sql'));
        foreach($sqlFile as $sql){
            $sql=trim($sql);
            if(!empty($sql)){
                //pre($sql);
                $db->query($sql,'update');
            }
        }
        $db->query('SET FOREIGN_KEY_CHECKS = 1;');
        self::$loadedSqlOrmDatabase=true;
    }
}
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

class AdapterMapExtractorInstancer extends \Art\Adapter\Map\Extractor\Database{
    public static function getInstance(){
        return new parent();
    }
}

?>
