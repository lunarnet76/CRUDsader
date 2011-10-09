<?php
// auto class loading
require('../CRUDsader/Autoload.php');
\CRUDsader\Autoload::register();

// debug
function pre($v, $title=false) {
    \CRUDsader\Debug::pre($v, $title);
}
function table($var) {
    echo '<table style="border:1px thin grey" border=1>';
    $first = true;
    foreach ($var as $r) {
        if ($first) {
            echo '<tr style="font-weight:bold; background-color:grey;color:white">';
            foreach ($r as $k => $v) {
                echo '<td>' . $k . '</td>';
            }
            echo '</tr>';
            $first = false;
        }
        echo '<tr>';
        foreach ($r as $k => $v) {
            echo '<td>' . $v . '</td>';
        }
        echo '</tr>';
    }
    echo '<table>';
}

// load configuration
$configuration = \CRUDsader\Configuration::getInstance();
$configuration->loadArray(array(
    'database' => array(
        'name' => 'crudsader'
    ),
    'debug' => array(
        'database' => array(
            'profiler' => true
        )
    )
));

// define mapping schema, by default it will load orm.xml
$map = \CRUDsader\Map::getInstance();

// validate the map
$map->validate();

// reset database
\CRUDsader\Map::getInstance()->extract();



// for later
$db = \CRUDsader\Database::getInstance();
echo '<style type="text/css">b{color:#ae1414}h1{color:#666}</style>';
// 1 BASIC OBJECT
echo '<h1>line ' . __LINE__ . ' 1-Basic Object</h1>';
/*
  <class name="address">
  <attribute name="street"        />
  <attribute name="streetNumber"  />
  <attribute name="city"          />
  <attribute name="country"       />
  </class>
 */
$address = \CRUDsader\Object::instance('address');
$address->street = 'Pennsylvania Avenue';
$address->streetNumber = '1600';
$address->city = 'Washington';
$address->country = 'U.S.A.';
$address->save();

echo '<h3>line ' . __LINE__ . ' you have just registered a new address which is </h3><b>' . $address . '</b> saved in database with with the id <b>' . $address->getId() . '</b><br>';

// 2 BASIC ASSOCIATION BETWEEN OBJECTS
echo '<h1>line ' . __LINE__ . ' 2-Basic association between Objects</h1>';
/*
  <class name="contact">
  <attribute name="name" />
  <associated to="address"/>
  </class>
 */
$contact = \CRUDsader\Object::instance('contact');
$contact->name = 'Mr. President';
$contact->address[] = $address; // we give him the address
$contact->save();

echo '<h3>line ' . __LINE__ . ' you have just registered a new contact which is </h3><b>' . $contact . '</b> saved in database with the id <b>' . $contact->getId() . '</b><br>';
echo 'it has the address <b>' . $contact->address[0] . '</b><br/>';


// 3 SPECIFYING DATABASE fields AND tables
echo '<h1>line ' . __LINE__ . ' 3-SPECIFYING DATABASE fields AND tables</h1>';
/*
  <class name="vehicle" databaseTable="Table_Vehicle">
  <attribute name="model" databaseField="Field_Model" />
  </class>
 */
$vehicle = \CRUDsader\Object::instance('vehicle');
$vehicle->model = 'B52';
$vehicle->save();
echo '<h3>line ' . __LINE__ . ' you have just registered a new vehicle which is </h3><b>' . $vehicle . '</b> saved in database with the id <b>' . $contact->getId() . '</b><br>';
$vehicleQuery = $db->query('SELECT * FROM ' . $vehicle->getDatabaseTable() . ' WHERE ' . $vehicle->getDatabaseIdField() . '=' . $vehicle->getId(), 'select');
$vehicleTableFields = $vehicleQuery->getFields();
$vehicleValues = $vehicleQuery->current();
echo 'registered in table ' . $vehicle->getDatabaseTable() . ' with this information:<br/>';
foreach ($vehicleTableFields as $i => $fieldName) {
    echo $fieldName . ' : ' . $vehicleValues[$i] . '</br>';
}

