<?php
/**
 * provide some debug and dump tools
 *
 * LICENSE: see CRUDsader/license.txt
 *
 * @author Jean-Baptiste Verrey <jeanbaptiste.verrey@gmail.com>
 * @copyright  2010 Jean-Baptiste Verrey
 * @license    http://www.CRUDsader.com/license/2.txt
 * @version    $Id$
 * @link       http://www.CRUDsader.com/manual/
 * @since      2.0
 */
/**
 * @category   Configuration
 * @package    CRUDsader2
 */
namespace CRUDsader {
    class Debug {
        protected static $_configuration;
        protected static $_chrono = array();

        public static function isActivated() {
            return self::$_configuration->php;
        }

        public static function setConfiguration(CRUDsader_Block $configuration) {
            self::$_configuration = $configuration;
        }

        public static function databaseProfiler() {
            if (self::$_configuration->database) {
                $db = CRUDsader_Database::getInstance();
                // display DB profiler
                if ($db->hasProfiler())
                    $db->getProfiler()->display();
            }
        }

        public static function chrono_start($index='script', $microtime=true) {
            if (!self::$_configuration->php)
                return;
            if ($microtime)
                self::$_chrono[$index] = microtime(true);
            else
                self::$_chrono[$index] = time(true);
        }

        public static function chrono_time($index='script', $microtime=true) {
            if (!self::$_configuration->php)
                return;
            if ($microtime)
                $end = microtime(true);
            else
                $end=time(true);
            return isset(self::$_chrono[$index]) ? round($end - self::$_chrono[$index], 4) : '';
        }

        protected static function preCallback($Parts) {
            // echo '<pre>';print_r(func_get_args());echo '</pre>';
            return '[<font color="#cc0000">\'' . $Parts[1] . '\'</font>]';
        }

        public static function _pre($data) {
            $out = print_r($data, true);

            // replace something like '[element] => <newline> (' with <a href="javascript:toggleDisplay('...');">...</a><div id="..." style="display: none;">
            $out = preg_replace('/([ \t]*)(\[[^\]]+\][ \t]*\=\>[ \t]*[a-z0-9 \t_]+)\n[ \t]*\(/iUe', "'\\1<a href=\"javascript:toggleDisplay(\''.(\$id = substr(md5(rand().'\\0'), 0, 7)).'\');\">\\2</a><div id=\"'.\$id.'\" style=\"display: none;\">'", $out);

            // replace ')' on its own on a new line (surrounded by whitespace is ok) with '</div>
            $out = preg_replace('/^\s*\)\s*$/m', '</div>', $out);

            // print the javascript function toggleDisplay() and then the transformed output
            echo '<script language="Javascript">function toggleDisplay(id) { document.getElementById(id).style.display = (document.getElementById(id).style.display == "block") ? "none" : "block"; }</script>' . $out;
        }

        public static function print_r($v) {
            foreach ($v as $k => $v) {
                if (is_array($v))
                    echo '[array]$k(' . count($v) . ')' . "\r\n";
                else if (is_object($v)) {
                    echo '[object]' . get_class($v) . "\r\n";
                    foreach ($v as $k => $v)
                        self::print_r($v);
                }else
                    echo $v;
            }
        }

