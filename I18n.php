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
                 * @var string, configured
                 */
                protected $_locale;

                /**
                 * @var string, configured
                 */
                protected $_language;

                /**
                 * @var string, configured
                 */
                protected $_timezone;

                /**
                 * @var \CRUDsader\Session
                 */
                protected $_session;

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
                        $this->_session = \CRUDsader\Session::useNamespace('i18n');
                }

                /**
                 * @param \CRUDsader\Block $block 
                 * @test test_configuration
                 */
                public function setConfiguration(\CRUDsader\Block $block = null) {
                        $this->_configuration = $block;

                        $language = $block->language;
                        $timezone = $block->timezone;

                        switch ($block->locale) {
                                case 'en_GB':
                                        $language = 'en';
                                        break;
                        }
                        $this->_language = isset($this->_session->language) ? $this->_session->language : $language;
                        $this->_timezone = $timezone;
                        date_default_timezone_set($timezone);
                }

                public function getLanguage() {
                        return $this->_language;
                }

                public function setLanguage($language, $useSession = true) {
                        $this->_language = $this->_session->language = $language;
                }

                public function getTimezone() {
                        return $this->_timezone;
                }

                public function getLocale() {
                        return $this->_locale;
                }

                public function detectLanguage($availableLanguages = array(), $default = false, $useSession = true) {
                        if ($useSession && isset($this->_session->language)) {
                                $this->setLanguage($this->_session->language);
                        } else if (!empty($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
                                // regex borrowed from Gabriel Anderson on http://stackoverflow.com/questions/6038236/http-accept-language
                                preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER["HTTP_ACCEPT_LANGUAGE"], $lang_parse);
                                $langs = $lang_parse[1];
                                $ranks = $lang_parse[4];

                                $getRank = function ($j)use(&$getRank, &$ranks) {
                                                while (isset($ranks[$j]))
                                                        if (!$ranks[$j])
                                                                return $getRank($j + 1);
                                                        else
                                                                return $ranks[$j];
                                        };

                                $lang2pref = array();
                                for ($i = 0; $i < count($langs); $i++)
                                        $lang2pref[$langs[$i]] = (float) $getRank($i);

                                $language = $default;
                                if (empty($lang2pref)) {
                                        $language = $default ? $default : $this->_language;
                                } else {

                                        // (comparison function for uksort)
                                        $cmpLangs = function ($a, $b) use ($lang2pref) {
                                                        if ($lang2pref[$a] > $lang2pref[$b])
                                                                return -1;
                                                        elseif ($lang2pref[$a] < $lang2pref[$b])
                                                                return 1;
                                                        elseif (strlen($a) > strlen($b))
                                                                return -1;
                                                        elseif (strlen($a) < strlen($b))
                                                                return 1;
                                                        else
                                                                return 0;
                                                };

                                        // sort the languages by prefered language and by the most specific region
                                        uksort($lang2pref, $cmpLangs);

                                        if (!empty($availableLanguages)) {
                                                $availableLanguages = array_flip($availableLanguages);
                                                foreach ($lang2pref as $k => $v) {
                                                        if (isset($availableLanguages[$k])) {
                                                                $language = $k;
                                                                break;
                                                        }
                                                }
                                        }else
                                                $language = key($lang2pref);
                                }
                                if ($language) {
                                        $this->_language = $language;
                                        if ($useSession)
                                                $this->setLanguage($language, true);
                                }
                        }
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