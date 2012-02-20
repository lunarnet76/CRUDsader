<?php
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
        echo \CRUDsader\Instancer::getInstance()->database->getDependency('descriptor')->highLight($v);
        return;
    }
    $out = var_dump($v);
    $out = ob_get_clean();

    $out = str_replace(array('":protected', ']=>', '["', '"]', 'array(', "   ", 'string', 'bool', 'NULL', 'object', '"\''), array('<i>(protected)</i>', '\'</font>]=>', '[<font color="ae1414">\'', '\'</font>]', '<font color="green">ARRAY</font>(', "       ", '<font color="green">STRING</font>', '<font color="green">BOOL</font>', '<font color="green">NULL</font>', '<font color="RED"><b>OBJECT</b></font>', '\''), $out);

    echo '<pre>';
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


$sl = \CRUDsader\Instancer::getInstance();
//\CRUDsader\Instancer::getInstance()->map->extract();
try{
$q = new \CRUDsader\Query('SELECT us.*,count(u.#id) us.?count,MAX(`u___sq`.id) us.?cid,u.* FROM userSubGroup us, user u WHERE u.id=? OR ? GROUP BY us.id ORDER BY u.id ASC');
$rs = $q->fetchAll(array(1,'true'));
pre($sl->database->getSQL());

pre(count($rs));
foreach($rs as $r){
    pre($r->toArray());
}

}catch(Exception $e){
    pre($e);
}

