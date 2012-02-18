<?php
// css
echo '<style type="text/css">' . file_get_contents('Parts/css/design.css') . file_get_contents('Parts/css/layout.css') . '</style><div class="template_content">';
// debug
error_reporting(-1);

function preCallback() {
    print_r(func_get_args());
}

function pre($v, $title=false) {
    echo '<b style="color:#ae1414">' . ($title ? strtoupper($title) : '') . '</b>     ****************************************************************************************************************************************************************************************************************************************<br/>';
    if ($v instanceof \CRUDsader\Adapter\Database\Rows) {
        table($v);
        return;
    }
    ob_start();
    if (is_string($v) && strpos(rtrim($v), 'SELECT') !== false) {
        echo \CRUDsader\Database::getInstance()->getAdapter('descriptor')->highLight($v);
        return;
    }
    $out = var_dump($v);
    $out = ob_get_clean();

    $out = str_replace(array('":protected', ']=>', '["', '"]', 'array(', "   ", 'string', 'bool', 'NULL', 'object', '"\''), array('<i>(protected)</i>', '\'</font>]=>', '[<font color="ae1414">\'', '\'</font>]', '<font color="green">ARRAY</font>(', "       ", '<font color="green">STRING</font>', '<font color="green">BOOL</font>', '<font color="green">NULL</font>', '<font color="RED"><b>OBJECT</b></font>', '\''), $out);

    print_r($out);
}

function table($var) {
    echo '<table style="border:1px thin grey" border=1>';
    $first = true;
    foreach ($var as $r) {
        if ($first) {
            echo '<tr style="font-weight:bold; background-color:grey;color:white">';
            foreach ($r as $k => $v) {
                echo '<td>' . $k . '</td>';
            }
            echo '</tr>';
            $first = false;
        }
        echo '<tr>';
        foreach ($r as $k => $v) {
            echo '<td>' . $v . '</td>';
        }
        echo '</tr>';
    }
    echo '<table>';
}
// button
echo '<a href="ut.php">back</a><br>';
// autoload
require_once('../../Autoload.php');
spl_autoload_register(array('\CRUDsader\Autoload', 'autoloader'));
\CRUDsader\Autoload::registerNameSpace('CRUDsader', '../../');

// error handling
function eh() {
    if (!error_reporting())
        return;
    pre(func_get_args());
    pre(xdebug_get_function_stack());
    pre(get_included_files());
    die('ERROR');
    return false;
}
set_error_handler('eh');
register_shutdown_function('shutdownFunction');

function shutDownFunction() {
    $error = error_get_last();
 
    if ($error !== null) {
        echo 'eRRORRR';
        pre($error);
        pre(xdebug_get_function_stack());
    }
    exit(1);
}
// bug of xml file, and for some reason it is not possible to use simplexml
$file = file_get_contents('configuration.xml');
preg_match_all('|\<const\s+name\=\"([^\"]*)\"\s+value\=\"([^\"]*)\"|', $file, $matches, PREG_PATTERN_ORDER);
foreach ($matches[1] as $index => $match) {
    define($match, $matches[2][$index]);
}
//
class Bootstrap {

    public static function setUpDatabase() {
        self::tearDownDatabase();
        mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD);
        mysql_select_db(DATABASE_NAME);
        mysql_query('SET FOREIGN_KEY_CHECKS = 0');
        $sqlFile = explode(';', file_get_contents(dirname(__FILE__) . '/Parts/database.sql'));
        foreach ($sqlFile as $sql) {
            $sql = trim($sql);
            if (!empty($sql)) {
                mysql_query($sql);
            }
        }
        mysql_query('SET FOREIGN_KEY_CHECKS = 1');
        mysql_close();
    }

    public static function tearDownDatabase() {
        mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD);
        mysql_select_db(DATABASE_NAME);
        mysql_query('SET FOREIGN_KEY_CHECKS = 0');
        $query = mysql_query('show tables') or die(mysql_error());
        while ($r = mysql_fetch_assoc($query)) {
            mysql_query('DROP TABLE `' . current($r) . '`');
        }
        mysql_query('SET FOREIGN_KEY_CHECKS = 1');
        mysql_close();
    }

    public static function setUpORMDatabase() {
        self::tearDownDatabase();
        mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD);
        mysql_select_db(DATABASE_NAME);
        mysql_query('SET FOREIGN_KEY_CHECKS = 0');
        $sqlFile = explode(';', file_get_contents(dirname(__FILE__) . '/Parts/databaseOrm.sql'));
        foreach ($sqlFile as $sql) {
            $sql = trim($sql);
            if (!empty($sql)) {
                mysql_query($sql) or die(mysql_error());
            }
        }
        mysql_query('SET FOREIGN_KEY_CHECKS = 1');
        mysql_close();
    }
}
class Database_Config extends \CRUDsader\Block {
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
class Database_BadHostConfig extends \CRUDsader\Block {
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
class Database_BadNameConfig extends \CRUDsader\Block {
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