<?php
class AAdapterDatabaseRowsMysql extends PHPUnit_Framework_TestCase {

    public function setUp() {
        Bootstrap::loadSQLDatabase();
    }

    function test_() {
        $rows = ArtAdapterDatabaseRowsMysqliInstancer::getInstance();
        $l = new \mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME);
        if (mysqli_errno($l))
            throw new Exception('error : ' . mysqli_error($l));
        $query = mysqli_query($l, 'SELECT * FROM employee');
        $rows->setResource($query, $query->num_rows);

        $this->assertEquals($rows->count(), $query->num_rows);
        $wentInTheLoop = 0;
        foreach ($rows as $k => $row) {
            $wentInTheLoop++;
            if ($wentInTheLoop == 1) {
                $this->assertEquals($row[0], 1);
                $this->assertEquals($row[1], 'jb');
            }
            if ($wentInTheLoop == 2) {
                $this->assertEquals($row[0], 2);
                $this->assertEquals($row[1], 'robert');
            }
        }
        $this->assertEquals($wentInTheLoop, 2);
        $this->assertEquals($rows->toArray(), array(array('1', 'jb','1'), array('2', 'robert', '')));
    }
}