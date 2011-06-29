<?php
/**
 * Art Framework
 *
 * LICENSE
 * see LICENSE.txt.
 *
 * @category   
 * @package    
 * @copyright 
 * @license   
 * @version    
 */
 

class Art_Adapter_Implementation_I18n_Ini extends Art_Adapter_Abstract{
    protected $_translations=array();
	/**
	* constructor
	* @access private
	* @return void
	*/
	public function init(){
            $configuration=Art_Configuration::getInstance()->i18n;
            $this->_language=$configuration->default->language;
	}

        public function loadFile($iniFilePath){
            if(!file_exists($iniFilePath))
                throw new Exception('File <b>'.$iniFilePath.'</b> does not exists');
            $properties=parse_ini_file($iniFilePath,true);
            if($properties===false){
                throw new Exception('File <b>'.$iniFilePath.'</b> could not be loaded as a configuration INI file');
            }
            $finalProperties=array();
            foreach($properties as $section=>$finalPropertiesuration){
                $ex=explode(':',$section);
                $child=trim($ex[0]);
                $parent=isset($ex[1])?trim($ex[1]):false;
                if(!isset($finalProperties[$child]))
                    $finalProperties[$child]=array();
                if($parent&&isset($finalProperties[$parent]))
                    foreach($finalProperties[$parent] as $key=>$value)
                        $finalProperties[$child][$key]=$value;
                foreach($finalPropertiesuration as $key=>$value)
                    $finalProperties[$child][$key]=$value;
            }
            $this->_translations=$finalProperties;
        }
	
	public function get($index){
            if(empty($index))return '';
            return isset($this->_translations[$this->_language][$index])?$this->_translations[$this->_language][$index]:'{'.$index.'}';
        }

        public function setLanguage($index){
            $this->_language=$index;
        }
	
	/**
	* return a singletoned instance
	* @access public
	* @return void
	* @static
	*/
public static function getInstance(){
        return parent::getInstanceOf(__CLASS__);
    }
}
?>