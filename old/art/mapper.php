<?php
class Art_Mapper {
    protected $_queryCache = array();
    protected $_configuration;
    protected $_map = array();
    protected $_connectors = array();
    protected static $_instance;
    protected static $_whereArgs = array();
    protected static $_whereArgsIndex = 0;

    public static function getInstance() {
        if (!isset(self::$_instance))
            self::$_instance = new self();
        return self::$_instance;
    }

    protected function __construct() {
        $this->_configuration = Art_Configuration::getInstance()->mapper;
    }

    public function loadXMLMap($path) {
        if (!file_exists($path))
            throw new Art_Mapper_Exception('XML Map Path <b>' . $path . '</b> does not exist');
        $o = simplexml_load_file($path);
        if (!$o)
            throw new Art_Mapper_Exception('XML Map Path "' . $path . '" could not be loaded');
        if (isset($this->_configuration->validateXML)) {
            $library = new DOMDocument("1.0");
            $library->validateOnParse = true;
            libxml_clear_errors();
            if (!@$library->load($path))
                throw new Art_Mapping_Exception('File <b>' . $path . '</b> was not validated' . implode(',', libxml_get_errors()));
        }
        $defaults = $this->_configuration->default;
        $this->_map[Art_Object_Association::NO_CLASS] = array(
            'definition' => array(
                'database-table' => Art_Object_Association::NO_CLASS,
                'association' => true
            ),
            'attributes' => array(),
            'inherit' => false,
            'associations' => array()
        );
        // classes
        $classes = $o->classes->class;
        foreach ($classes as $class) {
            $name = (string) $class['name'];
            $this->_map[$name] = array(
                'definition' => array(
                    'database-table' => isset($class['database-table']) ? (string) $class['database-table'] : $name,
                    'association' => isset($class['association']) ? (bool) $class['association'] : false
                ),
                'inherit' => false,
                'attributes' => isset($this->_map[$name]['attributes']) ? $this->_map[$name]['attributes'] : array(),
                'associations' => array()
            );
            $parent = false;
            if (isset($class->inherit[0])) {
                $parent = (string) $class->inherit[0]['from'];
                $this->_map[$name]['inherit'] = array(
                    'from' => $parent,
                    'implementation' => isset($class->inherit[0]['implementation']) ? (string) $class->inherit[0]['implementation'] : $defaults->inheritance
                );
                $this->_map[$parent]['attributes']['polymorphism'] = array(
                    'database-field' => 'polymorphism',
                    'type' => 'string',
                    'type-size' => 32,
                    'composition-key' => false,
                    'key' => false,
                    'mandatory' => false,
                );
            }
            $attributes = $class->attribute;
            foreach ($attributes as $attribute) {
                $attributeName = (string) $attribute['name'];
                $this->_map[$name]['attributes'][$attributeName] = array(
                    'database-field' => isset($attribute['database-field']) ? (string) $attribute['database-field'] : $attributeName,
                    'type' => isset($attribute['type']) ? (string) $attribute['type'] : $defaults->attributes->type,
                    'type-size' => isset($attribute['type-size']) ? (string) $attribute['type-size'] : false,
                    'key' => isset($attribute['key']) ? (bool) $attribute['key'] : false,
                    'composition-key' => isset($attribute['composition-key']) ? (bool) $attribute['composition-key'] : false,
                    'mandatory' => isset($attribute['mandatory']) ? (bool) $attribute['mandatory'] : $defaults->attributes->mandatory,
                    'default' => isset($attribute['default']) && (!isset($attribute['key']) || !(bool) $attribute['key']) ? (string) $attribute['default'] : new Art_Database_Expression('NULL'),
                    'data' => isset($attribute['data']) ? (string) $attribute['data'] : $defaults->attributes->data,
                    'data-specify' => array(
                        'view' => isset($attribute['data-view']) ? (string) $attribute['data-view'] : $defaults->attributes->{'data-view'},
                        'input' => isset($attribute['data-input']) ? (string) $attribute['data-input'] : $defaults->attributes->{'data-input'},
                        'format' => isset($attribute['data-format']) ? (string) $attribute['data-format'] : $defaults->attributes->{'data-format'},
                        'generator' => isset($attribute['data-generator']) ? (string) $attribute['data-generator'] : $defaults->attributes->{'data-generator'},
                        'validator' => isset($attribute['data-validator']) ? (string) $attribute['data-validator'] : $defaults->attributes->{'data-validator'}
                    )
                );
                $this->_map[$name]['attributes'][$attributeName]['mandatory'] = $this->_map[$name]['attributes'][$attributeName]['mandatory'] || $this->_map[$name]['attributes'][$attributeName]['key'] || $this->_map[$name]['attributes'][$attributeName]['composition-key'];
                $this->_map[$name]['attributes'][$attributeName]['data-options'] = $defaults->attributes->{'data-options'}->toArray();
                if (isset($attribute['data-options'])) {
                    $this->_map[$name]['attributes'][$attributeName]['data-options'] = (array) json_decode('{' . str_replace('\'', '"', (string) $attribute['data-options']) . '}');
                }
                $this->_map[$name]['attributes'][$attributeName]['data-options']['connector-type'] = $this->_map[$name]['attributes'][$attributeName]['type'];
                $this->_map[$name]['attributes'][$attributeName]['data-options']['connector-type-size'] = $this->_map[$name]['attributes'][$attributeName]['type-size'];
                $this->_map[$name]['attributes'][$attributeName]['data-options']['default'] = $this->_map[$name]['attributes'][$attributeName]['default'];
            }
            $associations = $class->associated;
            foreach ($associations as $association) {

                //
                $associationName = isset($association['name']) ? (string) $association['name'] : (string) $association['to'];
                $this->_map[$name]['associations'][$associationName] = array(
                    'cardinality' => isset($association['cardinality']) ? (string) $association['cardinality'] : $defaults->associations->cardinality,
                    'class' => isset($association['class']) ? (string) $association['class'] : false,
                    'composition' => isset($association['composition']) ? (bool) $association['composition'] : $defaults->associations->composition,
                    'mandatory' => isset($association['mandatory']) ? (bool) $association['mandatory'] : $defaults->associations->mandatory,
                    'to' => (string) $association['to'],
                    'from' => $name,
                    'name' => $associationName
                );
                // error checking
                if ($this->_map[$name]['associations'][$associationName]['composition'])
                    if ($this->_map[$name]['associations'][$associationName]['cardinality'] == 'many-to-many')
                        throw new Art_Mapper_Exception('Association between ' . $name . ' and ' . $associationName . ' is forbidden (many-to-many composition)');
                    else if ($this->_map[$name]['associations'][$associationName]['cardinality'] == 'one-to-one' && $this->_map[$name]['associations'][$associationName]['class'])
                        throw new Art_Mapper_Exception('Association between ' . $name . ' and ' . $associationName . ' is fobidden (one-to-one composition with association class)');
            }
        }
        $connectors = $o->connectors->connector;
        foreach ($connectors as $connector) {
            $connectorName = (string) $connector['name'];
            $this->_connectors[$connectorName] = array(
                'id' => array(
                    'database-type' => (string) $connector->id[0]['database-type'],
                    'database-default-size' => (string) $connector->id[0]['database-default-size']
                ),
                'types' => array()
            );
            $types = $connector->type;
            foreach ($types as $type)
                $this->_connectors[$connectorName]['types'][(string) $type['name']] = array(
                    'database-type' => (string) $type['database-type'],
                    'database-default-size' => (string) $type['database-default-size'],
                    'database-operator' => isset($type['database-operator']) ? (string) $type['database-operator'] : '=',
                );
        }
    }

