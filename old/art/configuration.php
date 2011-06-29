<?php

/**
 * utility to have a configuration that uses default values
 *
 * LICENSE: see Art/license.txt
 *
 * @author Jean-Baptiste Verrey <jeanbaptiste.verrey@gmail.com>
 * @copyright  2010 Jean-Baptiste Verrey
 * @license    http://www.Art.com/license/2.txt
 * @version    $Id$
 * @link       http://www.Art.com/manual/
 * @since      2.0
 */

/**
 * @category   Configuration
 * @package    Art2
 */
class Art_Configuration extends Art_Block {
    /**
     * @staticvar singletoned instance
     */
    protected static $_instance;
    /**
     * @var section loaded
     */
    protected $_sectionUsed = false;
    /**
     * @staticvar the default configuration, is merged with the loaded one
     */
    public static $defaults = array(
        'adapter' => array(
            'classNameSpace' => 'Art_Adapter_Implementation',
            'database' => array(
                'connector' => 'mysql',
                'descriptor' => 'mysql',
                'result' => 'mysql',
                'profiler' => 'none'
            ),
            'identifier'=>'default',
            'i18n'=>'ini'
        ),
        'database' => array(
            'vendor' => 'mysql', // for PDO
            'host' => 'localhost',
            'name' => 'art',
            'user' => 'root',
            'password' => ''
        ),
        'data' => array(
            'paths' => array(
                'Art_Data_'=>'library/art/data/'
            )
        ),
        'debug' => array(
            'php'=>true,
            'database'=>false
         ),
        'form' => array(
            'viewPath' => 'data/form/view/'
        ),
        'i18n'=> array(
            'default'=>array(
                'zone'=>'fr_CH',
                'language'=>'fr',
                'timezone'=>'Europe/London'
            )
        ),
        'mapper' => array(
            'connector'=>'mysql',
            'default' => array(
                'inheritance' => 'table',// table | concrete | hierarchy
                'attributes'=>array(
                    'type'=>'string', // connector type
                    'type-size'=>32,
                    'mandatory'=>false,
                    'data'=>'default',
                    'data-view'=>'default',
                    'data-input'=>'default',
                    'data-validator'=>'default',
                    'data-generator'=>'default',
                    'data-format'=>'default',
                    'data-options'=>array()
                ),
                'associations'=>array(
                    'cardinality'=>'one-to-one',
                    'composition'=>false,
                    'class'=>'AC',
                    'mandatory'=>false
                )
            ),
            'validateXML' => true,
            'pagination'=>array(
                'suffix'=>'through_',
                'maxResults'=>50
            ),
            'identifier'=>array(
                'highIdLength'=>6,
                'length'=>20
            )
        ),
        'mvc' => array(
            'path' => '',
            'server'=>'',
            'folder' => 'application/',
            'router' => array(
                'name'=>'default',
                'separator' => array(
                    'controller' => '/',
                    'params' => '/',
                    'parameter' => '/with',
                )
            ),
            'historic' =>true,
            'default' => array(
                'module' => 'default',
                'controller' => 'default',
                'action' => 'default',
                'params' => ''
            ),
            'module'=>array(
                
            ),
            'view'=>array(
                'template'=>'default',
                'suffix'=>'.view.php'
            )
        ),
        'session'=>array(
            'path'=>false
        )
    );

    /**
     * @static
     * @return self 
     */
    public static function getInstance() {
        if (!isset(self::$_instance))
            self::$_instance = new self();
        return self::$_instance;
    }

    /**
     * singletoned instance
     * @access protected
     */
    protected function __construct() {
        $this->loadDefaults();
    }

    /**
     * define the section to use, basically to use development stages or different configurations
     * @param string|mix $section the name of the section
     * @param bool $loadDefaults insert the defaults in the section
     */
    public function useSection($section=false, $loadDefaults=true) {
        if (!isset($this->_properties[$section]))
            throw new Art_Configuration_Exception('Section <b>' . $section . '</b> does not exists');
        $this->_sectionUsed = $section;
        if ($loadDefaults)
            $this->loadDefaults();
    }

    /**
     * load a INI file
     * @param string $iniFilePath path of the file
     * @param string|mix $section the name of the section
     */
    public function loadIniFile($iniFilePath) {
        if (!file_exists($iniFilePath))
            throw new Art_Configuration_Exception('File <b>' . $iniFilePath . '</b> does not exists');
        $properties = @parse_ini_file($iniFilePath, true);
        if ($properties === false)
            throw new Art_Configuration_Exception('File <b>' . $iniFilePath . '</b> could not be loaded as a configuration INI file');
        $finalProperties = array();
        foreach ($properties as $section => $property) {
            $ex = explode(':', $section);
            $child = trim($ex[0]);
            $parent = isset($ex[1]) ? trim($ex[1]) : false;
            if (!isset($finalProperties[$child]))
                $finalProperties[$child] = array();
            if ($parent && isset($finalProperties[$parent]))
                foreach ($finalProperties[$parent] as $key => $value)
                    $finalProperties[$child][$key] = $value;
            foreach ($property as $key => $value) {
                $pos = strpos($key, '.');
                if ($pos !== false) {
                    $var = '[\'' . str_replace('.', '\'][\'', $key) . '\']';
                    eval('$finalProperties[\'' . $child . '\']' . $var . '=$value;');
                } else
                    $finalProperties[$child][$key] = $value;
            }
        }
        $this->loadArray($finalProperties);
    }

    /**
     * load defaults
     */
    public function loadDefaults() {
        if ($this->_sectionUsed) {
            $this->_properties[$this->_sectionUsed]->loadArray(self::$defaults, false);
        } else
            $this->loadArray(self::$defaults, false);
    }

    public function __get($var) {
        if (isset($this->_properties[$this->_sectionUsed]->_properties[$var]))
            return $this->_properties[$this->_sectionUsed]->_properties[$var];
        else if (isset($this->_properties[$var]))
            return $this->_properties[$var];
        return null;
    }

    public function __set($var, $value) {
        if ($this->_locked)
            throw new Art_Configuration_Exception('Parameter <b>' . $var . '</b> is locked');
        if (isset($this->_properties[$this->_sectionUsed]->_properties[$var]))
            $this->_properties[$this->_sectionUsed]->_properties[$var] = $value;
        else
            $this->_properties[$var] = $value;
    }

    public function __isset($var) {
        return (isset($this->_properties[$this->_sectionUsed]->_properties[$var])) || (isset($this->_properties[$var]));
    }

    public function __unset($var) {
        if (isset($this->_properties[$this->_sectionUsed]->_properties[$var]))
            unset($this->_properties[$this->_sectionUsed]->_properties[$var]);
        unset($this->_properties[$var]);
    }

}
?>