// 4 INHERITANCE OF OBJECTS
echo '<h1>line ' . __LINE__ . ' 4-INHERITANCE OF OBJECTS</h1>';
/*
  <class name="person" inherit="contact">
  <attribute name="firstName"/>
  <attribute name="lastName"/>
  </class>
 */
$person = \CRUDsader\Object::instance('person');
$person->firstName = 'Barack';
$person->lastName = 'Obama';
// attribute of contact
$person->name = 'actual president';
$person->save();
echo '<h3>line ' . __LINE__ . ' you have just registered a new person which is </h3><b>' . $person . '</b> saved in database with the id <b>' . $person->getId() . '</b><br>';
echo 'it has been saved in two different tables: <b>' . $person->getDatabaseTable() . '</b> and <b>' . $person->getParent()->getDatabaseTable() . '</b> with the same id';

// 5 USING IDENTITY OF OBJECT
echo '<h1>line ' . __LINE__ . ' 5-USING IDENTITY OF OBJECT</h1>';
/*
  <class name="book" identity="isbn">
  <attribute name="isbn"/>
  <attribute name="title"/>
  </class>
 */
try {
    $book = \CRUDsader\Object::instance('book');
    $book->isbn = '978-0-240-52058-2';
    $book->title = 'How to cheat in Adobe Flash CS3';
    $book->save();
    echo '<h3>line ' . __LINE__ . ' you have just registered a new book which is </h3><b>' . $book . '</b> saved in database with the id <b>' . $book->getId() . '</b><br>';
} catch (Exception $e) {
    pre($e);
    $db->getAdapter('profiler')->display();
}

$book2 = \CRUDsader\Object::instance('book');
try {
    $book2->isbn = '978-0-240-52058-2';
    $book2->title = 'A pirate copy';
    $book2->save();
} catch (\CRUDsader\ObjectException $e) {
    if ($e->getMessage() == 'book_already_exists')
        echo '<h3>line ' . __LINE__ . ' you have tried (and fail) to register a new book which is </h3><b>' . $book2 . '</b> that has the same isbn than <b>' . $book . '</b><br>';
}

// 6 USING OBJECT CUSTOM PHP CLASS
echo '<h1>line ' . __LINE__ . '  6-USING OBJECT CUSTOM PHP CLASS</h1>';
/* * <class name="product" phpClass="ModelProduct">
  <attribute name="name" />
  <attribute name="price"/>
  <attribute name="vat" />
  </class>
 */
class ModelProduct extends \CRUDsader\Object {

    function getTotalPrice() {
        return $this->_('price') + (($this->_('price') / 100) * $this->_('vat'));
    }
}
$product = \CRUDsader\Object::instance('product');
$product->name = 'a book';
$product->price = '17.50';
$product->vat = '7.94';
echo '<h3>line ' . __LINE__ . ' you have created a product which is </h3><b>' . $product . '</b> that total price is  <b>' . $product->getTotalPrice() . '</b><br>';

// 7 DEFAULT field VALUE
echo '<h1>line ' . __LINE__ . '  7-DEFAULT field VALUE</h1>';
/**
  <class name="turtle">
  <attribute name="age" default="1"/>
  </class>
 */
$turtle = \CRUDsader\Object::instance('turtle');
echo '<h3>line ' . __LINE__ . ' you have created a turtle which is </h3><b>' . $turtle . '</b> with an age (by default) of  <b>' . $turtle->age . '</b><br>';


// 8 REQUIRED field
echo '<h1>line ' . __LINE__ . '  8-REQUIRED field</h1>';
/*
  <class name="horse">
  <attribute name="name" required="true"/>
  </class>
 */
$horse = \CRUDsader\Object::instance('horse');
try {
    $horse->save();
} catch (\CRUDsader\ObjectException $e) {
    if ($e->getMessage() == 'horse_fields_required')
        echo '<h3>line ' . __LINE__ . ' you have tried to save a horse </h3>but was missing the required field <b>name</b><br>';
}

// 9 CALCULATED field
echo '<h1>line ' . __LINE__ . '  9-CALCULATED field</h1>';
/*
  <class name="log">
  <attribute name="date" calculated="true"/>
  <attribute name="text" />
  </class>
 */
class ModelLog extends \CRUDsader\Object {