    /**
     * @todo add inheritance implementations
     */
    public function createDatabase($createOnlyTable=false) {
        $db = Art_Database::getInstance();
        $tables = array();
        $idField = array('type' => $this->_getFieldType('id'), 'null' => false, 'autoincrement' => true);
        $fkField = array('type' => $this->_getFieldType('id'), 'null' => false);
        $polymorphismField = array('type' => $this->_getFieldType('string', 32), 'null' => false);
        $fkFieldNull = array('type' => $this->_getFieldType('id'), 'null' => true);
        // classes
        foreach ($this->_map as $className => $class) {
            $tableName = $class['definition']['database-table'];
            $tables[$tableName] = array('fields' => array('id' => $idField), 'pk' => array('id'), 'indexes' => array('uniqueness' => array(), 'composition' => array()));
            foreach ($class['attributes'] as $attributeName => $attribute) {
                $tables[$tableName]['fields'][$attribute['database-field']] = array('type' => $this->_getFieldType($attribute['type'], $attribute['type-size']), 'null' => !( $attribute['key'] || $attribute['composition-key']));
                if ($attribute['key'])
                    $tables[$tableName]['indexes']['uniqueness'][$attribute['database-field']] = $attribute['database-field'];
                if ($attribute['composition-key'])
                    $tables[$tableName]['indexes']['composition'][$attribute['database-field']] = $attribute['database-field'];
            }
        }
        // associations
        foreach ($this->_map as $className => $class) {
            foreach ($class['associations'] as $associationName => $association) {
                if ($association['composition']) {
                    // ref in association class
                    if ($association['class']) {
                        $tableAssociationName = $this->getAssociationTableName($className, $association['to'], $associationName);
                        $tables[$tableAssociationName] = $tables[$association['class']]; // by copy, not by reference
                        $tables[$tableAssociationName]['fields'][$className] = $fkField;
                        $tables[$tableAssociationName]['fields'][$associationName] = $fkField;
                        $tables[$tableAssociationName]['indexes']['composition'][$className] = $className;
                        $tables[$tableAssociationName]['indexes']['composition'][$associationName] = $associationName;
                        // ref in composed table
                    } else {
                        $tableOut = $this->_map[$association['to']]['definition']['database-table'];
                        $tables[$tableOut]['fields'][$className] = $fkFieldNull;
                        $tables[$tableOut]['indexes']['composition'][$className] = $className;
                    }
                } else {
                    //if (isset($tables[$tableName]['indexes']['composition']))unset($tables[$tableName]['indexes']['composition']);
                    switch ($association['cardinality']) {
                        case 'one-to-one':
                            if ($association['class']) {
                                $tableAssociationName = $this->getAssociationTableName($className, $association['to'], $associationName);
                                $tables[$tableAssociationName] = $tables[$association['class']]; // by copy, not by reference
                                $tables[$tableAssociationName]['fields'][$className] = $fkField;
                                $tables[$tableAssociationName]['fields'][$associationName] = $fkField;
                                $tables[$tableAssociationName]['indexes']['uniqueness'][$className] = $className;
                                $tables[$tableAssociationName]['indexes']['uniqueness'][$associationName] = $associationName;
                            } else {
                                $tables[$class['definition']['database-table']]['fields'][$associationName] = $association['mandatory'] ? $fkField : $fkFieldNull;
                                $tables[$class['definition']['database-table']]['indexes'][$associationName] = array($associationName);
                            }
                            break;
                        case 'many-to-many':
                        case 'one-to-many':
                            $tableAssociationName = $this->getAssociationTableName($className, $association['to'], $associationName);
                            if ($association['class'])
                                $tables[$tableAssociationName] = $tables[$association['class']]; // by copy, not by reference
                            else
                                $tables[$tableAssociationName] = array('fields' => array('id' => $idField), 'pk' => array('id'), 'indexes' => array('uniqueness' => array()));
                            $tables[$tableAssociationName]['fields'][$className] = $fkField;
                            $tables[$tableAssociationName]['fields'][$association['to']] = $fkField;
                            if ($association['cardinality'] != 'many-to-many') {
                                $tables[$tableAssociationName]['indexes']['uniqueness'][$className] = $className;
                                $tables[$tableAssociationName]['indexes']['uniqueness'][$association['to']] = $association['to'];
                            }
                            break;
                    }
                }
            }
        }
        foreach ($this->_map as $className => $class)
            if ($class['definition']['association'] && !count($class['associations']))
                unset($tables[$className]);
        foreach ($tables as $name => $table) {
            if (!$createOnlyTable || $createOnlyTable == $name)
                $db->createTable($name, $table['fields'], $table['pk'], $table['indexes']);
        }
    }

