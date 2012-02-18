<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Object\Attribute {
    class Date extends \CRUDsader\Object\Attribute {

        /**
         * return true if valid, string or false otherwise
         * @return type 
         */
        protected function _inputValid() {
            if (parent::_inputValid())
                return true;
            if (!preg_match('|[0-9]{2}/[0-9]{2}/[0-9]{4}|', $this->_inputValue))
                return 'error.invalid';
            return true;
        }

        /**
         * when writing object from database
         * @param type $value
         */
        public function setValueFromDatabase($value) {
            if (\CRUDsader\Expression::isEmpty($value))
                $this->_inputValue = \CRUDsader\Instancer::getInstance()->{'expression.null'};
            else {
                $ex = explode('-', $value);
                switch (count($ex)) {
                    case 3:
                        $this->_inputValue = ($ex[2]=='00'?'':$ex[2]. '/') . ($ex[1]=='00'?'': $ex[1].'/')  . $ex[0];
                        break;
                    case 2:
                        $this->_inputValue = $ex[1] . '/' . $ex[0];
                        break;
                    case 1:
                        $this->_inputValue = $ex[0];
                        break;
                }
            }
        }

        public function getValueForDatabase() {
            if ($this->inputEmpty())
                return new \CRUDsader\Expression\Nil;
            $ex = explode('/', $this->_inputValue);
            switch (count($ex)) {
                case 3:
                    return $ex[2] . '-' . $ex[1] . '-' . $ex[0];
                    break;
                case 2:
                    return $ex[1] . '-' . $ex[0] . '-00';
                    break;
                case 1:
                    return $ex[0] . '-00-00';
                    break;
            }
            return new \CRUDsader\Expression\Nil;
        }
        
        
        public function generateRandom() {
            return date("Y-m-d H:i:s" , strtotime('- '.rand(0,1000).' days'));
        }
    }
}