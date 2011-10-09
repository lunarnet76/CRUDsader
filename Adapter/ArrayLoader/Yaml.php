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
        public function load($options=null){
            if(!is_array($options) || !isset($options['file']))
                throw new YamlException('you must specify a file');
            $filePath=$options['file'];
            $section=isset($options['section'])?$options['section']:false;
            $lines = file($filePath);
            if ($lines === false)
                throw new ConfigurationException('file "' . $filePath . '" could not be read properly');
            $configuration = array();
            $depths = array();
            $lastDepth = $depth = 0;
            $namespace = false;
            foreach ($lines as $lineNumber => $line) {
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
                                throw new ConfigurationException('section "' . $namespace . '" cannot inherit from unexistant section "' . $match[3] . '"');
                            $configuration[$namespace] = $configuration[$match[3]];
                        }
                        break;
                    default:// config line
                        if (preg_match('|^(\s*)([^:]*)\:\s*$|', $line, $match)) {// key:
                            $depth = strlen($match[1]) / 4;
                            $name = $match[2];
                            if ($depth == 0) {
                                if (!isset($configuration[$namespace][$match[2]]))
                                    $configuration[$namespace][$match[2]] = array();
                                $depths[$depth] = &$configuration[$namespace][$match[2]];
                            } else if ($depth == $lastDepth) {
                                if (!isset($depths[$depth - 1][$match[2]]))
                                    $depths[$depth - 1][$match[2]] = array();
                                $depths[$depth] = &$depths[$depth - 1][$match[2]];
                            } else if ($depth > $lastDepth) {
                                if (!isset($depths[$lastDepth][$match[2]]))
                                    $depths[$lastDepth][$match[2]] = array();
                                $depths[$depth] = &$depths[$lastDepth][$match[2]];
                            } else {
                                if(!isset($depths[$lastDepth - $depth - 1][$match[2]]))
                                    $depths[$lastDepth - $depth - 1][$match[2]] = array();
                                $depths[$depth] = &$depths[$lastDepth - $depth - 1][$match[2]];
                            }
                            $lastDepth = $depth;
                        } else if (preg_match('|^(\s*)([^:]*)\=\s*(.*)$|', $line, $match)) {// key: value
                            if (strlen($match[1]) / 4 == 0) {
                                $configuration[$namespace][$match[2]] = rtrim($match[3]);
                            }
                            else
                                $depths[$depth][$match[2]] = rtrim($match[3]);
                        }else {// is a value
                            $depths[$depth][] = $line;
                        }
                }
            }
            if ($section && !isset($configuration[$section]))
                throw new ConfigurationException('section "' . $section . '" does not exist');
            return ($section ? $configuration[$section] : $configuration);
        }
    }
    class YamlException extends \CRUDsader\Exception{
        
    }
}