    function calculateAttribute($attributeName) {
        switch ($attributeName) {
            case 'date':
                return new \CRUDsader\Expression\Now;
                break;
            default:
                parent::calculateAttribute($attributeName);
        }
    }
}
$log = \CRUDsader\Object::instance('log');
$log->text = 'just a test';
$log->save();
echo '<h3>line ' . __LINE__ . ' you have just registered a log which is </h3><b>' . $log . '</b> with a date of <b>' . $log->date . '</b><br>';

// 10 FORM
echo '<h1>line ' . __LINE__ . '  10-Form</h1>';
$form = new \CRUDsader\Form('login');
$login = $form->add(new CRUDsader\Form\Component);
$password = $form->add(new CRUDsader\Form\Component\Password);
if ($form->inputReceive() && $form->inputValid()) {
    $log = \CRUDsader\Object::instance('log');
    $log->text = 'login ' . $login->getInputValue() . ',' . $password->getInputValue();
    $log->save();
    echo '<h3>line ' . __LINE__ . ' you have just registered a log from a form which is </h3><b>' . $log . '</b> with a date of <b>' . $log->date . '</b><br>';
}else
    echo $form;

// 11 AUTOMATIC FORM
echo '<h1>line ' . __LINE__ . '  11-AUTOMATIC Form</h1>';
$form = $log->getForm();
if ($form->inputReceive() && $form->inputValid()) {
    $log->save();
    echo '<h3>line ' . __LINE__ . ' you have just updated a log from a form which is </h3><b>' . $log . '</b> with a date of <b>' . $log->date . '</b><br>';
}else
    echo $form;

// 12 AUTOMATIC FORM with association
echo '<h1>line ' . __LINE__ . '  12-AUTOMATIC Form with association</h1>';
$form = $contact->getForm();
if ($form->inputReceive() && $form->inputValid()) {
    $contact->save();
    echo '<h3>line ' . __LINE__ . ' you have just updated a contact from a form which is </h3><b>' . $contact . '</b> <br>';
}else
    echo $form;

// 13 AUTOMATIC FORM specifying input
echo '<h1>line ' . __LINE__ . '  13-AUTOMATIC FORM specifying input</h1>';
$log2 = \CRUDsader\Object::instance('log2');
$form = $log2->getForm();
if ($form->inputReceive() && $form->inputValid()) {
    $log2->save();
    echo '<h3>line ' . __LINE__ . ' you have just updated a log from a form which is </h3><b>' . $log2 . '</b> <br>';
}else
    echo $form;

// 14 ATTRIBUTE TYPE base
echo '<h1>line ' . __LINE__ . '  14-ATTRIBUTE TYPE base</h1>';
/*
  <class name="booking">
  <attribute name="from" type="date1"/>
  <attribute name="to" type="date1" />
  </class>
  <attributeTypes>
  <attributeType alias="default" length="32"/>
  <attributeType alias="date1" class="date" databaseType="DATE" />
  </attributeTypes>
 */
$booking = \CRUDsader\Object::instance('booking');
$booking->from = new \CRUDsader\Expression('NOW()');
$booking->to = new \CRUDsader\Expression('NOW()');
$booking->save();
echo '<h3>line ' . __LINE__ . ' you have just saved a booking which is </h3><b>' . $booking . '</b> <br>';

$bookingQuery = $db->query('SELECT * FROM ' . $booking->getDatabaseTable() . ' WHERE ' . $booking->getDatabaseIdField() . '=' . $booking->getId(), 'select');
$bookingTableFields = $bookingQuery->getFields();
$bookingValues = $bookingQuery->current();
echo 'registered in table ' . $booking->getDatabaseTable() . ' with this information:<br/>';
foreach ($bookingTableFields as $i => $fieldName) {
    echo $fieldName . ' : ' . $bookingValues[$i] . '</br>';
}


// 15 ATTRIBUTE TYPE custom PHP class
/*
  <class name="holiday">
  <attribute name="from" type="date2"/>
  <attribute name="to" type="date2" />
  </class>
  <attributeTypes>
  <attributeType alias="date2" class="date" databaseType="DATE" phpClass="Attribute" />
  </attributeTypes>
 */
echo '<h1>line ' . __LINE__ . '  15-ATTRIBUTE TYPE custom PHP class</h1>';
class AttributeDate extends \CRUDsader\Object\Attribute {

