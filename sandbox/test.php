<?php
require('../Art/Autoload.php');
spl_autoload_register(array('\Art\Autoload', 'autoLoad'));
\Art\Autoload::registerNameSpace('Art', '../Art/');

function eh() {
    pre(func_get_args());
    return true;
}
set_error_handler('eh');

function preCallback() {
    print_r(func_get_args());
}

function pre($v, $title=false) {
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
\Art\Configuration::getInstance()->adapter->map->loader->xml->file = '../Test/parts/map.xml';

$form = array(
    new \Art\Form('a'),
    new \Art\Form('b'),
    new \Art\Form('c'),
    new \Art\Form('d'),
    new \Art\Form('e')
);


$form[0]->add($form[1]);
$form[1]->add($form[2], '2f');
$form[2]->add($form[3], '3f');
$form[3]->add($form[4], '4f');
$test = $form[4]->add(new \Art\Form\Component\Input());
$test2 = $form[4]->add(new \Art\Form\Component\Input(), '2i');
$form[0]->resetSession();

$post = array(
    'a' => array(
        '0' => array(
            '2f' => array(
                '3f' => array(
                    '4f' => array(
                        '0' => 'test',
                        '2i' => 'test2',
                    )
                )
            )
        )
    )
);
if ($form[0]->receive($post) && !$form[0]->error()) {
    pre($test->getValue(), 'VALUE');
    pre($test2->getValue(), 'VALUE');
}
echo $form[0];
pre($form[0]->getSession()->toArray(),'session');
pre('FINI');
pre($test->getHTMLAttributes());
pre($form[0]);
pre($_REQUEST);

/*$person=new Object('person');
$form=$person->getForm('FROM person p,parent c,hasEmail e');

if($form->receive() && !$form->error()){
    //pre($person->toArray());
}
    echo $form;*/