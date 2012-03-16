<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader {
	/**
	 * Debug tools
	 * @package CRUDsader
	 */
	class Debug extends MetaClass {
		/**
		 * identify the class
		 * @var string
		 */
		protected $_classIndex = 'debug';

		/**
		 * shortcut for database profiler
		 * return string
		 */
		public function profileDatabase()
		{
			
			if (\CRUDsader\Instancer::getInstance()->database->hasDependency('profiler'))
				return \CRUDsader\Instancer::getInstance()->database->getDependency('profiler')->display();
		}
		
		

		public function log($v,$file = 'debug.log')
		{
			ob_start();
			var_dump($v);
			file_put_contents($file, ob_get_clean(),FILE_APPEND);
		}

		public static function errorHandler($errno, $errstr, $errfile, $errline, $context)
		{
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
			if (\CRUDsader\Instancer::getInstance()->debug->getConfiguration()->error && $show) {
				self::showError(debug_backtrace(), $output, $errstr);
				//  self::pre(array('Message' => $errstr, 'File' => $errfile, 'Line' => $errline/*, 'Context' => $context*/),$output);
			}
			if ($exit)
				exit;
			return true; // true disable PHP built-in error handler
		}
                
                public function sql(){
                    echo \CRUDsader\Instancer::getInstance()->database->highLight(\CRUDsader\Instancer::getInstance()->database->getSQL());
                }

		protected static function preCallback($Parts)
		{
			// echo '<pre>';print_r(func_get_args());echo '</pre>';
			return '[<font color="#cc0000">\'' . $Parts[1] . '\'</font>]';
		}

		public static function _pre($data)
		{
			$out = print_r($data, true);

			// replace something like '[element] => <newline> (' with <a href="javascript:toggleDisplay('...');">...</a><div id="..." style="display: none;">
			$out = preg_replace('/([ \t]*)(\[[^\]]+\][ \t]*\=\>[ \t]*[a-z0-9 \t_]+)\n[ \t]*\(/iUe', "'\\1<a href=\"javascript:toggleDisplay(\''.(\$id = substr(md5(rand().'\\0'), 0, 7)).'\');\">\\2</a><div id=\"'.\$id.'\" style=\"display: none;\">'", $out);

			// replace ')' on its own on a new line (surrounded by whitespace is ok) with '</div>
			$out = preg_replace('/^\s*\)\s*$/m', '</div>', $out);

			// print the javascript function toggleDisplay() and then the transformed output
			echo '<script language="Javascript">function toggleDisplay(id) { document.getElementById(id).style.display = (document.getElementById(id).style.display == "block") ? "none" : "block"; }</script>' . $out;
		}

		public static function print_r($v)
		{
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

		public static function pre($v, $title = '', $color = '#E9E9E9')
		{

			$debug_backtrace = debug_backtrace();
			$d = $debug_backtrace[0]['args'][0];

			echo '<div class="debug" style="background-color:' . $color . ';border-top:1px solid black;margin-bottom:5px">' . ($title != '' ? '<h3>' . $title . '</h3>' : ''); //(isset($d['File'])?$d['File'] . ':' . $d['Line']:'')
			if (!is_array($v) && !is_object($v)) {
				if (strpos($v, 'SELECT') === 0) {
					echo \CRUDsader\Instancer::getInstance()->database->highLight($v);
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

				if (strlen($content) > 10000)
					return substr($content, 0, 10000);
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

		public static function getMemoryUsage()
		{
			$size = memory_get_usage(true);
			$unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
			return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
		}

		public static function showError($trace, $type = false, $message = false)
		{
			echo '<style type="text/css">
                body{
                margin:0px;}
                div.php-error{
                    background-color:white;
                    width:100%;
                    margin:0px;
                    font-family: "Courier New",Courier,monospace;
                    font-size: 8pt;
                }
               
                .php-error-block{
                    border-bottom:1px dashed grey;
                    margin: 15px 15px 15px 10px;
                }
                .php-error-part-class{
                    background-color: #D3E0EB;
                    color: #7B8D9A;
                    font-weight: bold;
                    width:100%;
                    height:24px;
                }
                .php-error-class{
                    padding:5px;
                    background-color: #10759C;
                    border-left: 1px solid #D3E0EB;
                    border-top: 1px solid #D3E0EB;
                    color: #FFFFFF;
                    float: left;
                    font-size: 8pt;
                }
                .php-error-function{
                    float: left;
                    padding:5px;
                    background-color: #D3E0EB;
                    color: #334C60;
                }
                .php-error-file{
                     background-color: #D3E0EB;
                    color: #334C60;
                    padding: 2pt 0;
                }
                .php-error-line{
                    color: #ae1414;
                    font-weight:bold;
                    margin-left:5px;
                }
                .php-error-arg{
                   
                }
                .php-error-arg-string{
                    color:red;
                }
                .php-error-arg-null{
                    color:green;
                }
                .php-error-arg-object{
                    color:green
                }
                .php-error-arg-array{
                    color:green
                }
                 .php-error-top{
                     background-color: #10759C;
                        border-bottom: 1px solid #0E698B;
                        
                        text-align: left;
			color: white;
			font-size:30px;
			padding:5px;
                }
                .php-error-title{
			color:black !important;
                }
                .php-error-type{
                    color: #0E698B;
                    display: block;
                    font-size: 60pt;
                    font-weight: bold;
                    margin: -20pt -8pt 0 0;
                    padding: 0;
                }
                .php-error-separator{
                    background-color: #334C60;
                    border-bottom: 1px solid #617789;
                    border-top: 2px solid #2E4456;
                    color: #C8EFFA;
                    font-size: 9px;
                    padding: 1px 5px 3px 10px;
                }
                .php-error-head{
                    border-bottom: 1px dotted #ADBAC6;
                    color: #0088B5;
                    font-size: 16pt;
                    margin: 15px 15px 15px 10px;
                }
                </style>';
			echo '<div class="php-error-separator"></div>
                    <div class="php-error">
                        <div class="php-error-top">
                            ' . $message . '
                        </div>
                        <div class="php-error-separator"></div>';
			foreach ($trace as $i => $t) {
				if ($i == 0) {
					if (isset($t['file'])) {
						$file = $trace[1]['file'] = $t['file'];
						$line = $trace[1]['line'] = $t['line'];
					}
					continue;
				}
				if ($i == 1) {
					if (isset($file)) {
						$t['file'] = $file;
						$t['line'] = $line;
					}
					echo '<div class="php-error-head">Main</div>';
				}
				echo '<div class="php-error-block">';
				if (isset($t['class'])) {
					echo '<div class="php-error-part-class"><span class="php-error-class">' . $t['class'] . '</span> ' . ($t['type'] == '::' ? 'static' : '') . ' <span class="php-error-function">' . $t['function'] . '</span></div>';
				}
				if (isset($t['file']))
					echo '<div class="php-error-part-file"><span class="php-error-line">' . $t['line'] . '</span> <span class="php-error-file">' . (strpos($t['file'], __DIR__) !== false ? substr($t['file'], strlen(__DIR__ . '/')) : $t['file']) . '</span></div>';
				if (!empty($t['args'])) {
					echo '<ul class="php-error-args">';
					foreach ($t['args'] as $i=>$arg) {
						echo '<li class="php-error-arg">';
						if(isset($t['class']) && $t['class']=='CRUDsader\Database' && $t['function'] == 'query' && $i==0){
							echo sl()->database->highLight($arg);
						}
						else if (is_object($arg)) {
							echo '<span class="php-error-arg-object">' . get_class($arg) . '</span>';
						} elseif (is_null($arg)) {
							echo '<span class="php-error-arg-null">NULL</span>';
						} elseif (is_array($arg)) {
							echo '<span class="php-error-arg-array">Array(' . count($arg) . ')</span>';
						} elseif (is_string($arg)) {
							echo '<span class="php-error-arg-string">"' . ($arg) . '"</span>';
						} else {
							echo (string) $arg;
						}
						echo '</li>';
					}
					echo '</ul>';
				}
				echo '</div>';
				if ($i == 1) {
					echo '<div class="php-error-head">Stack Trace</div>';
				}
			}
			echo '</div><div class="php-error-separator"></div></div>';
		}
	}
}
