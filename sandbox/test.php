<?php
require('../Art/Autoload.php');
spl_autoload_register(array('\Art\Autoload', 'autoLoad'));
\Art\Autoload::registerNameSpace('Art','../Art/');
function eh(){
    pre(func_get_args());
    return true;
}
set_error_handler('eh');
function preCallback() {
    print_r(func_get_args());
}

function pre($v, $title=false) {
    \Art\Debug::pre($v,$title);
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
\Art\Configuration::getInstance()->adapter->map->loader->xml->file='../Test/parts/map.xml';

$form=new \Art\Form('person');
$name=$form->add(new \Art\Form\Component\Text('name'));

$form2=$form->add(new \Art\Form());
$name2=$form2->add(new \Art\Form\Component\Text('name2'));
if($form->receive() && !$form->error()){
    echo 'recu';
}
echo $form;
pre($_REQUEST);

/*$person=new Object('person');
$form=$person->getForm('FROM person p,parent c,hasEmail e');

if($form->receive() && !$form->error()){
    //pre($person->toArray());
}
    echo $form;*/