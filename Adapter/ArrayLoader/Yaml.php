<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Adapter\ArrayLoader {
    /**
     * load a pseudo YAML file, not really the official standard
     * @package CRUDsader\Adapter\ArrayLoader
     */
    class Yaml extends \CRUDsader\Adapter\ArrayLoader {
        
        protected $_arrayLoaded;
        protected $_sectionLoaded=false;
        
        public function __construct($options=null){
            if(!is_array($options) || !isset($options['file']))
                throw new YamlException('you must specify a file');
            $filePath=$options['file'];
            $this->_sectionLoaded=$section=isset($options['section'])?$options['section']:false;
            $lines = file($filePath);
            if ($lines === false)
                throw new YamlException('file "' . $filePath . '" could not be read properly');
            $configuration = array();
            $depths = array();
            $lastDepth = $depth = 0;
            $namespace = false;
            foreach ($lines as $lineNumber => $line) {
                switch ($line[0]) {
                    case '#':break; // comments
                    case '[':// namespace
                        if (!preg_match('|^\[([^\:\]\s]*)(\:([^\]\s\:]*)){0,1}\]\s*$|', $line, $match))
                            throw new YamlException('file "' . $filePath . '":' . $lineNumber . ' error :"' . $line . '" is not a proper namespace');

                        if ($section && $namespace == $section) {
                            break 2;
                        }
                        $namespace = $match[1];
                        $configuration[$namespace] = array();
                        if (isset($match[3])) {
                            if (!isset($configuration[$match[3]]))
                                throw new YamlException('section "' . $namespace . '" cannot inherit from unexistant section "' . $match[3] . '"');
                            $configuration[$namespace] = $configuration[$match[3]];
                        }
                        break;
                    default:// config line
                        if (preg_match('|^(\s*)([^:=]*)([:=])(.*)\s*$|', $line, $match)) {// key:
                            $depth = strlen($match[1]) / 4;
                            $name = $match[2];
                            if ($depth == 0) {// depth 0
                                if (!isset($configuration[$namespace][$match[2]]))
                                    $configuration[$namespace][$match[2]] = array();
                                $depths[$depth] = &$configuration[$namespace][$match[2]];
                            } else if ($depth == $lastDepth) {// same depth
                                if (!isset($depths[$depth - 1][$match[2]]))
                                    $depths[$depth - 1][$match[2]] = array();
                                $depths[$depth] = &$depths[$depth - 1][$match[2]];
                            } else if ($depth > $lastDepth) {// >
                                if (!isset($depths[$lastDepth][$match[2]]))
                                    $depths[$lastDepth][$match[2]] = array();
                                $depths[$depth] = &$depths[$lastDepth][$match[2]];
                            } else {
                                $depths[$depth] = &$depths[$depth - 1][$match[2]];
                            }
                            $lastDepth = $depth;
                            if ($match[3]=='=') {
                                $depths[$depth] = rtrim($match[4]);
                            }
                        }
                }
            }
            if ($section && !isset($configuration[$section]))
                throw new YamlException('section "' . $section . '" does not exist');
            $this->_arrayLoaded=$configuration;
        }
        
        public function get(){
            return $this->_sectionLoaded ? $this->_arrayLoaded[$this->_sectionLoaded] : $this->_arrayLoaded;
        }
    }
    class YamlException extends \CRUDsader\Exception{
        
    }
}