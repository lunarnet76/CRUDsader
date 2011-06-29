<?php

require_once('../Autoload.php');
spl_autoload_register(array('\Art\Autoload', 'autoLoad'));
\Art\Autoload::registerNameSpace('Art', '../');

require(dirname(__FILE__).'/configuration.ini');

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
