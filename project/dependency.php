<?php
$cmd = 'dot  -o'.__DIR__.'/dependency.png -Tpng:cairo '.__DIR__.'/dependency.gv 2>&1'; //Art/'.(empty($_REQUEST['file'])?'./':$_REQUEST['file']).'
echo $cmd . '<br>';
ob_start();
print_r(shell_exec($cmd));
$content = ob_get_clean();
echo '<pre>' . ($content) . '</pre>';
echo '<img src="dependency.png">' ;