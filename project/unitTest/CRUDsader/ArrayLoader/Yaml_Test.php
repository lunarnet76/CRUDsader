<?php
class YamlMock extends \CRUDsader\ArrayLoader\Yaml{
    
}
class ArrayLoader_Yaml_Test extends PHPUnit_Framework_TestCase {
    public function test_load(){
        $y = new YamlMock;
        $ret = $y->load(array('file'=>'Parts/Mock/ArrayLoader/Yaml1.php'));
        
        $this->assertEquals($ret['default'],array(
            'test'=>array(
                'test1'=>array(
                    'test2'=>array(
                        'test3'=>array(
                            'alpha'=>'v1',
                            'bravo'=>'v2',
                            'crazy'=>array(
                                'a'=>'v3',
                                'b'=>'v4',
                                'c'=>'v5'
                            )
                        ),
                        'test4'=>array(
                            'a'=>'v6',
                            'c'=>'7'
                         )
                    )
                ),
                'test1-1'=>array(
                    'a'=>'v8',
                    'b'=>'9'
                ),
            ),
            'test-1'=>array(
                'c'=>'10',
                'd'=>'v11'
            )
        ));
        $this->assertEquals($ret['dev'],array(
            'test'=>array(
                'test1'=>array(
                    'test2'=>array(
                        'test3'=>array(
                            'alpha'=>'hello',
                            'bravo'=>'v2',
                            'crazy'=>'world'
                        ),
                        'test4'=>array(
                            'a'=>'v6',
                            'c'=>'7'
                         )
                    )
                ),
                'test1-1'=>array(
                    'a'=>'v8',
                    'b'=>'9'
                ),
            ),
            'test-1'=>array(
                'c'=>'10',
                'd'=>'v11'
            )
        ));
        
        $this->assertEquals($ret['default'],$ret['same']);
	
	$ret = $y->load(array('file'=>'Parts/Mock/ArrayLoader/Yaml2.php'));
    }
}
