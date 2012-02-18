<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Object\Attribute {
    class DateTime extends \CRUDsader\Object\Attribute {

        /**
         * return true if valid, string or false otherwise
         * @return type 
         */
        protected function _inputValid() {
            return true;
        }
        
        public function setValueFromDatabase($value) {
            if (\CRUDsader\Expression::isEmpty($value))
                $this->_inputValue = \CRUDsader\Instancer::getInstance()->{'expression.null'};
            else {
                if(preg_match('|([0-9]{4})-([0-9]{2})-([0-9]{2})\s([0-9]{1,2}):([0-9]{2}):[0-9]{2}|',$value,$m)){
                    $this->_inputValue=$m[3].'/'.$m[2].'/'.$m[1].' '.$m[4].':'.$m[5];
                }
            }
        }
        
        public function generateRandom() {
            return date("Y-m-d H:i:s" , strtotime('- '.rand(0,1000).' days'));
        }

    }
}