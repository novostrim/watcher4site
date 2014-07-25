<?php
/*
	Watcher4site 
	(c) 2014 Novostrim, OOO. http://www.novostrim.com
	License: MIT
*/
$wspath = dirname(dirname($_SERVER['SCRIPT_FILENAME']));

$result = array('success' => false, 'err' => 1, 'result' => 0);
if (file_put_contents($wspath . '/test.inc.php', "<?php \r\n\r\n?>")) {
    $result['success'] = true;
    unlink($wspath . '/test.inc.php');
} else {
    $result['success'] = false;
    $result['err'] = 'err_write';
    $result['temp'] = $wspath;

}
print json_encode($result);
?>