    public function paginate(array $params=array()) {
        $configuration = Art_Configuration::getInstance()->mapper->pagination;
        $defaultParams = array('oql' => false, 'args' => array(), 'index' => false, 'results' => $configuration->maxResults, 'reset' => false);
        foreach ($defaultParams as $k => $v)
            $params[$k] = isset($params[$k]) ? $params[$k] : $v;
        $select = $this->_oqlToSql($params['oql'], $params['args']);
        /*
          pre($params['oql']);
          pre((string) $select['sql']);
          // */
        return new Art_Query_Pagination($select['sql'], $configuration->suffix . $params['index'], $params['results'], $params['reset'], $params['args'], $select['class']);
    }

    /**
     * @todo add inheritance implementations, cache, split request in 2 : 1-1 and 1.*
     * @todo add always association.* and parent ? 
     */
    public function query($oql, $args=array(), $fetchAll=true, $limit=false) {
        $select = $this->_oqlToSql($oql, $args);
        /*
          pre($oql);
          pre((string) $select['sql']);
          // */
        if ($limit)
            $select['sql']->limit($limit);

        $this->_lastResults = Art_Database::getInstance()->query($select['sql'], 'select');

        //foreach($results as $r)pre($r);
        $ret = $this->fetchResultsOfQuery($this->_lastResults, $select['class'], $fetchAll);
        return $ret;
    }

