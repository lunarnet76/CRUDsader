<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\ArrayLoader {
    /**
     * load a pseudo YAML file, not really the official standard
     * @package CRUDsader\ArrayLoader
     * @test ArrayLoader\Yaml_Test
     */
    class Yaml extends \CRUDsader\ArrayLoader {
        /**
         *
         * @param array $options
         * @return array 
         * @test test_load
         */
        public function load(array $options){
            if(!isset($options['file']))
                throw new YamlException('you must specify a file');
            $filePath=$options['file'];
            $section=isset($options['section'])?$options['section']:false;
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
                            throw new YamlException('file "' . $filePath . '":' . ($lineNumber+1) . ' error :"' . $line . '" is not a proper namespace');

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
                            $depth = strlen($match[1]);
			    if($depth%4)
				    throw new YamlException('depth separator must be 4 spaces and not '.($depth).' at line '.($lineNumber+1).':'.$match[0]);
			    $depth=$depth/4;
                            $name = $match[2];
                            if ($depth == 0) {// depth 0
                                if (!isset($configuration[$namespace][$name]))
                                    $configuration[$namespace][$name] = array();
                                $depths[$depth] = &$configuration[$namespace][$name];
                            } else if ($depth == $lastDepth) {// same depth
                                if (!isset($depths[$depth - 1][$name]))
                                    $depths[$depth - 1][$name] = array();
				if(!isset($depths[$depth - 1]) || !is_array($depths[$depth - 1])){
					throw new YamlException('error: should be at depth '.($depth - 1).' at line '.($lineNumber+1).':'.$line);
				}
                                $depths[$depth] = &$depths[$depth - 1][$name];
                            } else if ($depth > $lastDepth) {// >
                                if (!isset($depths[$lastDepth][$name]))
                                    $depths[$lastDepth][$name] = array();
                                $depths[$depth] = &$depths[$lastDepth][$name];
                            } else {
                                $depths[$depth] = &$depths[$depth - 1][$name];
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
            return isset($configuration[$section])?$configuration[$section]:$configuration;
        }
    }
    class YamlException extends \CRUDsader\Exception{
        
    }
}