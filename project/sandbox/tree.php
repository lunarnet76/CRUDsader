<?php
require('../../../CRUDsader/Autoload.php');

CRUDsader\Autoload::register();

try {

	// instancer
	function sl()
	{
		return \CRUDsader\Instancer::getInstance();
	}
	// configuration
	$configuration = sl()->configuration;
	
	// tree
	
	
	
	
	
	
	
	
	
	
	
	
	
} catch (Exception $e) {
	\CRUDsader\Debug::showError($e->getTrace(), 'exception', 'exception : ' . $e->getFile() . ':' . $e->getLine() . ' : ' . $e->getMessage());
	sl()->debug->profileDatabase();
}
