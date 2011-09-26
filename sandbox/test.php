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
\CRUDsader\Configuration::getInstance()->database->name = 'CRUDsader_test';
\CRUDsader\Configuration::getInstance()->debug->database->profiler=true ;
\CRUDsader\Configuration::getInstance()->adapter->map->loader->xml->file = '../Test/Parts/orm.xml';

try {
    $m = \CRUDsader\Map::getInstance();
    $m->extract();
} catch (Exception $e) {
    pre($e);
    exit;
}
mysql_connect('localhost', 'root', '');
mysql_select_db('CRUDsader_test');
mysql_query('SET FOREIGN_KEY_CHECKS = 0');
$sqlFile = explode(';', file_get_contents('../Test/Parts/databaseOrmRows.sql'));
foreach ($sqlFile as $sql) {
    $sql = trim($sql);
    if (!empty($sql)) {
        mysql_query($sql) or die($sql . mysql_error());
    }
}
mysql_query('SET FOREIGN_KEY_CHECKS = 1');
mysql_close();

class Model extends \CRUDsader\Object{
    
}

$oql = 'SELECT p.*,c.*
            FROM 
                person p,
                parent c,
                 
                 hasWebSite w2 ON p,hasEmail e, webSite w ON e,
                 hasGroup g, 
                 hasAddress a,
                 parent wp ON w2
                 WHERE
                    p.id=?
                    ORDER BY p.id ASC LIMIT 3';
/**/
$q = new \CRUDsader\Query($oql);

$r = $q->fetchAll(array(array('>' => new \CRUDsader\Expression('0'))));
$o = $r->findById(1);


// FORM
$form = $o->getForm();
try {
    if ($form->receiveInput() && $form->inputValid()) {
        $o->save();
        pre($o,'saved');
    }
} catch (Exception $e) {
    if ($e instanceof CRUDsader\Adapter\Database\Connector\MysqliException)
        pre($e->getSQL());
    $form->setInputError($e->getMessage());
}


echo $form;

$r = $q->fetchAll(array(array('>' => new \CRUDsader\Expression('0'))));
$o = $r->findById(1);

pre($o);

echo \CRUDsader\Database::getInstance()->getAdapter('profiler')->display(true);
//pre($o);

