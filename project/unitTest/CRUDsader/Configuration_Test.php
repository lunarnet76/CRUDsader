<?php
use \CRUDsader as c;

class Configuration1Mock extends \CRUDsader\Configuration {
    public static function getDefaults() {
        return parent::$_defaults;
    }
}
class ConfigurationEmptyMock extends \CRUDsader\Configuration {
    public function __construct() {
    }
}

class Configuration_Test extends PHPUnit_Framework_TestCase {
    
    function test_instance_defaults() {
        $instance = new c\configuration;
        $this->assertEquals($instance->toArray(), Configuration1Mock::getDefaults());
    }
    
    function test_load(){
        $instance = new ConfigurationEmptyMock;
        $instance->load(array('file'=>'Parts/Mock/Configuration/configuration.ini','section'=>false));
        $this->assertEquals($instance->namespace1->a->b->c->d,1);
        $this->assertEquals($instance->namespace1->a->b->e,2);
        $this->assertEquals($instance->namespace1->a->b->f,3);
        $this->assertEquals($instance->namespace1->g->h,4);
        $this->assertEquals($instance->namespace1->g->i,5);
        
        $this->assertEquals($instance->namespace2->a,'l');
        $this->assertEquals($instance->namespace2->b->b->c->g,6);
        $this->assertEquals($instance->namespace2->b->b->c->{7},'');
        $this->assertEquals($instance->namespace2->b->b->c->{8},'');
        
        $this->assertEquals($instance->namespace3->m->n->o->p->q,'      6 aand cowejf owejfo wejog wejog jwe END');
        
        $instance = new ConfigurationEmptyMock;
        $instance->load(array('file'=>'Parts/Mock/Configuration/configuration.ini','section'=>'namespace1'));
        $this->assertEquals($instance->a->b->c->d,1);
        $this->assertEquals($instance->a->b->e,2);
        $this->assertEquals($instance->a->b->f,3);
        $this->assertEquals($instance->g->h,4);
        $this->assertEquals($instance->g->i,5);
        

    }
}
