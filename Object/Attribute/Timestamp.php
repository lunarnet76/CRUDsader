<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       21/02/2012
 */
namespace CRUDsader\Object\Attribute {
        class Timestamp extends \CRUDsader\Object\Attribute {

                /**
                 * return true if valid, string or false otherwise
                 * @return type 
                 */
                public function isValid() {
                        if($this->_value instanceof \CRUDsader\Expression)
                                return true;
                        if (preg_match('|^[0-9]*$|', $this->_value)){
                                return isset($this->_options['inTheFuture']) && $this->_options['inTheFuture'] ? $this->_value > time() : true;
                        }
                        // date + hours
                        $t1 = preg_match('|^([0-9]{2})/([0-9]{2})/([0-9]{4})(?:\s[0-9]{2}:[0-9]{2})?$|', $this->_value, $match1);

                        $t2 = preg_match('|^([0-9]{4})-([0-9]{2})-([0-9]{2})(.*)$|', $this->_value, $match2);

                        if (!$t1 && !$t2)
                                return 'error.invalid';

                        if (isset($this->_options['inTheFuture']) && $this->_options['inTheFuture']) {
                                if ($t1)
                                        $date = $match1[3] . '-' . $match1[2] . '-' . $match1[1];
                                if ($t2)
                                        $date = $match2[1] . '-' . $match2[2] . '-' . $match2[3];
                                if (strtotime($date) < time())
                                        return 'error.date.inthefuture';
                        }
                        return true;
                }

                /**
                 * when writing object from database
                 * @param type $value
                 */
                public function setValueFromDatabase($value) {
                        if (isset($value))
                                $this->_value = strtotime(str_replace('+0000', '', $value));
                }

                public function toInput() {
                        $this->_htmlAttributes['class'] = 'date';

                        if (isset($this->_value))
                                $this->_htmlAttributes['value'] = strpos($this->_value, '/') !== false ? $this->_value : $this->format('d/m/Y');
                        return '<input ' . $this->getHtmlAttributesToHtml() . ' />';
                }

                public function toHtml() {
                        $v = $this->getValue();
                        return isset($v) ? date('d/m/Y' . (isset($this->_options['showHours']) && !$this->_options['showHours'] ? '' : ' h:i'), $v) : '';
                }

                public function format($f) {
                        $v = $this->getValue();
                        return isset($v) ? date($f, $v) : '';
                }

                public function getValueForDatabase() {
                        if ($this->_value instanceof \CRUDsader\Expression)
                                return $this->_value;

                        if ($this->isEmpty())
                                return null;

                        if (ctype_digit($this->_value))
                                return date('Y-m-d H:i:s', $this->_value);


                        if (preg_match('|^([0-9]{2})/([0-9]{2})/([0-9]{4})(?:\s([0-9]{2}\:[0-9]{2}))?$|', $this->_value, $match)) {
                                
                                return $match[3] . '-' . $match[2] . '-' . $match[1] . ' ' . (isset($match[4]) ? $match[4] : '');
                        }
                        $ex = explode('/', (string) $this->_value);
                        
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
                        return null;
                }
        }
}