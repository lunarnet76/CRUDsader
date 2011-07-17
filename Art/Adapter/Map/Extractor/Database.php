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
            foreach ($map['classes'] as $className => $classInfos) {
                if ($classInfos['definition']['association'])
                    continue;
                foreach ($classInfos['associations'] as $associationName => $associationInfos) {
                    switch ($associationInfos['reference']) {
                        case 'external':
                            // add fk in external table
                            $tables[$associationInfos['to']]['fields'][$className] = array(
                                'null' => !$associationInfos['composition'],
                                'type' => 'bigint',
                                'length' => 20
                            );
                            // define the fk as a reference
                            $tables[$map['classes'][$associationInfos['to']]['definition']['databaseTable']]['foreignKeys'][$className] = array('table' => $tables[$className]['name'], 'field' => $classInfos['definition']['databaseIdField'], 'onUpdate' => 'restrict', 'onDelete' => ($associationInfos['composition'] ? 'cascade' : 'set null'));
                            break;
                        case 'internal':
                            // add fk in internal table
                            $tables[$className]['fields'][$associationInfos['to']] = array(
                                'null' => !$associationInfos['composition'],
                                'type' => 'bigint',
                                'length' => 20
                            );
                            // define the fk as a reference
                            $tables[$className]['foreignKeys'][$associationInfos['to']] = array('table' => $tables[$associationInfos['to']]['name'], 'field' => $map['classes'][$associationInfos['to']]['definition']['databaseIdField'], 'onUpdate' => 'restrict', 'onDelete' => ($associationInfos['composition'] ? 'cascade' : 'set null'));
                            break;
                        case 'class':
                            // add fk in association table
                            $tables[$associationName]['fields'][$associationInfos['to']] = array(
                                'null' => false,
                                'type' => 'bigint',
                                'length' => 20
                            );
                            $tables[$associationName]['fields'][$className] = array(
                                'null' => false,
                                'type' => 'bigint',
                                'length' => 20
                            );
                             // define the fk as a reference
                            $tables[$associationName]['foreignKeys'][$associationInfos['to']] = array('table' => $tables[$associationInfos['to']]['name'], 'field' => $map['classes'][$associationInfos['to']]['definition']['databaseIdField'], 'onUpdate' => 'restrict', 'onDelete' => 'cascade' );
                            $tables[$associationName]['foreignKeys'][$className] = array('table' => $tables[$className]['name'], 'field' => $map['classes'][$className]['definition']['databaseIdField'], 'onUpdate' => 'restrict', 'onDelete' => 'cascade' );
                            break;
                    }
                }
            }
            //pre($tables);
            $database=\Art\Database::getInstance();
            foreach($tables as $class=>$infos){
                $database->createTable($infos['name'],$infos['fields'],$infos['identity'],$infos['surrogateKey'],$infos['foreignKeys'],$infos['indexes']);
            }
            //$database->createTable();
            /**
             * CREATE TABLE
             * @param string $name
             * @param array $fields array('col1'=>array('null'=>$bool,'type'=>$type,'length'=>$intOrFloatOrFalse))
             * @param array $identity array('col1','col2')
             * @param string $surrogateKey array('type'=>$type,'length'=>$int,'name'=>$name)
             * @param string $fkS array('type'=>$type,'length'=>$int,'name'=>$name)
             * @param array $indexes array('index1'=>array('col1','col2'))
             * @return bool 
             */
        }

    }

    class DatabaseException extends \Art\Exception {
        
    }

}