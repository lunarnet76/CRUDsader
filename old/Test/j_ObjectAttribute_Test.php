<?php
require('parts/j/Wrapper.php');
class ObjectAttributeTest extends PHPUnit_Framework_TestCase {

    public function test_oid_() {
        $instance = new \Art\Object\Attribute('string', '\\Art\\Object\\Attribute\\Wrapper\\Test');

        $label = 'test_wrappers';
        $form = new \Art\Form($label);
        $c1 = $form->add($instance, 'comment1');
        $request = array(
            $label => array(
                'comment1' => 'value1',
            )
        );
        $intheloop = false;
        if ($form->receive($request) && !$form->error()) {
            $intheloop = true;
        }
        $this->assertEquals($intheloop, true);

        $request = array(
            $label => array(
                'comment1' => 'error'
            )
        );
        $intheloop = false;
        if ($form->receive($request) && !$form->error()) {
            $intheloop = true;
        }
        $this->assertEquals($intheloop, false);
    }
}