<?php
/*
	Watcher4site 
	(c) 2014 Novostrim, OOO. http://www.novostrim.com
	License: MIT
*/

require_once '../lib/ajax_common.php';

if ( $result['success'] )
{
	$task = $db->getrow("select * from ".CONF_PREFIX."_task order by id desc");
	if ( $task )
	{
		if ( $task['ignpath'] )
			$task['ignpath'] = implode("\r\n", explode( ",", $task['ignpath'] ));
		$result['result'] = $task;
	}
	else
		$result['result'] = array( 'ext' => '', 'hash' => 0, 'ignext' => 'jpg', 'ignpath' => "temp\r\ntmp", 'limit' => 25 );
}
print json_encode( $result );
?>