<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader {
        /**
         * Internalization and Localization
         * @package   CRUDsader
         */
        class I18n extends MetaClass {
                /**
                 * @var string
                 */
                protected $_classIndex = 'i18n';

                /**
                 * @var string
                 */
                protected $_language = 'en';

                /**
                 * the list of dependencies
                 * @var array
                 */
                protected $_hasDependencies = array('translation');

                /**
                 * @param Block $configuration
                 */
                public function __construct() {
                        parent::__construct();
                        date_default_timezone_set($this->_configuration->timezone);
                        $this->_language = $this->_configuration->language;
                }
                
                public function getLanguage(){
                        return $this->_language;
                }

                /**
                 * shortcuts
                 * @param string $name
                 * @param array $arguments
                 * @return mix 
                 */
                public function __call($name, $arguments) {
                        switch ($name) {
                                case 'translate':
                                        $arguments[] = $this->_language;
                                        return call_user_func_array(array($this->_dependencies['translation'], $name), $arguments);
                                        break;
                                default:
                                        throw new I18nException('call to undefined function "' . $name . '"');
                        }
                }
        }
        class I18nException extends \CRUDsader\Exception {
                
        }
}