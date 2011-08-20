<?php
$dir=dirname(__FILE__).'/Art/Test/';
chdir($dir);
if(!isset($_REQUEST['file'])){
    $handle = opendir($dir);
     while (false !== ($file = readdir($handle))) {
         if($file!='.' && $file!='..'){
            echo '<a style="text-decoration:none" href="'.$_SERVER['PHP_SELF'].'?file='.$file.'">'.$file.'</a><br>';
         }
    }
} 
else {
    $cmd='php parts/phpunit.php --bootstrap parts/bootstrap.php '.(empty($_REQUEST['file'])?'./':$_REQUEST['file']).'';
    echo $cmd.'<br>';
    ob_start();
    
    print_r(shell_exec($cmd));
    $content=ob_get_clean();
    echo '<pre>'.($content).'</pre>';
}
?>