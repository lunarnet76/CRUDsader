<?php
$q = new \Art\Database\Select();
$q->from(array('alias' => 'e', 'table' => 'employee'))
        ->join(array('toTable' => 'login', 'fromAlias' => 'e', 'fromColumn' => 'login', 'toAlias' => 'l', 'toColumn' => 'id', 'type' => 'left'))
        ->where('e.id=? AND l.id=?')
        ->limit(array('results' => 20, 'offset' => 0))
        ->fields(array('tableAlias' => 'e', 'fieldAlias' => 'e_id', 'field' => 'id'))
        ->fields(array('tableAlias' => 'l', 'fieldAlias' => 'l_id', 'field' => 'id'))
        ->args(array(12, 27))
;
$_REQUEST['q'] = $q;

class MySQLDescriptor extends \Art\Adapter\Database\Descriptor\Mysqli {

    public static function getInstance($params=null) {
        return new \Art\Adapter\Database\Descriptor\Mysqli($params);
    }
}
class AAdapterDatabaseDescriptorMysql extends PHPUnit_Framework_TestCase {

    function test_() {
        $instance = ArtAdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $instance->connect();

        $descriptor = MySQLDescriptor::getInstance();
        $s = $descriptor->select($_REQUEST['q']);
        echo $descriptor->highLight($s, true);
    }

    public function test_createTable_(){
        $descriptor = MySQLDescriptor::getInstance();
        $this->assertEquals($descriptor->createTable(
            'table1',
            array(
                'column1' => array(
                    'type' => 'VARCHAR',
                    'length' => '32',
                    'null' => true
                )
            ),
            array('column1'),
            array('name'=>'id','type'=>'BIGINT','length'=>20),
            
            array(
                'name1'=>array('id','column1')
            )
        ),'CREATE TABLE `table1`(`column1` VARCHAR(32) NULL,`id` BIGINT(20) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`), KEY `unicity`(`column1`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin');
    }
}