    // a hack ...
    public function getLastResults() {
        return $this->_lastResults;
    }

    /**
     * @todo check "special for many to many that have associated classes"
     */
    protected function _oqlToSql($oql, $args=array()) {
        self::$_whereArgs = is_array($args) ? $args : array($args);
        self::$_whereArgsIndex = 0;
        $ex = explode('ORDER BY', $oql);
        $classes = array();
        // ORDER BY
        $orderBy = false;
        if (isset($ex[1])) {
            $oql = $ex[0];
            $orderBy = $ex[1];
            $matching = preg_match_all('|\s*([^\.\,]*)([\.\,])?|', $orderBy . ',', $orderByFields, PREG_SET_ORDER);
            if ($matching === false)
                throw new Art_Mapper_Query_Exception('bad ORDER BY part');
        }
        $wellStructured = preg_match('_^(\s*(SELECT)\s+([a-zA-Z0-9\.\,\*\s]*)\s+)?(FROM)\s+([a-zA-Z0-9]*)\s*(\s+(WHERE)\s+([a-zA-Z0-9\.\=\?\s\(\)]*))?$_', $oql, $matches);
        if (!$wellStructured)
            throw new Art_Mapper_Query_Exception('bad query');
        $part = false;
        $fields = array();
        $where = false;
        foreach ($matches as $match) {
            switch ($match) {
                case 'SELECT':
                case 'FROM':
                case 'WHERE':
                case 'LIMIT':
                    $part = $match;
                    break;
                default:
                    if (!$part)
                        continue;
                    switch ($part) {
                        case 'SELECT':
                            $matching = preg_match_all('|\s*([^\.\,]*)([\.\,])|', $match . ',', $fields, PREG_SET_ORDER);
                            if ($matching === false)
                                throw new Art_Mapper_Query_Exception('bad SELECT part');
                            break;
                        case 'FROM':
                            $baseClass = $class = $match;
                            $classes[$class] = $class;
                            break;
                        case 'WHERE':
                            $where = preg_replace_callback('|([a-zA-Z0-9\.]*)\=(\?)|', array('Art_Mapper', '_whereCallBack'), $match);
                    }
                    $part = false;
            }
        }
        if (!isset($this->_map[$class]))
            throw new Art_Mapper_Query_Exception('class "' . $class . '" does not exist');
        // build select
        $baseClassAlias = $alias = 'object';
        $specifiedFields = array($alias => array($alias . '_id' => 'id'));
        $select = new Art_Database_Select($this->_map[$class]['definition']['database-table'], $alias);
        if ($where)
            $select->where($where, $args);
        // buid fields
        $joins = array();
        foreach ($fields as $field) {
            switch ($field[2]) {
                case ',': // is a fieldname or *
                    $specifiedFields[$alias][$alias . '_id'] = 'id';
                    if ($field[1] != 'id') {
                        if ($field[1][0] == '*') {
                            foreach ($this->_map[$baseClass]['attributes'] as $attributeName => $attribute)
                                $specifiedFields[$alias][$alias . '_' . $attributeName] = $attribute['database-field'];
                        } else {
                            $attribute = $this->classGetAttribute($baseClass, $field[1]);
                            $specifiedFields[$alias][$alias . '_' . $field[1]] = $attribute['database-field'];
                        }
                    }
                    $baseClass = $class;
                    $alias = $baseClassAlias = 'object';
                    unset($association);
                    break;
                case '.':// join
                    $alias.='_' . $field[1];
                    if ($this->classHasParent($baseClass) && ($field[1] == 'parent' || $this->classGetParentClass($baseClass) == $field[1])) {
                        $baseClass = $this->classGetParentClass($baseClass);
                        $joins[$alias] = array($alias => $this->classGetTable($baseClass), 'join' => array($alias, 'id', $baseClassAlias, 'id'), 'type' => 'LEFT', 'fields' => array($alias . '_id' => 'id'));
                        $baseClassAlias = $alias;
                    } else if ($field[1] == 'association') {
                        if (!isset($association) || !$association['class'])
                            throw new Art_Mapper_Query_Exception('association ' . $alias . ' does not exist');
                        $baseClass = $association['class'];
                        $baseClassAlias = $alias;
                    }
                    else if ($this->classHasAssociation($baseClass, $field[1])) {
                        $association = $this->classGetAssociation($baseClass, $field[1]);
                        if ($association['composition']) {
                            // ref in association class
                            if ($association['class']) {
                                // link to the association class
                                $tableAssociationName = $this->getAssociationTableName($association['from'], $association['to'], $field[1]);
                                $joins[$alias . '_association'] = array($alias . '_association' => $tableAssociationName, 'join' => array($alias . '_association', ($baseClass), $baseClassAlias, 'id'), 'type' => 'LEFT', 'fields' => array($alias . '_id' => 'id'));
                                $joins[$alias] = array($alias => $this->classGetTable($association['to']), 'join' => array($alias, 'id', $alias . '_association', $field[1]), 'type' => 'LEFT', 'fields' => array($alias . '_id' => 'id'));
                            } else
                                $joins[$alias] = array($alias => $this->classGetTable($association['to']), 'join' => array($alias, $baseClass, $baseClassAlias, 'id'), 'type' => 'LEFT', 'fields' => array($alias . '_id' => 'id'));
                        } else {
                            if ($association['cardinality'] == 'one-to-one' && !$association['class']) {
                                $joins[$alias] = array($alias => $association['to'], 'join' => array($alias, 'id', $baseClassAlias, $association['to']), 'type' => 'LEFT', 'fields' => array($alias . '_id' => 'id'));
                            } else {
                                $tableAssociationName = $this->getAssociationTableName($association['from'], $association['name'], $field[1]);
                                $joins[$alias . '_association'] = array($alias . '_association' => $tableAssociationName, 'join' => array($alias . '_association', $baseClass, $baseClassAlias, 'id'), 'type' => 'LEFT', 'fields' => array($alias . '_association_id' => 'id'));
                                $joins[$alias] = array($alias => $this->classGetTable($association['to']), 'join' => array($alias, 'id', $alias . '_association', $association['to']), 'type' => 'LEFT', 'fields' => array($alias . '_id' => 'id'));
                            }
                        }
                        $baseClass = $association['to'];
                        $baseClassAlias = $alias;
                        $lastAssociation = $association;
                    }else
                        throw new Art_Mapper_Query_Exception('association ' . $alias . ' does not exist');
                    $baseTableAlias = $field[1];
                    $classes[$baseClass] = $baseClass;
                    break;
            }
        }
        // build order by
        if (isset($orderByFields)) {
            $orderByClause = '';
            $baseClass = $class;
            foreach ($orderByFields as $field) {
                if (!isset($field[2])
                )
                    continue;
                switch ($field[2]) {
                    case ',': // is a fieldname or *
                        $v = trim($field[1]);
                        $desc = false;
                        if (false !== $r = strpos($v, 'DESC')) {
                            $desc = true;
                            $field[1] = substr($v, 0, $r);
                        }
                        if ($field[1] == 'id')
                            $orderByClause.=$alias . '.id' . ($desc ? ' DESC' : ' ASC') . ',';
                        else {
                            $attribute = $this->classGetAttribute($baseClass, trim($field[1]));
                            $orderByClause.=$baseClassAlias . '_' . $attribute['database-field'] . ($desc ? ' DESC' : ' ASC') . ',';
                        }
                        $baseClass = $class;
                        $alias = $baseClassAlias = 'object';
                        unset($association);
                        break;
                    case '.':// join
                        $alias.='_' . $field[1];
                        if ($this->classHasParent($baseClass) && ($field[1] == 'parent' || $this->classGetParentClass($baseClass) == $field[1])) {
                            $baseClass = $this->classGetParentClass($baseClass);
                            $baseClassAlias = $alias;
                        } else if ($field[1] == 'association') {
                            if (!isset($association))
                                throw new Art_Mapper_Query_Exception('association ' . $alias . ' does not exist');
                            $baseClass = $association['class'];
                            $baseClassAlias = $alias;
                        }
                        else if ($this->classHasAssociation($baseClass, $field[1])) {
                            $association = $this->classGetAssociation($baseClass, $field[1]);
                            $baseClass = $association['to'];
                            $baseClassAlias = $alias;
                            $lastAssociation = $association;
                        }else
                            throw new Art_Mapper_Query_Exception('association ' . $alias . ' does not exist');
                        $baseTableAlias = $field[1];
                        break;
                }
            }
            if ($orderByClause[strlen($orderByClause) - 1] == ',')
                $orderByClause[strlen($orderByClause) - 1] = ' ';
            if ($orderBy)
                $select->orderBy($orderByClause);
        }

        if (isset($specifiedFields['object']))
            $select->specifySelectFromTableFields($specifiedFields['object']);
        foreach ($joins as $i => $join) {
            if (isset($specifiedFields[$i]))
                $join['fields'] = $specifiedFields[$i];
            $select->join($join);
        }
        return array('sql' => $select, 'class' => $class, 'included' => $classes);
    }

