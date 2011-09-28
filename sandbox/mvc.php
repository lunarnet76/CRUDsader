<?php
require('../CRUDsader/Autoload.php');
spl_autoload_register(array('\CRUDsader\Autoload', 'autoLoad'));
\CRUDsader\Autoload::registerNameSpace('CRUDsader', '../CRUDsader/');

function eh() {
    pre(func_get_args());
    pre(debug_backtrace());
    die('ERROR');
    return true;
}
set_error_handler('eh');

function preCallback() {
    print_r(func_get_args());
}

function pre($v, $title=false) {
    if ($v instanceof \CRUDsader\Interfaces\Arrayable)
        $v = $v->toArray();
    \CRUDsader\Debug::pre($v, $title);
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
\CRUDsader\Configuration::getInstance()->adapter->mvc->router->explicit=array(
    'blog/'=>'Controller/Blog'
);
$fc=\CRUDsader\MVC\Controller\Front::getInstance();
$fc->route();

//$fc->dispatch();