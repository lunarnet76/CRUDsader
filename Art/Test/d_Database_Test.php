<?php
\Art\Configuration::getInstance()->database = Database_Config::$configuration;

class DatabaseTest extends PHPUnit_Framework_TestCase {

    public function test_adapter_() {
        $db = \Art\Database::getInstance();
        $this->assertEquals($db->getConnector() instanceof \Art\Adapter\Database\Connector, true);
        $this->assertEquals($db->getDescriptor() instanceof \Art\Adapter\Database\Descriptor, true);
        if ($db->hasProfiler())
            $this->assertEquals($db->getProfiler() instanceof \Art\Adapter\Database\Profiler, true);
    }

    public function test_quote_() {
        $db = \Art\Database::getInstance();
        $this->assertEquals($db->quote('test') === 'test', false);
        $this->assertEquals($db->quoteIdentifier('test') === 'test', false);
    }
    
    public function test_query_(){
        $db = \Art\Database::getInstance();
        $rows=$db->query('SELECT * FROM employee','select');
        $this->assertEquals($rows instanceof \Art\Adapter\Database\Rows,true);
        $this->assertEquals($rows->count(),2);
        $wentInTheLoop=0;
         foreach($rows as $k=>$row){
             $wentInTheLoop++;
             if($wentInTheLoop==1){
                 $this->assertEquals($row['id'],1);
                 $this->assertEquals($row['name'],'jb');
             }
             if($wentInTheLoop==2){
                 $this->assertEquals($row['id'],2);
                 $this->assertEquals($row['name'],'robert');
             }
         }
         $this->assertEquals($wentInTheLoop,2);
         $this->assertEquals($rows->toArray(),array(array('id'=>'1','name'=>'jb','login'=>'1'),array('id'=>'2','name'=>'robert','login'=>'')));
    }
    
     public function test_queryStatment_(){
        $db = \Art\Database::getInstance();
        $db->prepareQueryStatement('SELECT * FROM employee WHERE id=?','select');
        $rows=$db->executeQueryStatement(array('1'));
        $this->assertEquals(is_array($rows),true);
        $this->assertEquals(count($rows),1);
        $this->assertEquals($rows[0]['id'],1);
        $this->assertEquals($rows[0]['name'],'jb');
        $this->assertEquals($rows[0]['login'],1);
        
        $db->prepareQueryStatement('INSERT INTO employee(id,name) VALUES(?,?)','insert');
        $db->executeQueryStatement(array('3','bernard'));
        
        $db->prepareQueryStatement('DELETE FROM employee WHERE id=?','delete');
        $db->executeQueryStatement(array('3'));
    }
    
    public function test_transaction_(){
        $db = \Art\Database::getInstance();
        $db->beginTransaction();
        $rows=$db->query('INSERT INTO employee(id,name) VALUES(4,"mister4")','insert');
        $rows=$db->query('INSERT INTO employee(id,name) VALUES(5,"mister5")','insert');
        $db->rollback();
        
        $rows=$db->query('SELECT * FROM employee WHERE id=4 OR id=5','select');
        $this->assertEquals($rows->count(),0);
        
         $db->beginTransaction();
        $rows=$db->query('INSERT INTO employee(id,name) VALUES(4,"mister4")','insert');
        $rows=$db->query('INSERT INTO employee(id,name) VALUES(5,"mister5")','insert');
        $db->commit();
        
        $rows=$db->query('SELECT * FROM employee WHERE id=4 OR id=5','select');
        $this->assertEquals($rows->count(),2);
        
        $db->query('DELETE FROM employee WHERE id=4 OR id=5','delete');
    }
 
}