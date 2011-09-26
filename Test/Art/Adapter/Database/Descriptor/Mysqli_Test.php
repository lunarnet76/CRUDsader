<?php
class AdapterDatabaseDescriptorMysqli_Test extends PHPUnit_Framework_TestCase {

    function setUp() {
        Bootstrap::setUpORMDatabase();
    }


    function test_quoteIdentifier_() {
        $instance = AdapterDatabaseDescriptorMysqliInstancer::getInstance();
        $this->assertEquals($instance->quoteIdentifier('test'), '`test`');
    }

    function test_quote_() {
        $instanceConnector = AdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $instanceConnector->connect();
        $instance = AdapterDatabaseDescriptorMysqliInstancer::getInstance();
        $instance->setConnector($instanceConnector);

        $this->assertEquals($instance->quote('test'), '"test"');
        $this->assertEquals($instance->quote(new \Art\Expression('test')), 'test');
    }

    function test_insert_() {
        $instanceConnector = AdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $instanceConnector->connect();
        $instance = AdapterDatabaseDescriptorMysqliInstancer::getInstance();
        $instance->setConnector($instanceConnector);

        $sql = 'INSERT INTO `table`(`field1`,`field2`,`field3`) VALUES ("value1","v2","this is \"value3\"")';
        $this->assertEquals($instance->insert('table', array('field1' => 'value1', 'field2' => 'v2', 'field3' => 'this is "value3"')), $sql);
    }

    function test_update_() {
        $instanceConnector = AdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $instanceConnector->connect();
        $instance = AdapterDatabaseDescriptorMysqliInstancer::getInstance();
        $instance->setConnector($instanceConnector);

        $sql = 'UPDATE `table` SET `field1`="value1",`field2`="v2",`field3`="this is \"value3\"" WHERE `id`=15';
        $this->assertEquals($instance->update('table', array('field1' => 'value1', 'field2' => 'v2', 'field3' => 'this is "value3"'), '`id`=15'), $sql);
    }

    function test_delete_() {
        $instanceConnector = AdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $instanceConnector->connect();
        $instance = AdapterDatabaseDescriptorMysqliInstancer::getInstance();
        $instance->setConnector($instanceConnector);

        $sql = 'DELETE FROM `table` WHERE `id`=15';
        $this->assertEquals($instance->delete('table', '`id`=15'), $sql);
    }

    function test_listTables_() {
        $instanceConnector = AdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $instanceConnector->connect();
        $instance = AdapterDatabaseDescriptorMysqliInstancer::getInstance();
        $instance->setConnector($instanceConnector);

        $sql = 'SHOW TABLES';
        $this->assertEquals($instance->listTables(), $sql);
    }

    function test_deleteTable_() {
        $instanceConnector = AdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $instanceConnector->connect();
        $instance = AdapterDatabaseDescriptorMysqliInstancer::getInstance();
        $instance->setConnector($instanceConnector);

        $sql = 'DROP TABLE `table`';
        $this->assertEquals($instance->deleteTable('table'), $sql);
    }
    
    function test_createTable_(){
        $instanceConnector=AdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $instanceConnector->connect();
        $instance=AdapterDatabaseDescriptorMysqliInstancer::getInstance();
        $instance->setConnector($instanceConnector);
        
        $sql='CREATE TABLE `table`(`id` BIGINT(20) NOT NULL AUTO_INCREMENT,`field1` VARCHAR(32) NOT NULL ,UNIQUE KEY `unicity`(`field1`),PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin';
        $this->assertEquals($instance->createTable(
                'table',
                 /*array $fields=*/array(
                     'field1'=>array(
                         'type'=>'VARCHAR',
                         'length'=>'32',
                         'null'=>false
                     )
                 ), 
                /*array $identity=*/array(
                    'field1'
                ), 
                /*array $surrogateKey=*/array(
                    'name'=>'id',
                    'type'=>'BIGINT',
                    'length'=>20
                ), 
                /*array $indexes=*/array(
                    // useless at the time
                )
        ),$sql);
    }
    
