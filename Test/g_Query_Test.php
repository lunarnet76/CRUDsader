<?php
class QueryTest extends PHPUnit_Framework_TestCase {

    function setUp() {
        Bootstrap::loadSQLORMDatabase();
    }

    /*function test___construct_() {
        $q=new \Art\Query('
            SELECT * 
            FROM contact, 
                hasEmail,
                hasWebSite');
        $q->execute();
    }*/
    
    function test___construct_() {
        $q=new \Art\Query('SELECT p.*,c.*
            FROM 
                person p,
                parent c,
                 hasEmail e, webSite w ON e,
                 hasGroup g, 
                 hasAddress a,
                 hasWebSite w2 ON p,
                 parent wp ON w2');
        $r=$q->execute(array(1,"http://website1"));
        pre($r->toArray(true));
        
    }
    //*/

    function test_execute_ExceptionClassDoesNotExist() {
        $exception = false;
        try {
            $aq = new \Art\Query('FROM unexistant');
            $aq->execute();
        } catch (\Art\QueryException $e) {
            $this->assertEquals($e->getMessage(), 'error in FROM : class "unexistant" does not exist');
            $exception = true;
        }
        $this->assertEquals($exception, true);
    }

    function test_validateSyntax_() {
        $q = array(
            'FROM contact',
            'FROM contact c',
            'SELECT * FROM contact c',
            'SELECT c.* FROM contact c',
            'SELECT * FROM contact',
            'SELECT * FROM contact , parent',
            'SELECT * FROM contact , parent e',
            'SELECT * FROM contact c , parent e',
            'SELECT * FROM contact c , parent e ON c',
            'SELECT * FROM contact c , parent e  ON  c    , hasEmail he ON c , webSite w ON he',
            'FROM contact c WHERE c.id=?',
            'FROM contact c WHERE (c.id=? AND c.firstName=?) OR c.lastName=?',
            'FROM contact c WHERE (c.id=? AND (c.osef=? OR c.firstName=?)) OR c.lastName=?',
            'FROM contact c WHERE (c.id=? AND (c.osef=? OR c.firstName=?)) OR c.lastName=? ORDER BY c.lastName',
            'FROM contact c WHERE (c.id=? AND (c.osef=? OR c.firstName=?)) OR c.lastName=? ORDER BY c.lastName,c.id , c.osef',
            'FROM contact c WHERE (c.id=? AND (c.osef=? OR c.firstName=?)) OR c.lastName=? ORDER BY c.lastName,c.id , c.osef LIMIT 1',
            'FROM contact c WHERE (c.id=? AND (c.osef=? OR c.firstName=?)) OR c.lastName=? ORDER BY c.lastName,c.id , c.osef LIMIT 1 , 10',
            'FROM contact c WHERE (c.id=? AND (c.osef=? OR c.firstName=?)) OR c.lastName=? ORDER BY c.lastName , c.id , c.osef LIMIT 1 , 10 ',
        );
        foreach ($q as $oql) {
            $aq = new \Art\Query($oql);
            $this->assertEquals($aq->validateSyntax(), true);
        }
    }
}