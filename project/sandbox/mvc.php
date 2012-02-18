<?php
require('../../Autoload.php');
\CRUDsader\Autoload::register();

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

\CRUDsader\Instancer::call('object','instance',array('test'));