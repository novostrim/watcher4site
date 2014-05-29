<?php
/*
	Watcher4site 
	(c) 2014 Novostrim, OOO. http://www.novostrim.com
	License: MIT
*/

require_once dirname( dirname( $_SERVER['SCRIPT_FILENAME'] )).'/lib/ajax_common.php';

if ( 1 )// $result['success'] )
{
	require_once 'watcher.php';
/*	$params = array();
	foreach ( array('ext', 'hash', 'ignext', 'ignpath' ) as $ip )
		$params[ $ip ] = get( $ip );

	$token = get('token');
	if ( $token != CONF_SALT )
	{
		print "Wrong token";
		exit();
	}
	$ret = watcher( count( $params ) ? $params : '' );*/
	$ret = watcher();
	$count = 0;
	$dif = 0;
	$task = $db->getrow("select * from ".CONF_PREFIX."_task order by id desc");
	if ( $ret && $task )
	{
		$qwhere = $db->parse("where idtask=?s && status>0", $task['id'] );
		$count = $db->getone( "select count(*) from ?n as m ?p", CONF_PREFIX.'_files', $qwhere );

		$app = $db->getall("select * from ?n", CONF_PREFIX.'_app' );
		foreach ( $app as $iap )
			$$iap['name'] = $iap['value'];
		if ( $count && $count > $latestmod )
			$dif = (int)$count - (int)$latestmod;
		$db->query("update ?n set value=?s where name=?s", CONF_PREFIX.'_app', $count, 'latestmod' );
	}
	print "$ret=$count=$dif";
	$test = (int)get('test');
	if ( $test )
	{
		print "<br>Task completed: ".( $ret ? 'yes' : 'no')."<br>$count files have been changed<br>";
	}
	if ( $dif )
	{
		if ( $nfyemail )
		{
			require_once "../lib/mail.php";
			$emails = explode( ',', $nfyemail );
			$body = $emailtext."<br>[$ret:$count:$dif]";
			$from = 'noreplay@'.str_replace( "www.", '', CONF_HOST );
			foreach ( $emails as $ie )
				if ( $ie )
				{
					$ret = send_mail( '', $ie, $conf['appname'], $body, $conf['appname'], $from );
					if ( $test )
						print "Email to $ie: ".( $ret ? 'ok' : 'error' )."<br>"; 
				}
		}
		if ( $nfyurl )
		{
			$ret = @file_get_contents( $nfyurl );
			if ( $test )
				print "URL $nfyurl: ".( $ret === false ? 'error' : 'ok' ).'<br>';
		}
		if ( $nfyscript )
		{
			$fname = CONF_DOCROOT.($nfyscript[0] != '/' ? '/' : '' ).$nfyscript;
			$ret = 0;
			if ( $test )
				print "$fname: <br>";
			if ( file_exists( $fname ))
			{
				require_once $fname;
				$ret = 1;
			}
			if ( $test )
				print '<br>'.( $ret ? 'ok' : 'error' ).'<br>';
		}
	}
}
?>
