<?php
class ArtAdapterDatabaseRowsMysqliInstancer extends \Art\Adapter\Database\Rows\Mysqli{
    public static function getInstance(){
        return new parent();
    }
}

class AAdapterDatabaseRowsMysql extends PHPUnit_Framework_TestCase {
     function test_(){
         $rows=ArtAdapterDatabaseRowsMysqliInstancer::getInstance();
         $l=new \mysqli(DATABASE_HOST,DATABASE_USER,DATABASE_PASSWORD,DATABASE_NAME);
         if(mysqli_errno($l))
            throw new Exception('error : '.  mysqli_error($l));
         $query=mysqli_query($l,'SELECT * FROM employee');
         
         $rows->setResource($query,  $query->num_rows);
         
         $this->assertEquals($rows->count(),$query->num_rows);
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
}