    public function getValue() {
        return date('d/m/Y');
    }
}
$holiday = \CRUDsader\Object::instance('holiday');
$holiday->from = new \CRUDsader\Expression('NOW()');
echo '<h3>line ' . __LINE__ . ' you have just used an holiday starting the </h3><b>' . $holiday->from . '</b> <br>';


// 16 QUERY base
echo '<h1>line ' . __LINE__ . '  15-QUERY base</h1>';
/*
  <class name="userGroup">
  <attribute name="name"/>
  </class>
  <class name="userSubGroup" inherit="userGroup">
  <attribute name="subname"/>
  </class>
  <class name="userEmail">
  <attribute name="name"/>
  </class>
  <class name="userVehicle">
  <attribute name="name"/>
  </class>
  <class name="user">
  <attribute name="name"/>
  <associated to="userSubGroup" reference="internal"/>
  <associated to="userEmail" name="hasEmail" reference="external" composition="true"/>
  <associated to="userVehicle" reference="table" composition="true"/>
  </class>
 */
$user = \CRUDsader\Object::instance('user');
$user->name = 'batman';

$userSubGroup = \CRUDsader\Object::instance('userSubGroup');
$userSubGroup->name = 'superheroes';
$userSubGroup->subname = 'alliesOfJustice';

$email1 = \CRUDsader\Object::instance('userEmail');
$email1->name = 'batman@superhero.com';

$email2 = \CRUDsader\Object::instance('userEmail');
$email2->name = 'bruce.wayne@gmail.com';

$userVehicle1 = \CRUDsader\Object::instance('userVehicle');
$userVehicle1->name = 'batmobile';

$userVehicle2 = \CRUDsader\Object::instance('userVehicle');
$userVehicle2->name = 'batboat';

$user->userSubGroup[] = $userSubGroup;
$user->hasEmail[] = $email1;
$user->userVehicle[] = $userVehicle1;
$user->userVehicle[] = $userVehicle2;

$user->save();

$user2 = \CRUDsader\Object::instance('user');
$user2->name = 'robin';


$email1 = \CRUDsader\Object::instance('userEmail');
$email1->name = 'robin@superhero.com';

$email2 = \CRUDsader\Object::instance('userEmail');
$email2->name = 'whatever@gmail.com';


$user2->userSubGroup[] = $userSubGroup;// the same than batman;
$user2->hasEmail[] = $email1;
$user2->userVehicle[] = $userVehicle1;// the same than batman;

$user2->save();

echo '<h3>line ' . __LINE__ . ' you have just registered a user which is </h3>';
echo '<pre>';
print_r($user->toArray());
echo '</pre>';

echo '<h3>line ' . __LINE__ . ' getting users which OQL "From user", fetching only one object</h3>';
$query = new \CRUDsader\Query('FROM user');
$user = $query->fetch();
echo '<pre>'; 
print_r($user->toArray());
echo '</pre>';

$oqls = array(
    'FROM user',
    'FROM user,userSubGroup',
    'FROM user,userSubGroup,userVehicle',
    'FROM user u,userVehicle uv,userSubGroup usg ON u,parent p ON usg',
    'FROM user u,userSubGroup,userVehicle LIMIT 1',
    'FROM user u,userSubGroup,userVehicle LIMIT 1,1',
    'FROM user u ORDER BY u.name DESC'
);

// if a query load an object that already exists it just update it
foreach ($oqls as $oql) {
    echo '<h3>line ' . __LINE__ . ' getting users which OQL "' . $oql . '", fetching all</h3>';
    $query = new \CRUDsader\Query($oql);
    foreach ($query->fetchAll() as $user) {
        echo '<pre>';
        print_r($user->toArray());
        echo '</pre>';
    }
}

echo '<h3>line ' . __LINE__ . ' getting users which OQL "FROM user u WHERE u.name=?" with arg "batman", fetching only one object</h3>';
$query = new \CRUDsader\Query('FROM user u WHERE u.name=?');
$user = $query->fetch('batman');
echo '<pre>';
print_r($user->toArray());
echo '</pre>';


echo $db->getAdapter('profiler')->display();