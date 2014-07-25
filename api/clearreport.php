<?php
/*
	Watcher4site 
	(c) 2014 Novostrim, OOO. http://www.novostrim.com
	License: MIT
*/

require_once '../lib/ajax_common.php';

if ($result['success']) {
    $task = $db->getrow("select * from " . CONF_PREFIX . "_task order by id desc");
    if ($task) {
        if ($db->query("delete from ?n where idtask=?s && status=1", CONF_PREFIX . '_files', $task['id']))
            if ($db->query("update ?n set status=0, time=ntime, hash=nhash, uptime=nuptime,
		           size=nsize, perm=nperm where idtask=?s", CONF_PREFIX . '_files', $task['id'])
            ) {
                $db->query("update ?n set value=NOW() where name=?s", CONF_PREFIX . '_app', 'uptime');
                $db->query("update ?n set value=0 where name=?s", CONF_PREFIX . '_app', 'latestmod');
            }
    }
}

print json_encode($result);