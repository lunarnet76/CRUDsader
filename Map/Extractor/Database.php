<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Map\Extractor {
	/**
	 * create the mapped tables
	 * @abstract
	 * @package    CRUDsader\Map
	 */
	class Database extends \CRUDsader\Map\Extractor {

		public function extract(array $map, array $doNotDeleteTable = null)
		{
			$mapObject = \CRUDsader\Instancer::getInstance()->map;
			$tables = array();
			foreach ($map['classes'] as $className => $classInfos) {
				$infoSurrogateKey = $mapObject->classGetFieldAttributeIdType($className);
				$surrogateKey = array('type' => $infoSurrogateKey['databaseType'], 'length' => $infoSurrogateKey['length'], 'name' => $classInfos['definition']['databaseIdField']);
				$tables[$className] = array(
				    'name' => $classInfos['definition']['databaseTable'],
				    'identity' => array(),
				    'fields' => array(),
				    'surrogateKey' => $surrogateKey,
				    'foreignKeys' => array(),
				    'indexes' => array()
				);
				foreach ($classInfos['attributes'] as $attributeName => $attributeInfos) {
					$tables[$className]['fields'][$attributeInfos['databaseField']] = array('null' => true, 'type' => $map['attributeTypes'][$attributeInfos['type']]['databaseType'], 'length' => $map['attributeTypes'][$attributeInfos['type']]['length'] ? $map['attributeTypes'][$attributeInfos['type']]['length'] : false);
				}
				/* if($classInfos['inherit']){
				  $clDef=$map['classes'][$className]['definition'];
				  $fks[$clDef['databaseTable']][$clDef['databaseIdField']] = array('table' => $tables[$className]['name'], 'field' => $classInfos['definition']['databaseIdField'], 'onUpdate' => 'restrict', 'onDelete' => ($associationInfos['composition'] ? 'cascade' : 'set null'));
				  } */
			}
			$fks = array();
			foreach ($map['classes'] as $className => $classInfos) {
				foreach ($classInfos['definition']['identity'] as $fieldName) {
					$tables[$className]['identity'][] = $classInfos['attributes'][$fieldName]['databaseField'];
				}
				foreach ($classInfos['associations'] as $associationName => $associationInfos) {
					switch ($associationInfos['reference']) {
						case 'external':
							// add fk in external table
							$tables[$associationInfos['to']]['fields'][$associationInfos['externalField']] = array(
							    'null' => true,
							    'type' => 'int',
							    'length' => 10
							);
							$tables[$associationInfos['to']]['indexes'][$associationInfos['externalField']] = array($associationInfos['externalField']);

							// define the fk as a reference
							$toField =  $associationInfos['databaseIdField'];
							$fks[$map['classes'][$associationInfos['to']]['definition']['databaseTable']][$associationInfos['externalField']] = array('reference'=>'external','table' => $tables[$className]['name'], 'field' => $toField, 'onUpdate' => 'restrict', 'onDelete' => ($associationInfos['composition'] ? 'cascade' : 'set null'));
							break;
						case 'internal':
							// add fk in internal table
							if (!isset($tables[$className]['fields'][$associationInfos['internalField']]))
								$tables[$className]['fields'][$associationInfos['internalField']] = array(
								    'null' => true,
								    'type' => 'int',
								    'length' => 10
								);
							$toField = $associationInfos['externalField'] ? $associationInfos['externalField'] : $map['classes'][$associationInfos['to']]['definition']['databaseIdField'];
							if ($associationInfos['externalField']){
								$tables[$associationInfos['to']]['indexes'][$associationInfos['externalField']] = array($associationInfos['externalField']);
							}
							$tables[$className]['indexes'][$associationInfos['internalField']] = array($associationInfos['internalField']);
							// define the fk as a reference

							$fks[$className][$associationInfos['internalField']] = array('reference'=>'internal','table' => $tables[$associationInfos['to']]['name'], 'field' => $toField, 'onUpdate' => 'restrict', 'onDelete' => ($associationInfos['composition'] ? 'cascade' : 'set null'));
							break;
						case 'table':
							$associationTable = $associationInfos['databaseTable'];
							if (!isset($tables[$associationTable])) {
								$tables[$associationTable] = array(
								    'name' => $associationTable,
								    'identity' => array(),
								    'fields' => array(),
								    'surrogateKey' => array('type' => 'int', 'length' => 10, 'name' => $this->_configuration->idField),
								    'association' => array(),
								    'foreignKeys' => array(),
								    'indexes' => array()
								);
							}
							// add fk in association table
							$tables[$associationTable]['fields'][$associationInfos['internalField']] = array(
							    'null' => false,
							    'type' => 'int',
							    'length' => 10
							);
							$tables[$associationTable]['fields'][$associationInfos['externalField']] = array(
							    'null' => false,
							    'type' => 'int',
							    'length' => 10
							);
							// define the fk as a reference
							$fks[$associationTable][$associationInfos['externalField']] = array(
							    'table' => $tables[$associationInfos['to']]['name'],
							    'field' => $map['classes'][$associationInfos['to']]['definition']['databaseIdField'],
							    'onUpdate' => 'restrict', 'onDelete' => 'cascade'
							);
							$fks[$associationTable][$associationInfos['internalField']] = array(
							    'table' => $tables[$className]['name'],
							    'field' => $map['classes'][$className]['definition']['databaseIdField'],
							    'onUpdate' => 'restrict', 'onDelete' => 'cascade'
							);
							//create the table if does not exist
							break;
					}
				}
			}
			$database = \CRUDsader\Instancer::getInstance()->database;
			$q = $database->setForeignKeyCheck(false);
			$q = $database->listTables();

			foreach ($q as $d) {
				if ($doNotDeleteTable == null || !in_array(current($d), $doNotDeleteTable))
					$database->query('DROP TABLE `' . current($d) . '`', 'drop');
			}
			$q = $database->setForeignKeyCheck(true);

			foreach ($tables as $class => $infos) {
				if ($doNotDeleteTable == null || !in_array($infos['name'], $doNotDeleteTable))
					$database->createTable($infos['name'], $infos['fields'], $infos['identity'], $infos['surrogateKey'], $infos['indexes']);
			}
			foreach ($fks as $classFrom => $fkeys) {
				foreach ($fkeys as $fieldFrom => $infos) {
					$fromTable = isset($map['classes'][$classFrom]) ? $map['classes'][$classFrom]['definition']['databaseTable'] : $classFrom;
					if ($doNotDeleteTable == null || !in_array($fromTable, $doNotDeleteTable)) {

						$database->createTableReference(array(
						    'fromTable' => $fromTable,
						    'toTable' => $infos['table'],
						    'fromField' => $fieldFrom,
						    'toField' => $infos['field'],
						    'onUpdate' => $infos['onUpdate'],
						    'onDelete' => $infos['onDelete']
						));
					}
				}
			}
		}
	}
	class DatabaseException extends \CRUDsader\Exception {
		
	}
}