    public function getAllowedClassForPopulateNewObject($oql) {
        $select = $this->_oqlToSql($oql);
        return $select['included'];
    }

    /**
     * @todo replace eval or not ?
     * @param <type> $results
     * @param <type> $class
     * @param <type> $fetchAll
     * @return <type>
     */
    public function fetchResultsOfQuery($results, $class, $fetchAll=true) {
        // write into object
        $objects = new Art_Object_Collection($class);
        Art_Object::setWriteMode(true);
        Art_Object_Collection_Association::setWriteMode(true);
        $object = false;
        foreach ($results as $fields) {
            foreach ($fields as $fieldName => $fieldValue) {
                if ($fieldName == 'object_id') {
                    if (!isset($objects[$fieldValue])) {
                        $object = new Art_Object($class);
                        $object->id = $fieldValue;
                        $objects[$fieldValue] = $object;
                    }
                    $object = $objects[$fieldValue];
                }
                $this->evalSet($object, $fieldName, $fieldValue);
            }
            //echo '<br><br><br><br>';
        }
        Art_Object::setWriteMode(false);
        Art_Object_Collection_Association::setWriteMode(false);
        return $fetchAll ? $objects : ($objects->count() == 1 ? $objects->current() : false);
    }

    public function evalSet($object, $fieldName, $fieldValue) {
        // pre($fieldName.'='.$fieldValue);
        $ex = explode('_', $fieldName);
        $cnt = count($ex);
        $first = true;
        foreach ($ex as $i => $e) {
            if ($first) {
                $first = false;
            } else {
                if ($i == $cnt - 1)
                    $object->$e = $fieldValue;
                else
                    $object= & $object->$e;
            }
        }
    }