        public static function pre($v, $title='', $color='#E9E9E9') {
            
            $debug_backtrace = debug_backtrace();
            $d = $debug_backtrace[0]['args'][0];

            echo '<div class="debug" style="background-color:' . $color . ';border-top:1px solid black;margin-bottom:5px">' . ($title != '' ? '<h3>' . $title . '</h3>' : ''); //(isset($d['File'])?$d['File'] . ':' . $d['Line']:'')
            if (!is_array($v) && !is_object($v)) {
                if (strpos($v, 'SELECT') === 0) {
                    echo \CRUDsader\Database::getInstance()->highLight($v);
                } else {
                    echo gettype($v) . ' : ';
                    print_r($v);
                }
            } else {
                /* if(extension_loaded('xdebug')){
                  ob_start();
                  var_dump($v);
                  echo preg_replace('|<font color="#ae1414"><b>Array</b></font>(\s+)|','<font color="#ae1414"><b>Array</b></font>',str_replace(
                  array(
                  "\t(",
                  "<b>array</b>",
                  'protected',
                  '<b>object</b>',
                  ")\n\n",
                  '    )',
                  '<font color=\'#888a85\'>=&gt;</font>'
                  ), array(
                  '',
                  '<font color="#ae1414"><b>Array</b></font> &nbsp; ',
                  '<font color="green"><b>protected</b></font>',
                  ' <font color="#ae1414"><b>Object</b></font>',
                  ")\r",

                  ')',' <font color="blue"><b>=&gt;</b></font>'
                  ), ob_get_clean()));
                  echo '</div>';
                  return;
                  } */

                // ob_start();
                echo '<pre>';
                print_r($v);
                echo '</pre>';
                return;
                // $content=ob_get_clean();

                if (strlen($content) > 100000)
                    return substr($content, 0, 100000);
                $content = preg_replace_callback('/\[([^\]]*)\]/', array('CRUDsader_Debug', 'preCallback'), $content);

                echo preg_replace('|<font color="#ae1414"><b>Array</b></font>(\s+)|', '<font color="#ae1414"><b>Array</b></font>', str_replace(
                                array(
                            "\t(",
                            "array\n",
                            'protected',
                            'object(',
                            ")\n\n",
                            '    )'
                                ), array(
                            '',
                            '<font color="#ae1414"><b>Array</b></font>',
                            '<font color="green"><b>protected</b></font>',
                            ' <font color="#ae1414"><b>Object</b></font>(',
                            ")\r", ')'
                                ), $content)
                );
            }
            echo '</div>';
        }

        public static function errorHandler($errno, $errstr, $errfile, $errline, $context) {
            if (error_reporting() == 0) {// @ errors
                return;
            }
            $exit = false;
            $show = true;
            switch ($errno) {
                case E_ERROR :$output = 'Fatal run-time error';
                    $exit = true;
                    break;
                case E_WARNING :$output = 'Run-time warning';
                    break;
                case E_PARSE :$output = 'Compile-time parse errors';
                    break;
                case E_NOTICE :$output = 'Run-time notice';
                    break;
                case E_CORE_ERROR :$output = 'Fatal errors that occur during PHP\'s initial startup';
                    $exit = true;
                    break;
                case E_CORE_WARNING :$output = 'Warning';
                    break;
                case E_COMPILE_ERROR :$output = 'Fatal compile-time errors';
                    $exit = true;
                    break;
                case E_COMPILE_WARNING :$output = 'Compile-time warning';
                    break;
                case E_USER_ERROR :$output = 'User-generated error';
                    $exit = true;
                    break;
                case E_USER_WARNING :$output = 'User-generated warning';
                    break;
                case E_USER_NOTICE :$output = 'User-generated notice';
                    break;
                case E_STRICT :$output = 'User-generated strict notice';
                    $show = false;
                    break;
                case E_RECOVERABLE_ERROR :$output = 'Catchable fatal error';
                    $exit = true;
                    break;
                case E_DEPRECATED :$output = 'will not work in future versions';
                    break;
                case E_USER_DEPRECATED :$output = 'User-generated code will not work in future versions';
                    break;
                case E_ALL :$output = 'FATAL ERROR';
                    break;
                default:
                    $output = 'UNKNOWN ERROR';
                    break;
            }
            if (self::$_configuration->php && $show)
                self::pre(array('Message' => $errstr, 'File' => $errfile, 'Line' => $errline, 'Context' => $context), $output);
            if ($exit) {
                if (self::$_configuration->php) {
                    echo 'exit';
                    self::pre(debug_backtrace());
                }
                exit;
            }
            return true; // true disable PHP built-in error handler
        }

        public static function exitException($e) {
            if (!self::$_configuration->php)
                return;
            ob_start();
            pre($e, get_class($e), 'white');
            $content = ob_get_clean();
            return str_replace(array(
                'message:',
                '['
                    ), array(
                '<font color="#ae1414" style="font-weight:bold">message:',
                '</font>['
                    ), $content) . self::stop();
        }

        public static function getMemoryUsage() {
            $size = memory_get_usage(true);
            $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
            return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
        }
    }
}
