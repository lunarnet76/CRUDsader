<?php
class AdapterMapLoaderXmlInstancer extends CRUDsader\Adapter\Map\Loader\Xml {
    static $_instance;

    public static function getInstance($var=null) {
        if (!isset(self::$_instance))
            self::$_instance = new parent($var);
        return self::$_instance;
    }
}
class AdapterMapLoaderXml_Test extends PHPUnit_Framework_TestCase {

    function setUp() {

        \CRUDsader\Configuration::getInstance()->adapter->map->loader->xml->file = 'Parts/orm.xml';
    }

    function tearDown() {

        \CRUDsader\Configuration::getInstance()->adapter->map->loader->xml->file = 'orm.xml';
    }

    function test_() {
        $instance = AdapterMapLoaderXmlInstancer::getInstance(\CRUDsader\Configuration::getInstance()->adapter->map->loader->xml);
        $this->assertEquals($instance->getSchema(\CRUDsader\Configuration::getInstance()->map->defaults), array(
            'attributeTypes' =>
            array(
                'default' =>
                array(
                    'length' => 32,
                    'class' => 'String',
                    'phpClass' => '\\CRUDsader\\Object\\Attribute\\Wrapper\\',
                    'databaseType' => "VARCHAR",
                    'options' => array(
                        'length' => 32
                    ),
                ),
                'email' =>
                array(
                    'length' => 255,
                    'class' => "Email",
                    'phpClass' => "\CRUDsader\Object\Attribute\Wrapper\\",
                    'databaseType' => "VARCHAR",
                    'options' => array(
                        'length' => 255
                    )
                ),
                'date' =>
                array(
                    'length' => 0,
                    'class' => 'Date',
                    'phpClass' => "\CRUDsader\Object\Attribute\Wrapper\\",
                    'databaseType' => "DATE",
                    'options' =>
                    array(
                        'length' => 0
                    )
                )
            ),
            'classes' =>
            array(
                'contact' =>
                array(
                    'definition' =>
                    array(
                        'databaseTable' => "Tcontact",
                        'identity' =>
                        array(
                            "name"
                        ),
                        'databaseIdField' =>
                        "PKcontactid",
                        'attributeCount' =>
                        array(
                            'id' =>
                            false,
                            'name' =>
                            false,
                            'FKgroup' =>
                            true
                        ),
                    ),
                    'inherit' =>
                    false,
                    'attributes' =>
                    array(
                        'name' =>
                        array(
                            'required' =>
                            true,
                            'default' =>
                            false,
                            'databaseField' => "Fname",
                            'type' =>
                            "default",
                            'searchable' =>
                            true,
                            'calculated' =>
                            false
                        ),
                    ),
                    'attributesReversed' =>
                    array(
                        'Fname' =>
                        "name"
                    ),
                    'associations' =>
                    array(
                        'hasGroup' =>
                        array(
                            'to' =>
                            "group",
                            'reference' =>
                            "internal",
                            'name' =>
                            "hasGroup",
                            'min' =>
                            0,
                            'max' =>
                            1,
                            'composition' =>
                            true,
                            'databaseTable' =>
                            "hasGroup",
                            'internalField' =>
                            "FKgroup",
                            'externalField' =>
                            "group"
                        ),
                        'hasEmail' =>
                        array(
                            'to' =>
                            "email",
                            'reference' =>
                            "external",
                            'name' =>
                            "hasEmail",
                            'min' =>
                            0,
                            'max' =>
                            0,
                            'composition' =>
                            true,
                            'databaseTable' =>
                            "hasEmail",
                            'internalField' =>
                            "contact",
                            'externalField' =>
                            "FKcontact"
                        ),
                        'hasAddress' =>
                        array(
                            'to' =>
                            "address",
                            'reference' =>
                            "external",
                            'name' =>
                            "hasAddress",
                            'min' =>
                            0,
                            'max' =>
                            1,
                            'composition' =>
                            true,
                            'databaseTable' =>
                            "hasAddress",
                            'internalField' =>
                            "contact",
                            'externalField' =>
                            "FKcontact"
                        ),
                        'hasWebSite' =>
                        array(
                            'to' =>
                            "webSite",
                            'reference' =>
                            "table",
                            'name' =>
                            "hasWebSite",
                            'min' =>
                            0,
                            'max' =>
                            0,
                            'composition' =>
                            false,
                            'databaseTable' =>
                            "C2Wb",
                            'internalField' =>
                            "FK2contact",
                            'externalField' =>
                            "FK2website"
                        ),
                    ),
                ),
                'person' =>
                array(
                    'definition' =>
                    array(
                        'databaseTable' =>
                        "Tperson",
                        'identity' =>
                        array(
                        ),
                        'databaseIdField' =>
                        "PKpersonid",
                        'attributeCount' =>
                        array(
                            'id' =>
                            false,
                            'title' =>
                            false
                        ),
                    ),
                    'inherit' =>
                    "contact",
                    'attributes' =>
                    array(
                        'title' =>
                        array(
                            'required' =>
                            true,
                            'default' =>
                            false,
                            'databaseField' =>
                            "Ftitle",
                            'type' =>
                            "default",
                            'searchable' =>
                            true,
                            'calculated' =>
                            false
                        ),
                    ),
                    'attributesReversed' =>
                    array(
                        'Ftitle' =>
                        "title",
                    ),
                    'associations' =>
                    array(
                    ),
                ),
                'group' =>
                array(
                    'definition' =>
                    array(
                        'databaseTable' =>
                        "group",
                        'identity' =>
                        array(
                        ),
                        'databaseIdField' =>
                        "id",
                        'attributeCount' =>
                        array(
                            'id' =>
                            false,
                            'name' =>
                            false
                        ),
                    ),
                    'inherit' =>
                    false,
                    'attributes' =>
                    array(
                        'name' =>
                        array(
                            'required' =>
                            false,
                            'default' =>
                            false,
                            'databaseField' =>
                            "Fname",
                            'type' =>
                            "default",
                            'searchable' =>
                            true,
                            'calculated' =>
                            false
                        ),
                    ),
                    'attributesReversed' =>
                    array(
                        'Fname' =>
                        "name",
                    ),
                    'associations' =>
                    array(
                    ),
                ),
                'email' =>
                array(
                    'definition' =>
                    array(
                        'databaseTable' =>
                        "email",
                        'identity' =>
                        array(
                        ),
                        'databaseIdField' =>
                        "id",
                        'attributeCount' =>
                        array(
                            'id' =>
                            false,
                            'address' =>
                            false,
                            'created' =>
                            false,
                            'FKcontact' =>
                            true,
                            'FKwebSite' =>
                            true
                        ),
                    ),
                    'inherit' =>
                    false,
                    'attributes' =>
                    array(
                        'address' =>
                        array(
                            'required' =>
                            false,
                            'default' =>
                            false,
                            'databaseField' =>
                            "Faddress",
                            'type' =>
                            "default",
                            'searchable' =>
                            true,
                            'calculated' =>
                            false
                        ),
                        'created' =>
                        array(
                            'required' =>
                            false,
                            'default' =>
                            false,
                            'databaseField' =>
                            "Fwhen",
                            'type' =>
                            "date",
                            'searchable' =>
                            true,
                            'calculated' =>
                            false
                        ),
                    ),
                    'attributesReversed' =>
                    array(
                        'Faddress' =>
                        "address",
                        'Fwhen' =>
                        "created"
                    ),
                    'associations' =>
                    array(
                        'webSite' =>
                        array(
                            'to' =>
                            "webSite",
                            'reference' =>
                            "internal",
                            'name' =>
                            false,
                            'min' =>
                            0,
                            'max' =>
                            1,
                            'composition' =>
                            false,
                            'databaseTable' =>
                            "email2webSite",
                            'internalField' =>
                            "FKwebSite",
                            'externalField' =>
                            "webSite"
                        ),
                    ),
                ),
                'address' =>
                array(
                    'definition' =>
                    array(
                        'databaseTable' =>
                        "address",
                        'identity' =>
                        array(
                        ),
                        'databaseIdField' =>
                        "id",
                        'attributeCount' =>
                        array(
                            'id' =>
                            false,
                            'street' =>
                            false,
                            'streetNumber' =>
                            false,
                            'city' =>
                            false,
                            'country' =>
                            false,
                            'FKcontact' =>
                            true
                        ),
                    ),
                    'inherit' =>
                    false,
                    'attributes' =>
                    array(
                        'street' =>
                        array(
                            'required' =>
                            false,
                            'default' =>
                            false,
                            'databaseField' =>
                            "Fstreet",
                            'type' =>
                            "default",
                            'searchable' =>
                            true,
                            'calculated' =>
                            false
                        ),
                        'streetNumber' =>
                        array(
                            'required' =>
                            false,
                            'default' =>
                            false,
                            'databaseField' =>
                            "FstreetNumber",
                            'type' =>
                            "default",
                            'searchable' =>
                            true,
                            'calculated' =>
                            false
                        ),
                        'city' =>
                        array(
                            'required' =>
                            false,
                            'default' =>
                            false,
                            'databaseField' =>
                            "Fcity",
                            'type' =>
                            "default",
                            'searchable' =>
                            true,
                            'calculated' =>
                            false
                        ),
                        'country' =>
                        array(
                            'required' =>
                            false,
                            'default' =>
                            false,
                            'databaseField' =>
                            "Fcountry",
                            'type' =>
                            "default",
                            'searchable' =>
                            true,
                            'calculated' =>
                            false
                        ),
                    ),
                    'attributesReversed' =>
                    array(
                        'Fstreet' =>
                        "street",
                        'FstreetNumber' =>
                        "streetNumber",
                        'Fcity' =>
                        "city",
                        'Fcountry' =>
                        "country"
                    ),
                    'associations' =>
                    array(
                    ),
                ),
                'webSite' =>
                array(
                    'definition' =>
                    array(
                        'databaseTable' =>
                        "webSite",
                        'identity' =>
                        array(
                        ),
                        'databaseIdField' =>
                        "id",
                        'attributeCount' =>
                        array(
                            'id' =>
                            false,
                            'url' =>
                            false
                        ),
                    ),
                    'inherit' =>
                    "contact",
                    'attributes' =>
                    array(
                        'url' =>
                        array(
                            'required' =>
                            false,
                            'default' =>
                            false,
                            'databaseField' =>
                            "Furl",
                            'type' =>
                            "default",
                            'searchable' =>
                            true,
                            'calculated' =>
                            false
                        ),
                    ),
                    'attributesReversed' =>
                    array(
                        'Furl' =>
                        "url"
                    ),
                    'associations' =>
                    array(
                    )
                ),
            )
        ));
    }
}