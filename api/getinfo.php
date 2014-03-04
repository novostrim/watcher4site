<?php
/*
	Watcher4site 
	(c) 2014 Novostrim, OOO. http://www.novostrim.com
	License: MIT
*/

require_once '../lib/ajax_common.php';

if ( $result['success'] )
{
	$dbtask = CONF_PREFIX.'_task';
	$task = $db->getrow("select *, ( TIMEDIFF( NOW(), lastrun ) < '00:00:30' ) as inproc from 
		                 ?n order by id desc", $dbtask);

	if ( $task )
	{
		$result['result'] = $task;
		$result['result']['ok'] = $task['closed'] ? 1 : ( $task['inproc'] ? 2 : 3 );
		$result['result']['numfiles'] = $db->getone("select count(*) from ?n 
			  where idtask=?s && !isfolder", CONF_PREFIX.'_files', $task['id'] );
		$result['result']['changes'] = $db->getone("select count(*) from ?n 
			  where idtask=?s && status > 0", CONF_PREFIX.'_files', $task['id'] );
	}
	else
		$result['result'] = array( 'ok' => 0 );
}
print json_encode( $result );
?>