<?php
\Art\Configuration::getInstance()->adapter->map->loader->xml->file = dirname(__FILE__) . '/parts/map.xml';
class MapTest extends PHPUnit_Framework_TestCase {

    function test_validateSchema_() {
        $map = \Art\Map::getInstance();
        $this->assertEquals($map->validate(), true);
    }

    function test_class_() {
        $map = \Art\Map::getInstance();
        // print_r($map);
        $this->assertEquals($map->classExists('contact'), true);
        $this->assertEquals($map->classExists('alienVsPredator'), false);

        $this->assertEquals($map->classGetDatabaseTable('contact'), 'Tcontact');
        $this->assertEquals($map->classHasAssociation('contact', 'hasLogin'), true);
        $this->assertEquals($map->classHasAssociation('contact', 'photo'), true);
        $this->assertEquals($map->classHasAssociation('company', 'alienVsPredator'), false);
    }

    function test_classGetJoin_() {
        $map = \Art\Map::getInstance();
        $scenarios = array(
            '1' => $map->classGetJoin('contact', 'hasLogin', 'c', 'l'),
            '2' => $map->classGetJoin('contact', 'photo', 'c', 'p'),
            '3' => $map->classGetJoin('contact', 'company', 'c', 'com'),
            '4' => $map->classGetJoin('contact', 'hasAddress', 'c', 'a', 'a2ha'),
            '5' => $map->classGetJoin('contact', 'address', 'c', 'a', 'a2c'),
            '6' => $map->classGetJoin('address', 'contact', 'a', 'c', 'a2c')
        );
        // 1 hasLogin
        $this->assertEquals($scenarios['1'], array(
            'table' => array(
                'fromAlias' => 'c',
                'fromColumn' => 'contact_id',
                'toAlias' => 'l',
                'toColumn' => 'hasLogin',
                'toTable' => 'login',
                'type' => 'left'
            )
        ));
        // 2 photo
        $this->assertEquals($scenarios['2'], array(
            'table' => array(
                'fromAlias' => 'c',
                'fromColumn' => 'contact_id',
                'toAlias' => 'p',
                'toColumn' => 'contact',
                'toTable' => 'photo',
                'type' => 'left'
            )
        ));
        // 3 company
        $this->assertEquals($scenarios['3'], array(
            'table' => array(
                'fromAlias' => 'c',
                'fromColumn' => 'company',
                'toAlias' => 'com',
                'toColumn' => 'id',
                'toTable' => 'company',
                'type' => 'left'
            )
        ));
        // 4 hasAddress
        $this->assertEquals($scenarios['4'], array(
            'association' => array(
                'fromAlias' => 'c',
                'fromColumn' => 'contact_id',
                'toAlias' => 'a2ha',
                'toColumn' => 'contact',
                'toTable' => 'hasAddress',
                'type' => 'left'
            ),
            'table' => array(
                'fromAlias' => 'a2ha',
                'fromColumn' => 'address',
                'toAlias' => 'a',
                'toColumn' => 'id',
                'toTable' => 'address',
                'type' => 'left'
            )
        ));
        //  address
        $this->assertEquals($scenarios['5'], array(
            'association' => array(
                'fromAlias' => 'c',
                'fromColumn' => 'contact_id',
                'toAlias' => 'a2c',
                'toColumn' => 'contact',
                'toTable' => 'address2contact',
                'type' => 'left'
            ),
            'table' => array(
                'fromAlias' => 'a2c',
                'fromColumn' => 'address',
                'toAlias' => 'a',
                'toColumn' => 'id',
                'toTable' => 'address',
                'type' => 'left'
            )
        ));
        // reverse : address 2 contact
        $this->assertEquals($scenarios['6'], array(
            'association' => array(
                'fromAlias' => 'a',
                'fromColumn' => 'id',
                'toAlias' => 'a2c',
                'toColumn' => 'address',
                'toTable' => 'address2contact',
                'type' => 'left'
            ),
            'table' => array(
                'fromAlias' => 'a2c',
                'fromColumn' => 'contact',
                'toAlias' => 'c',
                'toColumn' => 'contact_id',
                'toTable' => 'Tcontact',
                'type' => 'left'
            )
        ));

        $map->extract();

        $db = \Art\Database::getInstance();
        foreach ($scenarios as $i => $sc) {
            $select = new \Art\Database\Select();
            if ($i == 6)
                $select->from(array('table' => 'address', 'alias' => 'a'));
            else
                $select->from(array('table' => 'Tcontact', 'alias' => 'c'));
            if (isset($sc['association']))
                $select->join($sc['association']);
            $select->join($sc['table']);
            $sql = \Art\Database::getInstance()->getDescriptor()->highLight(\Art\Database::getInstance()->getDescriptor()->select($select));

            ($db->select($select));
        }

        //$db->getProfiler()->display();
    }
}
