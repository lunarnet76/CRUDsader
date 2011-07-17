<?php

\Art\Configuration::getInstance()->adapter->map->loader->xml->file=dirname(__FILE__) . '/parts/map.xml';

class MapTest extends PHPUnit_Framework_TestCase {

    function test_validateSchema_() {
        $map = \Art\Map::getInstance();
        $this-> assertEquals($map->validate(), true);
    }

    /*function test_class_() {
        $map = \Art\Map::getInstance();
        // print_r($map);
        $this-> assertEquals($map->classExists('contact'), true);
        $this-> assertEquals($map->classExists('alienVsPredator'), false);

        $this-> assertEquals($map->classGetDatabaseTable('contact'), 'Tcontact');
        $this-> assertEquals($map->classHasAssociation('contact', 'hasLogin'), true);
        $this-> assertEquals($map->classHasAssociation('company', 'buyFrom'), true);
        $this-> assertEquals($map->classHasAssociation('company', 'alienVsPredator'), false);
    }*/

    function test_classGetJoin_() {
        $map = \Art\Map::getInstance();
        $scenarios=array(
            '1'=>$map->classGetJoin('contact', 'hasLogin', 'c', 'l'),
            '2'=>$map->classGetJoin('contact', 'photo', 'c', 'p'),
            '3'=>$map->classGetJoin('contact', 'company', 'c', 'com'),
            '4'=>$map->classGetJoin('contact', 'hasAddress', 'c', 'a','ac'),
            '5'=>$map->classGetJoin('hasAddress', 'company', 'ha', 'c','ac'),
        );
        // 1 hasLogin
        $this-> assertEquals($scenarios['1'], array(
                    'table' => array(
                        'fromAlias' => 'c',
                        'fromColumn' => 'id',
                        'toAlias' => 'l',
                        'toColumn' => 'hasLogin',
                        'toTable' => 'login',
                        'type' => 'left'
                    )
                ));
        // 2 photo
        $this-> assertEquals($scenarios['2'], array(
                    'table' => array(
                        'fromAlias' => 'c',
                        'fromColumn' => 'id',
                        'toAlias' => 'p',
                        'toColumn' => 'contact',
                        'toTable' => 'photo',
                        'type' => 'left'
                    )
                ));
        // 3 company
        $this-> assertEquals($scenarios['3'], array(
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
        $this-> assertEquals($scenarios['4'], array(
                    'association' => array(
                        'fromAlias' => 'c',
                        'fromColumn' => 'id',
                        'toAlias' => 'ac',
                        'toColumn' => 'contact',
                        'toTable' => 'hasAddress',
                        'type' => 'left'
                    ),
                    'table' => array(
                        'fromAlias' => 'ac',
                        'fromColumn' => 'address',
                        'toAlias' => 'a',
                        'toColumn' => 'id',
                        'toTable' => 'address',
                        'type' => 'left'
                    )
                ));
        // 5 hasAddress => company
        /*$this-> assertEquals($scenarios['5'], array(
                    'association' => array(
                        'fromAlias' => 'c',
                        'fromColumn' => 'id',
                        'toAlias' => 'ac',
                        'toColumn' => 'contact',
                        'toTable' => 'hasAddress',
                        'type' => 'left'
                    ),
                    'table' => array(
                        'fromAlias' => 'ac',
                        'fromColumn' => 'address',
                        'toAlias' => 'a',
                        'toColumn' => 'id',
                        'toTable' => 'address',
                        'type' => 'left'
                    )
                ));*/
        /*$select= new \Art\Database\Select();
        echo \Art\Database::getInstance()->getDescriptor()->highLight(\Art\Database::getInstance()->getDescriptor()->select($select->from(array('table'=>'employee','alias'=>'e'))
                ->join($scenarios['5']['association'])
                ->join($scenarios['5']['table']))
                );*/
        
        $map->extract();
    }

}

