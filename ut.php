<?php
$dir=dirname(__FILE__).'/Test';
chdir($dir);
if(!isset($_REQUEST['file']) && !isset($_REQUEST['all'])){
echo '<a style="text-decoration:none" href="'.$_SERVER['PHP_SELF'].'?all=true">ALL</a><br>';
    $handle = opendir($dir.'/Art/');
    if($handle===false)die('cant read');
     while (false !== ($file = readdir($handle))) {
         if($file!='.' && $file!='..'){
            echo '<a style="text-decoration:none" href="'.$_SERVER['PHP_SELF'].'?file='.$file.'">'.$file.'</a><br>';
         }
    }
} 
if(isset($_REQUEST['all'])){
    $cmd='php phpunit.php --bootstrap bootstrap.php --coverage-html coverage --configuration configuration.xml 2>&1';//Art/'.(empty($_REQUEST['file'])?'./':$_REQUEST['file']).'
    echo $cmd.'<br>';
    ob_start();
    print_r(shell_exec($cmd));
    $content=ob_get_clean();
    echo '<pre>'.($content).'</pre>';
    echo '<a style="text-decoration:none" target="_blank" href="Test/coverage/Art.html">coverage</a>';
}
if(isset($_REQUEST['file'])){
    $cmd='php phpunit.php --bootstrap bootstrap.php Art/'.(empty($_REQUEST['file'])?'./':$_REQUEST['file']).' 2>&1';//
    echo $cmd.'<br>';
    ob_start();
    print_r(shell_exec($cmd));
    $content=ob_get_clean();
    echo '<pre>'.($content).'</pre>';
}