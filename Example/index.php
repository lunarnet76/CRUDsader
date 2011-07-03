<?php
namespace Art;
// errors
error_reporting(E_ALL);

// class autoloader, REQUIRED
/*
 * you can register multiple namespace, and there is a classic CamelCase fallback autoloader
 */
require('../Art/Autoload.php');
Autoload::registerNameSpace('Art', '../Art/');
spl_autoload_register(array('\Art\Autoload','autoload'));

// load configuration, OPTIONAL 
/*
 * the ini file works more or less like Zend_Config : you have sections, inheritance, arrays, comments
 * you can load other files into the same configuration and existing values would be replaced
 * EVERY CLASSES of the framework uses this configuration, and most of the framework has a default configuration that is defined already in the Configuration class
 * @todo : the INI loader could be an adapter or it could be more options to load configuration such as from YAML, XML, etc...
 */
Configuration::getInstance()->load('configuration.ini','testAlpha'); // load the file with the section testAlpha

// loading ORM Map, REQUIRED
/*
 * different adapters exists, so you can use array instead of the XML file
 * @todo : inheritance type, at the moment only "one table per class" exists
 */
Map::getInstance()->load('map.xml'); // example with the 7 different type of associations

// example of using a basic object
/**
 * classes have a special feature called identity that makes them unique, in this case it's login + type
 */
$employee=new Object('employee');
// attribute have an AttributeType that have validators, format, view (input) and value generator
$employee->login='employee1@mycompany.com';
$employee->type='secretary';
// you can also use expressions in order to use database function or more complex stuff
$employee->created=new Expression\Now(); // same as $employee->created=new Expression('NOW()');
$employee->save(); // can be done in batch by using a unit of work object

// Retrieving the same employee
$query=new Query('FROM employee WHERE id=?',$employee->getId());
$employee=$query->getLast();

// Updating
$employee->type='salesman';
$employee->save();

// Deleting
$employee->delete();

/**
 * Example of 1-1 association with association class
 */
$address=new Object('address');
$address->streetName='Pennsylvania Avenue';
$address->streetNumber='1600';
$employee->address[]=$address;

$address=$employee->address->getLast();// SAME AS $address=$employee->address[0];
$address->association->name='home';// SAME AS $address->name='home';

$employee->save();

/**
 * automatic form
 * you can use directly a query to generate a form such as "FROM employee"
 */
$form=$employee->getForm();
if($form->receive($_POST) && !$form->error()){
    $employee->save();
    $form->reset();
}else
    echo $form;

/*
 * Object Query Language
 * just a complex example
 */
$query=new Query('
    SELECT e.*, a.*, a2e.name
    FROM employee e 
    JOIN address a ON e ASSOCIATION a2e
    WHERE   e.id=? AND (a2e.name=? OR a.streetName=? OR a.streetName=?)
',array(
    array('>='=>'1'),// using an operator
    'home',
    new Expression('"home"'),// using an expression
    array('='=>new Expression('"home"'))// using both
));