<?php
/*
	Watcher4site 
	(c) 2014 Novostrim, OOO. http://www.novostrim.com
	License: MIT
*/

require_once '../lib/ajax_common.php';

if ($result['success']) {
    $dbtask = CONF_PREFIX . '_task';
    $task = $db->getrow("select * from ?n order by id desc", $dbtask);
    $script = str_replace('getauto', 'cron', $_SERVER['SCRIPT_FILENAME']);
    $params = '';
    $apps = array();
    if ($task) {
        $pars = array('token=' . CONF_SALT);
        if ($task['hash'])
            $pars[] = 'hash=1';
        if ($task['ext'])
            $pars[] = 'ext=' . urlencode($task['ext']);
        if ($task['ignext'])
            $pars[] = 'ignext=' . urlencode($task['ignext']);
        if ($task['ignpath'])
            $pars[] = 'ignpath=' . urlencode(implode(',', explode("\n", $task['ignpath'])));

        $params = '?' . implode('&', $pars);
        $app = $db->getall("select * from ?n", CONF_PREFIX . '_app');
        foreach ($app as $iap)
            $apps[$iap['name']] = $iap['value'];
    }
    $params = ''; // cut
    $result['result'] = array_merge(array('script'    => $script . $params,
                                          'urlscript' => str_replace('getauto', 'cron', $_SERVER['SCRIPT_NAME']) . /*$params.'&test=1'*/
                                              '?test=1'), $apps);
}
print json_encode($result);
?>