    function test_select_(){
        Bootstrap::setUpORMDatabase();
        $select=array(
            'from'=>array('table'=>'Tperson','alias'=>'p','id'=>'PKpersonid'),
            'fields'=>array(
                array('tableAlias'=>'p','field'=>'PKpersonid'),
                array('tableAlias'=>'c','field'=>'PKcontactid'),
                array('tableAlias'=>'c','field'=>'Fname'),
                array('tableAlias'=>'g','field'=>'*'),
                array('tableAlias'=>'a','field'=>'*'),
                array('tableAlias'=>'c2wb','field'=>'*'),
                array('tableAlias'=>'wb','field'=>'*'),
            ),
            'joins'=>array(
                array('table'=>'Tcontact','alias'=>'c','field'=>'PKcontactid','joinAlias'=>'p','joinField'=>'PKpersonid'),
                array('table'=>'group','alias'=>'g','field'=>'id','joinAlias'=>'c','joinField'=>'FKgroup'),
                array('table'=>'address','alias'=>'a','field'=>'FKcontact','joinAlias'=>'c','joinField'=>'PKcontactid'),
                array('table'=>'C2Wb','alias'=>'c2wb','field'=>'FK2contact','joinAlias'=>'c','joinField'=>'PKcontactid'),
                array('table'=>'webSite','alias'=>'wb','field'=>'id','joinAlias'=>'c2wb','joinField'=>'FK2webSite'),
                array('table'=>'email','alias'=>'em','field'=>'FKcontact','joinAlias'=>'c','joinField'=>'PKcontactid'),
                array('table'=>'webSite','alias'=>'emWb','field'=>'id','joinAlias'=>'em','joinField'=>'FKwebSite'),
            ),
            'where'=>'c.PKcontactid>0 AND p.PKpersonid>0',
            'order'=>'`wb`.`Furl` DESC',
            'limit'=>array(
                'count'=>3,
                'from'=>0
            )
        );  
        $instanceConnector=AdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $instance=AdapterDatabaseDescriptorMysqliInstancer::getInstance();
        $instance->setConnector($instanceConnector);
        $sql=$instance->select($select,array(0,0));
        //echo  (string)$sql;
        $this->assertEquals((string)$sql,'SELECT `p___sq`.`PKpersonid`,`c___sq`.`PKcontactid`,`c___sq`.`Fname`,`g___sq`.*,`a___sq`.*,`c2wb___sq`.*,`wb___sq`.* FROM (SELECT `p`.`PKpersonid` AS `distinctId` FROM `Tperson` AS `p` LEFT JOIN `Tcontact` AS `c` ON `c`.`PKcontactid`=`p`.`PKpersonid` LEFT JOIN `group` AS `g` ON `g`.`id`=`c`.`FKgroup` LEFT JOIN `address` AS `a` ON `a`.`FKcontact`=`c`.`PKcontactid` LEFT JOIN `C2Wb` AS `c2wb` ON `c2wb`.`FK2contact`=`c`.`PKcontactid` LEFT JOIN `webSite` AS `wb` ON `wb`.`id`=`c2wb`.`FK2webSite` LEFT JOIN `email` AS `em` ON `em`.`FKcontact`=`c`.`PKcontactid` LEFT JOIN `webSite` AS `emWb` ON `emWb`.`id`=`em`.`FKwebSite` WHERE c.PKcontactid>0 AND p.PKpersonid>0 GROUP BY `p`.`PKpersonid` ORDER BY `wb`.`Furl` DESC LIMIT 0,3) AS `object` LEFT JOIN `Tperson` AS `p___sq` ON `object`.`distinctId`=p___sq.`PKpersonid` LEFT JOIN `Tcontact` AS `c___sq` ON `c___sq`.`PKcontactid`=`p___sq`.`PKpersonid` LEFT JOIN `group` AS `g___sq` ON `g___sq`.`id`=`c___sq`.`FKgroup` LEFT JOIN `address` AS `a___sq` ON `a___sq`.`FKcontact`=`c___sq`.`PKcontactid` LEFT JOIN `C2Wb` AS `c2wb___sq` ON `c2wb___sq`.`FK2contact`=`c___sq`.`PKcontactid` LEFT JOIN `webSite` AS `wb___sq` ON `wb___sq`.`id`=`c2wb___sq`.`FK2webSite` LEFT JOIN `email` AS `em___sq` ON `em___sq`.`FKcontact`=`c___sq`.`PKcontactid` LEFT JOIN `webSite` AS `emWb___sq` ON `emWb___sq`.`id`=`em___sq`.`FKwebSite`');
        $instanceConnector->disconnect();
        $instanceConnector->connect(true);
        $q=$instanceConnector->query($sql,'select');
        $this->assertEquals($q->count(),8);
    }
    
    
    function tearDown() {
        Bootstrap::tearDownDatabase();
    }
}