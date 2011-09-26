<?php
/**
 * LICENSE: see Art/license.txt
 *
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     http://www.Art.com/license/1.txt
 * @version     $Id$
 * @link        http://www.Art.com/manual/
 * @since       1.0
 */
namespace Art\Adapter\Map\Extractor {

    /**
     * create the mapping tables
     * @abstract
     * @package    Art\Adapter\Map
     */
    class Database extends \Art\Adapter\Map\Extractor {

        public function create(array $map) {
            $mapObject = \Art\Map::getInstance();
            $tables = array();
            foreach ($map['classes'] as $className => $classInfos) {
                $tables[$className] = array(
                    'name' => $classInfos['definition']['databaseTable'],
                    'identity' => array(),
                    'fields' => array(),
                    'surrogateKey' => array('type' => 'bigint', 'length' => 20, 'name' => $classInfos['definition']['databaseIdField']),
                    'foreignKeys' => array(),
                    'indexes' => array()
                );
                foreach ($classInfos['attributes'] as $attributeName => $attributeInfos) {
                    $tables[$className]['fields'][$attributeInfos['databaseField']] = array('null' => true, 'type' => $map['attributeTypes'][$attributeInfos['type']]['databaseType'], 'length' => $map['attributeTypes'][$attributeInfos['type']]['length']?$map['attributeTypes'][$attributeInfos['type']]['length']:false);
                }
                /*if($classInfos['inherit']){
                    $clDef=$map['classes'][$className]['definition'];
                    $fks[$clDef['databaseTable']][$clDef['databaseIdField']] = array('table' => $tables[$className]['name'], 'field' => $classInfos['definition']['databaseIdField'], 'onUpdate' => 'restrict', 'onDelete' => ($associationInfos['composition'] ? 'cascade' : 'set null'));
                }*/
            }
            $fks = array();
            foreach ($map['classes'] as $className => $classInfos) {
                foreach ($classInfos['definition']['identity'] as $fieldName) {
                    $tables[$className]['fields'][$classInfos['attributes'][$fieldName]['databaseField']]['null'] = false;
                    $tables[$className]['identity'][]=$classInfos['attributes'][$fieldName]['databaseField'];
                }
                foreach ($classInfos['associations'] as $associationName => $associationInfos) {
                    switch ($associationInfos['reference']) {
                        case 'external':
                            // add fk in external table
                            $tables[$associationInfos['to']]['fields'][$associationInfos['externalField']] = array(
                                'null' =>  true,
                                'type' => 'bigint',
                                'length' => 20
                            );
                            // define the fk as a reference
                            $fks[$map['classes'][$associationInfos['to']]['definition']['databaseTable']][$associationInfos['externalField']] = array('table' => $tables[$className]['name'], 'field' => $classInfos['definition']['databaseIdField'], 'onUpdate' => 'restrict', 'onDelete' => ($associationInfos['composition'] ? 'cascade' : 'set null'));
                            break;
                        case 'internal':
                            // add fk in internal table
                            $tables[$className]['fields'][$associationInfos['internalField']] = array(
                                'null' => true,
                                'type' => 'bigint',
                                'length' => 20
                            );
                            // define the fk as a reference
                            $fks[$className][$associationInfos['internalField']] = array('table' => $tables[$associationInfos['to']]['name'], 'field' => $map['classes'][$associationInfos['to']]['definition']['databaseIdField'], 'onUpdate' => 'restrict', 'onDelete' => ($associationInfos['composition'] ? 'cascade' : 'set null'));
                            break;
                        case 'table':
                            $associationTable =$associationInfos['databaseTable'];
                            if (!isset($tables[$associationTable])) {
                                $tables[$associationTable] = array(
                                    'name' => $associationTable,
                                    'identity' => array(),
                                    'fields' => array(),
                                    'surrogateKey' => array('type' => 'bigint', 'length' => 20, 'name' => $this->_configuration->idField),
                                    'association' => array(),
                                    'foreignKeys' => array(),
                                    'indexes' => array()
                                );
                            }
                            // add fk in association table
                            $tables[$associationTable]['fields'][$associationInfos['internalField']] = array(
                                'null' => false,
                                'type' => 'bigint',
                                'length' => 20
                            );
                            $tables[$associationTable]['fields'][$associationInfos['externalField']] = array(
                                'null' => false,
                                'type' => 'bigint',
                                'length' => 20
                            );
                            // define the fk as a reference
                            $fks[$associationTable][$associationInfos['externalField']] = array('table' => 
                                $tables[$associationInfos['to']]['name'],
                                'field' => $map['classes'][$associationInfos['to']]['definition']['databaseIdField'], 'onUpdate' => 'restrict', 'onDelete' => 'cascade');
                            $fks[$associationTable][$associationInfos['internalField']] = array('table' => $tables[$className]['name'], 'field' => $map['classes'][$className]['definition']['databaseIdField'], 'onUpdate' => 'restrict', 'onDelete' => 'cascade');
                            //create the table if does not exist
                            break;
                    }
                }
            }
            $database = \Art\Database::getInstance();
            $q = $database->setForeignKeyCheck(false);
            $q = $database->listTables();
            foreach ($q as $d) {
                $database->query('DROP TABLE `' . current($d) . '`');
            }
            $q = $database->setForeignKeyCheck(true);
            foreach ($tables as $class => $infos) {
                $database->createTable($infos['name'], $infos['fields'], $infos['identity'], $infos['surrogateKey'], $infos['foreignKeys'], $infos['indexes']);
            }
            foreach ($fks as $classFrom => $fkeys) {
                foreach ($fkeys as $fieldFrom => $infos) {
                    $database->createTableReference(array(
                        'fromTable' => isset($map['classes'][$classFrom]) ? $map['classes'][$classFrom]['definition']['databaseTable'] : $classFrom,
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
    class DatabaseException extends \Art\Exception {
        
    }
}