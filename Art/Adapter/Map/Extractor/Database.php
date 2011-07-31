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
                    'identity' => $classInfos['definition']['identity'],
                    'fields' => array(),
                    'surrogateKey' => array('type' => 'bigint', 'length' => 20, 'name' => $classInfos['definition']['databaseIdField']),
                    'association' => $classInfos['definition']['association'],
                    'foreignKeys' => array(),
                    'indexes' => array()
                );
                foreach ($classInfos['attributes'] as $attributeName => $attributeInfos) {
                    $tables[$className]['fields'][$attributeName] = array('null' => true, 'type' => $map['attributeTypes'][$attributeInfos['type']]['databaseType'], 'length' => $map['attributeTypes'][$attributeInfos['type']]['length']);
                }
                foreach ($classInfos['definition']['identity'] as $fieldName) {
                    $tables[$className]['fields'][$fieldName]['null'] = false;
                }
            }
            $fks = array();
            foreach ($map['classes'] as $className => $classInfos) {
                if ($classInfos['definition']['association'])
                    continue;
                foreach ($classInfos['associations'] as $associationName => $associationInfos) {
                    switch ($associationInfos['reference']) {
                        case 'external':
                            // add fk in external table
                            $field=$associationInfos['name']?$associationInfos['name']:$className;
                            $tables[$associationInfos['to']]['fields'][$field] = array(
                                'null' => !$associationInfos['composition'],
                                'type' => 'bigint',
                                'length' => 20
                            );
                            // define the fk as a reference
                            $fks[$map['classes'][$associationInfos['to']]['definition']['databaseTable']][$field] = array('table' => $tables[$className]['name'], 'field' => $classInfos['definition']['databaseIdField'], 'onUpdate' => 'restrict', 'onDelete' => ($associationInfos['composition'] ? 'cascade' : 'set null'));
                            break;
                        case 'internal':
                            // add fk in internal table
                            $tables[$className]['fields'][$associationInfos['to']] = array(
                                'null' => !$associationInfos['composition'],
                                'type' => 'bigint',
                                'length' => 20
                            );
                            // define the fk as a reference
                            $fks[$className][$associationInfos['to']] = array('table' => $tables[$associationInfos['to']]['name'], 'field' => $map['classes'][$associationInfos['to']]['definition']['databaseIdField'], 'onUpdate' => 'restrict', 'onDelete' => ($associationInfos['composition'] ? 'cascade' : 'set null'));
                            break;
                        case 'class':
                            $associationTable = $mapObject->getDatabaseAssociationTable($associationInfos['name'], $className, $associationInfos['to']);
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
                            $tables[$associationTable]['fields'][$associationInfos['to']] = array(
                                'null' => false,
                                'type' => 'bigint',
                                'length' => 20
                            );
                            $tables[$associationTable]['fields'][$className] = array(
                                'null' => false,
                                'type' => 'bigint',
                                'length' => 20
                            );
                            // define the fk as a reference
                            $fks[$associationTable][$associationInfos['to']] = array('table' => $tables[$associationInfos['to']]['name'], 'field' => $map['classes'][$associationInfos['to']]['definition']['databaseIdField'], 'onUpdate' => 'restrict', 'onDelete' => 'cascade');
                            $fks[$associationTable][$className] = array('table' => $tables[$className]['name'], 'field' => $map['classes'][$className]['definition']['databaseIdField'], 'onUpdate' => 'restrict', 'onDelete' => 'cascade');
                            //create the table if does not exist
                            break;
                    }
                }
            }
            $database = \Art\Database::getInstance();

            $q = $database->query('set foreign_key_checks = 0', 'update');
            $q = $database->query('SHOW TABLES', 'select');
            foreach ($q as $d) {
                $database->query('DROP TABLE ' . current($d));
            }
            $q = $database->query('set foreign_key_checks = 1', 'update');
            foreach ($tables as $class => $infos) {
                $database->createTable($infos['name'], $infos['fields'], $infos['identity'], $infos['surrogateKey'], $infos['foreignKeys'], $infos['indexes']);
            }
            foreach ($fks as $classFrom => $fkeys) {
                foreach ($fkeys as $fieldFrom => $infos) {
                    $database->createTableReference(array(
                        'fromTable' => isset($map['classes'][$classFrom])?$map['classes'][$classFrom]['definition']['databaseTable']:$classFrom,
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