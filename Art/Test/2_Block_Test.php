<?php
class ArtBlockInstancer extends \Art\Block {

    public static function getInstance() {
        return new parent();
    }
}
class BlockTest extends PHPUnit_Framework_TestCase {

    function test_accessors() {
        $instance = ArtBlockInstancer::getInstance();
        $instance->p1 = 'v1';
        $this->assertEquals($instance->p1, 'v1');
        $this->assertEquals(isset($instance->p1), true);
        unset($instance->p1);
        $this->assertEquals(isset($instance->p1), false);
    }

    /**
     * @depends test_accessors
     * @expectedException \Art\BlockException
     */
    function test_lock_set() {
        $instance = ArtBlockInstancer::getInstance();
        $instance->p1 = 'v1';
        $instance->lock();
        $instance->p1 = 'v2';
    }

    /**
     * @depends test_accessors
     */
    function test_unlock_() {
        $instance = ArtBlockInstancer::getInstance();
        $instance->p1 = 'v1';
        $instance->lock();
        $exception = false;
        try {
            $instance->p1 = 'v2';
        } catch (Exception $e) {
            $exception = true;
        }
        $this->assertEquals($exception, true);
        $instance->unlock();
        $instance->p1 = 'v2';
        $this->assertEquals($instance->p1, 'v2');
    }

    /**
     * @depends test_accessors
     * @expectedException \Art\BlockException
     */
    function test_lock_get() {
        $instance = ArtBlockInstancer::getInstance();
        $instance->lock();
        $instance->loadArray(array());
    }

    /**
     * @depends test_accessors
     * @expectedException \Art\BlockException
     */
    function test_lock_unset() {
        $instance = ArtBlockInstancer::getInstance();
        $instance->p1 = 'v1';
        $instance->lock();
        unset($instance->p1);
    }

    /**
     * @depends test_accessors
     * @expectedException \Art\BlockException
     */
    function test_lock_reset() {
        $instance = ArtBlockInstancer::getInstance();
        $instance->lock();
        $instance->reset();
    }

    /**
     * @depends test_accessors
     */
    function test_reset() {
        $instance = ArtBlockInstancer::getInstance();
        $instance->p1 = 'v1';
        $instance->p2 = 'v2';
        $this->assertEquals($instance->count(), 2);
        $instance->reset();
        $this->assertEquals($instance->count(), 0);
    }

    /**
     * @depends test_accessors
     */
    function test_loadArray() {
        $instance = ArtBlockInstancer::getInstance();
        $instance->loadArray(array('p1' => 'v1', 'p2' => 'v2'));
        $this->assertEquals($instance->p1, 'v1');
        $this->assertEquals($instance->p2, 'v2');

        $instance->loadArray(array('p1' => 'v1', 'p2' => 'v2', 'p3' => array('p4' => 'v4', 'p5' => 'v5')));
        $this->assertEquals($instance->p3 instanceof \Art\Block, true);
        $this->assertEquals($instance->p3->p4, 'v4');

        $instance->p3->loadArray(array('p4' => 'NOTv4', 'p5' => 'NOTv5'), false);
        $this->assertEquals($instance->p3->p4, 'v4');
        $this->assertEquals($instance->p3->p5, 'v5');
    }

    /**
     * @depends test_accessors
     */
    function test_toArray() {
        $instance = ArtBlockInstancer::getInstance();
        $values = array('p1' => 'v1', 'p2' => 'v2', 'p3' => array('p4' => 'v4', 'p5' => 'v5'));
        $instance->loadArray($values);
        $this->assertEquals($instance->toArray(), $values);
    }
}
?>
