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
 

class Art_Adapter_Implementation_I18n_Default extends Art_Adapter_Abstract{
	/**
	* constructor
	* @access private
	* @return void
	*/
	public function init(){
	}
	
	public function get($index){
            return $index;
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