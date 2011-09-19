<?php
// Set the limit to 5 MB.
$fiveMBs = 5 * 1024 * 1024;
$fp = fopen("php://memory", 'r+');
if(isset($_REQUEST['w'])){
fputs($fp, "hello\n");
rewind($fp);

}

// Read what we have written.

echo stream_get_contents($fp);