<html><body>
        <?php if (!isset($_REQUEST['class'])) { ?>
            <script src="lib/jquery-1.7.1.min.js" type="text/javascript"></script>
            <script  type="text/javascript">
                $(document).ready(function(){
                    $('a').live('click',function(e){
                        if($(this).attr('target'))
                            return;
                        if(this.href){
                            
                            $.ajax({
                                url: this.href,
                                success:function(data){
                                    $('#content').html(data);
                                    $('#content').css('margin-top',$(document).scrollTop());
                                }
                            });
                            e.preventDefault();
                            return false;
                        }
                    });
                });
            </script> 
        <?php
        }

        echo ' 
        <style type="text/css">
                    #content{
                        position:absolute;
                        left:300px;
                        padding:0 0 0 5px;
                        width: 5000px;
                    }

                    .left{
                        position:absolute;
                        left:0px;
                        width:300px;
                        border-right:1px solid grey;
                        overflow-x:scroll;
                    }

            div.php-namespace{
                padding:15px;
            }
            div.php-class{
                padding:15px;
            }
            div.php-function{
                padding:10px;
            }
        .php-tag{
            color:red;
            font-weight:bold;
        }
        .php-class-keyword{
            color:green;
            font-weight:bold;
        }
        .php-class-dependency{
            color:#ae1414;
            font-weight:bold;
        }
        .php-method-implement{
        color:#ae1414;
            font-weight:bold;
        }
        .php-type{
            color:blue;
        }

        .php-doc{
        color:orange
        }
        .php-test{
        }
        .php-test-red{
            color:#ae1414;
        }
        .php-test-green{
            color:green;
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

        function readAction() {
            require('../Autoload.php');
            \CRUDsader\Autoload::register();
            echo '<h3>' . $_REQUEST['class'] . '</h3>';
            $reader = new \ReflectionClass('\\' . str_replace('..', 'CRUDsader', str_replace('/', '\\', $_REQUEST['class'])));

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
            $doc = $reader->getDocComment();

            $comment = trim(substr($doc, 6, strpos($doc, '@') - 6));
            $methods = $reader->getMethods();
            //
            $properties = ($reader->getProperties());


            $tested = (preg_match('$\@(test)\s*([^\@\*]*)$', $doc, $match));
            $dependency = (preg_match('$\@(dependency)\s*([^\@\*]*)$', $doc, $dependencies));


            $t = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            echo '<code><span class="php-tag">&lt;?php</span><br/>';

            if ($namespace)
                echo '<div class="php-namespace"><span class="php-class-keyword">namespace</span> ' . $namespace . '{<br/>';
            if (!$tested)
                echo '<span class="php-test php-test-red">UNTESTED</span> ';
            echo '<div class="php-class"><span class="php-doc">' . $comment . '</span><br/>';
            if ($interface) {
                echo '<span class="php-class-keyword">interface</span> ';
            } else {
                if ($abstract)
                    echo '<span class="php-class-keyword">abstract</span> ';
                echo '<span class="php-class-keyword">class</span>';
            }



            echo ' <span class="php-class"><a href="ut.php?file=' . str_replace('\\', '/', substr($class, 10)) . '_Test.php' . '" target="_blank">' . $class . '</a></span>';
            if ($parentClass)
                echo '<br/><span class="php-class-keyword">extends</span> <a  href="doc.php?class=' . $parentClass . '">' . $parentClass . '</a>';
            if ($dependency)
                echo ' <span class="php-class-keyword">@depends <span class="php-class-dependency">' . $dependencies[2] . '</span> </span>';
            if (!empty($interfaces)) {
                echo '<br/><span class="php-class-keyword">implements</span><br/>';
                foreach ($interfaces as $interface)
                    echo $t . '<a  href="doc.php?class=' . $interface . '">' . $interface . '</a><br/>';
            }
            echo '<br/>{';
            foreach ($properties as $property) {
                $paramsHtml = array();
                echo '<div class="php-function">';
                $doc = $property->getDocComment();
                $var = false;
                if (preg_match_all('$\@(var)\s*([^\@\*]*)$', $doc, $matches)) {
                    foreach ($matches[1] as $index => $type) {
                        switch ($type) {
                            case 'var':
                                $var = $matches[2][$index];
                                break;
                        }
                    }
                }

                $comment = trim(substr($doc, 6, strpos($doc, '@') - 6));
                if ($comment != '*')
                    echo '<span class="php-doc">' . $comment . '</span><br/>';
                if ($property->class != $class)
                    echo '<span class="php-method-implement"><a href="doc.php?class=' . $property->class . '">' . $property->class . '</a>' . '</span> : ';
                if ($property->isPublic())
                    echo '<span class="php-class-keyword">public</span>';
                else if ($property->isProtected())
                    echo '<span class="php-class-keyword">protected</span>';
                else if ($property->isPrivate())
                    echo '<span class="php-class-keyword">protected</span>';
                else
                    echo $t;
                if ($property->isStatic())
                    echo ' <span class="php-class-keyword">static</span>';
                if ($var)
                    echo ' <span class="php-type">' . $var . '</span>';
                echo ' <span class="php-var">$' . $property->getName() . '</span>';
                $params = array();


                echo '</div>';
            }
            $baseTypes = array('int', 'bool', 'boolean', 'object', 'mix', 'string', 'array');
            foreach ($methods as $method) {

                $paramsHtml = array();
                echo '<div class="php-function">';
                $doc = $method->getDocComment();

                $test = false;
                $throws = false;
                $return = '<span class="php-type">void</span>';
                if (preg_match_all('$\@(param|test|access|throws|return)\s*([^\@\*]*)$', $doc, $matches)) {
                    foreach ($matches[1] as $index => $type) {
                        switch ($type) {
                            case 'test':
                                $test = trim($matches[2][$index]);
                                break;
                            case 'throws':
                                preg_match('|\s*(\w*)\s*(.*)|', $matches[2][$index], $throws);
                                break;
                            case 'param':
                                $ex = explode(' ', $matches[2][$index]);
                                $paramsHtml[$ex[1]] = '<span class="php-type">' . (in_array($ex[0], $baseTypes) ? $ex[0] : '<a href="doc.php?class=' . $ex[0] . '">' . $ex[0] . '</a>') . '</span> <span class="php-var">' . $ex[1] . '</span>';
                                break;
                            case 'return':
                                $return = trim($matches[2][$index]);
                                if (!in_array($return, $baseTypes))
                                    $return = '<a href="doc.php?class=' . $return . '">' . $return . '</a>';
                                else
                                    $return = '<span class="php-type">' . $return . '</span>';
                                break;
                        }
                    }
                }
                $comment = trim(substr($doc, 6, strpos($doc, '@') - 6));

                if ($comment != '*')
                    echo '<span class="php-doc">' . $comment . '</span><br/>';


                if ($method->class != $class)
                    echo '<span class="php-method-implement"><a href="doc.php?class=' . $method->class . '">' . $method->class . '</a>' . '</span> : ';
                if (!$test && !$method->isAbstract())
                    echo '<span class="php-test php-test-red">UNTESTED</span> ';
                if ($method->isAbstract())
                    echo '<span class="php-class-keyword">abstract</span> ';
                if ($method->isPublic())
                    echo '<span class="php-class-keyword">public</span>';
                else if ($method->isProtected())
                    echo '<span class="php-class-keyword">protected</span>';
                else if ($method->isPrivate())
                    echo '<span class="php-class-keyword">protected</span>';
                else
                    echo $t;
                echo ' <span class="php-class-keyword">' . $return . '</span> ' . $method->getName() . '(';
                $params = array();
                foreach ($method->getParameters() as $m) {
                    $name = '$' . $m->getName();
                    $params[] = isset($paramsHtml[$name]) ? $paramsHtml[$name] : '<span class="php-var">' . $name . '</span>';
                }
                echo implode(',', $params), ')';

                //    if ($test)
                //     echo $t.$t.'<span class="php-test php-test-green">@tested</span> ' . $test;
                if (($throws)) {
                    echo '<br>' . $t . '<span class="php-class-keyword">throws</span> <span class="php-test php-test-throws">' . $throws[1] . '</span> <span class="php-doc">' . $throws[2] . '</span></br>';
                }
                echo '</div>';
            }
            echo '}</div>';
            if ($namespace)
                echo '}</div>';
            echo '</code>';
        }

        function _readDir($dir) {
            $ret = array();
            if (strpos($dir, '../project') !== false)
                return $ret;
            if ($handle = opendir($dir)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file == '.' || $file == '..')
                        continue;
                    if (strpos($file, '.') === false) {
                        $rd = _readDir($dir . '/' . $file);
                        $ret = array_merge($ret, $rd);
                    } else {
                        if (substr($file, -4) == '.php')
                            $ret[] = $dir . '/' . substr($file, 0, -4); //str_replace('/', '\\', substr($dir, 10) . ($dir == 'CRUDsader' ? 'A/' : '/') . substr($file, 0, -4));
                    }
                }
                closedir($handle);
            }
            return $ret;
        }

        function pre($v) {
            echo '<pre>';
            print_r($v);
            echo '</pre>';
        }

        function defaultAction() {
            echo '<div class="left">';
            $dir = '..';
            $l = strlen($dir);
            $classes = _readDir($dir);
            sort($classes);
            // namespaces
            echo '<h3><a href="dependency.php">see dependencies</a></h3>';
            $namespaces = array();
            foreach ($classes as $class) {
                $exs = explode('/', $class);
                $str = '';
                for ($i = 0; $i < count($exs); $i++) {
                    $lstr = $str;
                    $str.=$exs[$i] . '/';
                    if (!isset($namespaces[$str]))
                        $namespaces[$str] = array();
                }
                if (!isset($namespaces[$lstr]))
                    $namespaces[$lstr] = array();
                $namespaces[$lstr][] = $class;
            }

            foreach ($namespaces as $name => $classes) {
                if (empty($classes))
                    continue;
                echo '<h3>' . $name . '</h3>';
                foreach ($classes as $class)
                    echo '<a  href="doc.php?class=' . $class . '">' . $class . '</a><br/>';
            }
            echo '</div><div id="content"></div>';
        }
        if(!isset($_SESSION))
            session_start();
        
       

        if (isset($_REQUEST['class'])) {
            $_SESSION['class'] = $_REQUEST['class'];
            readAction();
        }else{
            defaultAction();
            if(isset($_SESSION['class'])){
                echo '<script  type="text/javascript">
                    $(document).ready(function(){
                    $.ajax({
                                url: "doc.php?class='.$_SESSION['class'].'",
                                 success:function(data){
                                    $(\'#content\').html(data);
                                }
                            });
                });</script>';
            }
        }