    function evalGet($object, $fieldName) {
        $ex = explode('_', $fieldName);
        $first = true;
        foreach ($ex as $i => $e)
            if ($first) {
                $first = false;
            } else
                $object = $object->$e;
        return $object;
    }

    public function toArray() {
        return $this->_map;
    }

    public function classHasParent($className) {
        return isset($this->_map[$className]['inherit']['from']);
    }

    public function classGetParentClass($className) {
        return $this->_map[$className]['inherit']['from'];
    }

    public function classHasAttribute($className, $attributeName) {
        return isset($this->_map[$className]['attributes'][$attributeName]);
    }

    public function classGetAttributeFieldName($className, $attributeName) {
        return $this->_map[$className]['attributes'][$attributeName]['database-field'];
    }

    public function classGetAttribute($className, $attributeName) {
        return $this->_map[$className]['attributes'][$attributeName];
    }

    public function classGetAttributes($className) {
        return $this->_map[$className]['attributes'];
    }

    public function classExists($className) {
        return isset($this->_map[$className]);
    }

    public function classGetTable($className) {
        return $this->_map[$className]['definition']['database-table'];
    }

    public function classIsAssociationClass($className) {
        return $this->_map[$className]['definition']['association'];
    }

    public function classHasAssociation($className, $associationName) {
        return isset($this->_map[$className]['associations'][$associationName]);
    }

