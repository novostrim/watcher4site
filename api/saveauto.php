<?php
/*
	Watcher4site 
	(c) 2014 Novostrim, OOO. http://www.novostrim.com
	License: MIT
*/

require_once '../lib/ajax_common.php';

if ($result['success']) {
    foreach (array('nfyemail', 'nfyurl', 'nfyscript', 'emailtext') as $in)
        $db->query("update ?n set value=?s where name=?s", CONF_PREFIX . '_app', post($in), $in);
    $result['result'] = array();
    $app = $db->getall("select * from ?n", CONF_PREFIX . '_app');
    foreach ($app as $iap)
        $result['result'][$iap['name']] = $iap['value'];
}
print json_encode($result);
?>