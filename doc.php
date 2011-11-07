<html><body>
        <?php if(!isset($_REQUEST['class'])){?>
        <script src="jquery-1.6.4.min" type="text/javascript"></script>
        <script  type="text/javascript">
            $(document).ready(function(){
                $('a').live('click',function(e){
                    $('#error-popup').hide();
                    var pos=String(this).indexOf('javascript:');
                    if(pos==-1){
                        var pos=String(this).indexOf('dl:');
                        if(pos==-1){
                            if(this.href)
                                $.ajax({
                                    url: this.href,
                                    success:function(data){
                                        $('#content').html(data);
                                    }
                            });
                        }else{
                            downloadFile('http://'+ this.href.substr(12));
                        }
                    }else{
                        eval(String(this).substr(11));
                    }
                    e.preventDefault();
                    return false;
                });
            });
        </script> 
        <?php }
        
        echo '                
<style type="text/css">
    div.php-namespace{
        padding:15px;
    }
    div.php-class{
        padding:15px;
    }
    div.php-function{
        padding:15px;
    }
.php-tag{
    color:red;
    font-weight:bold;
}
.php-class-keyword{
    color:green;
    font-weight:bold;
}
.php-doc{
color:orange
}
.php-test{
    color:#ae1414;
}
.php-var{
font-weight:bold;
}
.php-class{
font-weight:bold
}
a{
color:#ae1414;
}</style>';

        function _readDir($dir) {
            $ret = array( substr($dir, 10).'/namespace');
            if ($handle = opendir($dir)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file == '.' || $file == '..')
                        continue;
                    if (strpos($file, '.') === false) {
                        $ret = array_merge($ret, _readDir($dir . '/' . $file));
                    } else {
                        if (substr($file, -4) == '.php')
                            $ret[] = str_replace('/', '\\', substr($dir, 10) . ($dir == 'CRUDsader' ? 'A/' : '/') . substr($file, 0, -4));
                    }
                }
                closedir($handle);
            }
            return $ret;
        }

        function readAction() {
            require('CRUDsader/Autoload.php');
            \CRUDsader\Autoload::register();
            $reader = new \ReflectionClass('\\CRUDsader\\' . $_REQUEST['class']);

            // namespace
            $namespace = $reader->inNamespace() ? $reader->getNamespaceName() : false;
            // class
            $interface = $reader->isInterface();
            $abstract = $reader->isAbstract();
            $class = $reader->getName();
            // extends
            $parentClass = $reader->getParentClass() ? $reader->getParentClass()->getName() : false;
            // implements
            $interfaces = ($reader->getInterfaceNames());
            $classDoc = $reader->getDocComment();
            $methods = $reader->getMethods();

            $t = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            echo '<code><span class="php-tag">&lt;?php</span><br/>';
            if ($namespace)
                echo '<div class="php-namespace"><span class="php-class-keyword">namespace</span> <a  href="?namespace=' . $namespace . '">' . $namespace . '</a> {<br/>';
            echo '<div class="php-class"><span class="php-doc">' . $classDoc . '</span><br/>';
            if ($interface) {
                echo '<span class="php-class-keyword">interface</span> ';
            } else {
                if ($abstract)
                    echo '<span class="php-class-keyword">abstract</span> ';
                echo '<span class="php-class-keyword">class</span>';
            }
            echo ' <span class="php-class">' . $class . '</span>';
            if ($parentClass)
                echo '<br/><span class="php-class-keyword">extends</span> <a  href="?class=' . $parentClass . '">' . $parentClass . '</a>';
            if (!empty($interfaces)) {
                echo '<br/><span class="php-class-keyword">implements</span><br/>';
                foreach ($interfaces as $interface)
                    echo $t.'<a  href="?class=' . $interface . '">' . $interface . '</a><br/>';
            }
            echo '<br/>{';
            foreach ($methods as $method) {
                $paramsHtml = array();
                echo '<div class="php-function">';
                $doc = $method->getDocComment();
                $test = false;
                if (preg_match_all('$\@(param|test|access)\s*([^\@\*]*)$', $doc, $matches)) {
                    foreach ($matches[1] as $index => $type) {
                        switch ($type) {
                            case 'test':
                                $test = $matches[2][$index];
                                break;
                            case 'param':
                                $ex = explode(' ', $matches[2][$index]);
                                $paramsHtml[$ex[1]] = '<span class="php-type">' . $ex[0] . '</span> <span class="php-var">' . $ex[1] . '</span>';
                                break;
                        }
                    }
                }
                $comment = substr($doc, 0, strpos($doc, '@'));
                echo '<span class="php-doc">' . $comment . '</span><br/>';
                if ($test)
                    echo '<span class="php-test php-test-green">' . $test . '</span><br/>';
                else
                    echo '<span class="php-test php-test-red">UNTESTED</span><br/>';
                if ($method->isPublic())
                    echo '<span class="php-class-keyword">public</span>';
                else if ($method->isProtected())
                    echo '<span class="php-class-keyword">protected</span>';
                else if ($method->isPrivate())
                    echo '<span class="php-class-keyword">protected</span>';
                else
                    echo $t;
                echo ' <span class="php-class-keyword">function</span> ' . $method->getName() . '(';
                $params = array();
                foreach ($method->getParameters() as $m) {
                    $name = '$' . $m->getName();
                    $params[] = isset($paramsHtml[$name]) ? $paramsHtml[$name] : '<span class="php-var">' . $name . '</span>';
                }
                echo implode(',', $params), ')';
                echo '</div>';
            }
            echo '}</div>';
            if ($namespace)
                echo '}</div>';
            echo '</code>';
        }

        function defaultAction() {

            $_REQUEST['namespace'] = isset($_REQUEST['namespace']) ? $_REQUEST['namespace'] : 'CRUDsader';
            $last = substr($_REQUEST['namespace'], 0, strrpos($_REQUEST['namespace'], '\\'));
            echo '<table  valign="top"><tr><td><h1>' . $_REQUEST['namespace'] . '</h1>';
            echo '<h3><a  href="doc/?namespace=' . $last . '">' . $last . '</a></h3><br/>';
            $dir = str_replace('\\', '/', $_REQUEST['namespace']);
            $l = strlen($dir);
            $classes = _readDir($dir);
            $classes[]='A/namespace';
            sort($classes);
            foreach ($classes as $class) {
                $sls=count(explode('/', $class));
                if (strpos($class, '/namespace') !== false) {
                    if($class=='A/namespace')
                        echo  '<h3>ROOT</h3>';
                    else
                    if ($sls == 2)
                        echo '<h3>' . substr($class, 0, -10) . '</h3>';
                }else
                    echo '<a  href="doc/?class=' . $class . '">' . $class . '</a><br/>';
            }
            echo '</td><td valign="top" id="content"></td></tr></table>';
        }
        if (isset($_REQUEST['class']))
            readAction();
        else
            defaultAction();

    

