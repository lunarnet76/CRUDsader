<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Adapter\I18n\Translation {
    /**
     * Translations are stored in an ini file
     * @package CRUDsader\Adapter\I18n\Translation
     */
    class Ini extends \CRUDsader\Adapter {

        /**
         * constructor
         */
        public function init() {
            $lines = @file($this->_configuration->file);
            $configuration = array();
            $section = false;
            foreach ($lines as $line) {
                switch ($line[0]) {
                    case '#':break; // comments
                    case '[':// namespace
                        if (!preg_match('|^\[([^\:\]\s]*)(\:([^\]\s\:]*)){0,1}\]\s*$|', $line, $match))
                            throw new ConfigurationException('file "' . $filePath . '":' . $lineNumber . ' error :"' . $line . '" is not a proper namespace');

                        if ($section && $namespace == $section) {
                            break 2;
                        }
                        $namespace = $match[1];
                        $configuration[$namespace] = array();
                        if (isset($match[3])) {
                            if (!isset($configuration[$match[3]]))
                                throw new \CRUDsader\Exception('section "' . $namespace . '" cannot inherit from unexistant section "' . $match[3] . '"');
                            $configuration[$namespace] = $configuration[$match[3]];
                        }
                        break;
                    default:// good
                        if (preg_match('|([^=]*)=(.*)|', $line, $match)) {
                            $configuration[$namespace][$match[1]] = $match[2];
                        }
                }
            }
            $this->_translations = $configuration[$namespace];
        }

        /**
         * translate given index
         * @param string|array $index
         * @param string $glue for imploding if $index is an array
         * @return string 
         */
        public function translate($index, $glue=',') {
            if (is_array($index)) {
                $ret = array();
                foreach ($index as $k => $v) {
                    $ret[] = $this->translate($v);
                }
                return implode($glue, $ret);
            }
            if (isset($this->_translations[$index]))
                return $this->_translations[$index];
            return '{' . $index . '}';
        }
    }
}