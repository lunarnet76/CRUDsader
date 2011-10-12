<?php
$dir = dirname(__FILE__) . '/Test';
chdir($dir);

function rd($dir) {
    $handle = opendir($dir);
    if ($handle === false)
        die('cant read');
    while (false !== ($file = readdir($handle))) {
        if ($file != '.' && $file != '..') {
            $dirname=str_replace(dirname(__FILE__).'/Test/CRUDsader/','',$dir);
            if (is_dir($dir . $file)) {
                rd($dir .  $file.'/');
            }
            else
                echo '<a style="text-decoration:none" href="' . $_SERVER['PHP_SELF'] . '?file=' .$dirname.$file . '">' . $dirname.$file . '</a><br>';
        }
    }
}
if (!isset($_REQUEST['file']) && !isset($_REQUEST['all'])) {
    echo '<a style="text-decoration:none" href="' . $_SERVER['PHP_SELF'] . '?all=true">ALL</a><br>';
    rd($dir . '/CRUDsader/');
}
if (isset($_REQUEST['all'])) {
    $cmd = 'php phpunit.php --bootstrap bootstrap.php --coverage-html coverage --configuration configuration.xml 2>&1'; //Art/'.(empty($_REQUEST['file'])?'./':$_REQUEST['file']).'
    echo $cmd . '<br>';
    ob_start();
    print_r(shell_exec($cmd));
    $content = ob_get_clean();
    echo '<pre>' . ($content) . '</pre>';
    echo '<a style="text-decoration:none" target="_blank" href="Test/coverage/CRUDsader.html">coverage</a>';
}
if (isset($_REQUEST['file'])) {
    $cmd = 'php phpunit.php --bootstrap bootstrap.php CRUDsader/' . (empty($_REQUEST['file']) ? './' : $_REQUEST['file']) . ' 2>&1'; //
    echo $cmd . '<br>';
    ob_start();
    print_r(shell_exec($cmd));
    $content = ob_get_clean();
    echo '<pre>' . ($content) . '</pre>';
}