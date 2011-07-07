<?php
class AdapterMapExtractorInstancer extends \Art\Adapter\Map\Extractor\Database{
    public static function getInstance(){
        return new parent();
    }
}
class AdapterMapExtractorTest extends PHPUnit_Framework_TestCase {

    public function test_create() {
        $instance = AdapterMapExtractorInstancer::getInstance();
        $instance->create(array());
    }
}