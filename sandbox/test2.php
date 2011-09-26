<?php
require('../Art/Autoload.php');
spl_autoload_register(array('\Art\Autoload', 'autoLoad'));
\Art\Autoload::registerNameSpace('Art', '../Art/');

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
    if ($v instanceof \Art\Interfaces\Arrayable)
        $v = $v->toArray();
    \Art\Debug::pre($v, $title);
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
\Art\Configuration::getInstance()->database->name = 'art_test';
//\Art\Configuration::getInstance()->debug->database->profiler=true ;
\Art\Configuration::getInstance()->adapter->map->loader->xml->file = '../Test/Parts/orm.xml';

$form3=new \Art\Form('test');
$form3->add(new \Art\Form\Component(),'inp');

$form=new \Art\Form();

$form2=$form->add(new \Art\Form());

$form2->add($form3,'test2');


echo $form;