    public function classGetAssociation($className, $associationName) {
        return $this->_map[$className]['associations'][$associationName];
    }

    public function classGetAssociations($className) {
        return $this->_map[$className]['associations'];
    }

    public function classInheritsFrom($className, $parentClassName) {
        return isset($this->_map[$className]['inherit']['from']) && ($this->_map[$className]['inherit']['from'] == $parentClassName || (isset($this->_map[$this->_map[$className]['inherit']['from']]['inherit']['from']) && $this->_map[$this->_map[$className]['inherit']['from']]['inherit']['from'] == $parentClassName));
    }

    public function getAssociationTableName($class, $classTo, $associationName) {
        $i = $this->_map[$class]['associations'][$associationName];
        if ($i['class'] == $i['name'])
            return $associationName;
        return $class > $classTo ? $classTo . '2' . $class : $class . '2' . $classTo;
    }

    protected function _getFieldType($connectorType, $connectorTypeSize=false) {
        $connector = $this->_connectors[$this->_configuration->connector];
        if ($connectorType == 'id'
        )
            return $connector['id']['database-type'] . '(' . $connector['id']['database-default-size'] . ')';
        $size = $connectorTypeSize ? $connectorTypeSize : $connector['types'][$connectorType]['database-default-size'];
        return $connector['types'][$connectorType]['database-type'] . ($size ? '(' . $size . ')' : '');
    }

    protected static function _whereCallBack($string) {
        $operator = '=';
        if (is_array(self::$_whereArgs[self::$_whereArgsIndex])) {
            $operator = key(self::$_whereArgs[self::$_whereArgsIndex]);
            $arg = current(self::$_whereArgs[self::$_whereArgsIndex++]);
        }else
            $arg=self::$_whereArgs[self::$_whereArgsIndex++];
        $pos = strrpos($string[1], '.');
        $end = false;
        if ($pos !== false) {
            $end = substr($string[1], $pos + 1);
            $string[1] = substr($string[1], 0, $pos);
            return 'object_' . str_replace('.', '_', $string[1]) . '.' . $end . ' ' . $operator . ' ' . Art_Database::getInstance()->quote($arg);
        } else {
            return 'object.' . $string[1] . ' ' . $operator . ' ' . Art_Database::getInstance()->quote($arg);
        }
    }
}