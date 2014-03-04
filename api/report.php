<?php
/*
	Watcher4site 
	(c) 2014 Novostrim, OOO. http://www.novostrim.com
	License: MIT
*/

require_once '../lib/ajax_common.php';

function reportlink( $page )
{
	global $urlparam;

	$ret = '#/report'.( $urlparam ? '?'.$urlparam : '');
	if ( $page == 1 )
		return $ret;

	return $ret.( $urlparam ? '&' : '?').'p='.$page;
}

function getfullname( $idowner )
{
	global $db, $paths;

	$owner = $db->getrow("select name,idowner from ?n where id=?s", CONF_PREFIX.'_files', $idowner );
	$ret = '';
	if ( $owner['idowner'])
		$ret = getfullname( $owner['idowner'] ).'/';
	return $ret.$owner['name'];
}

if ( $result['success'] )
{
	$task = $db->getrow("select * from ".CONF_PREFIX."_task order by id desc");
	if ( $task )
	{
		$paths = array();
		$qwhere = $db->parse("where idtask=?s && status>0", $task['id'] );
	    $urlparam = url_params( 'p' );
		$query = $db->parse( "select count(*) from ?n as m ?p", CONF_PREFIX.'_files', $qwhere );
		$result['pages'] = pages( $query, array( 'onpage' => 50, 'page' => (int)get('p') ), 'reportlink' );
		$result['items'] = $db->getall("select * from ?n as m ?p
			order by status desc,idowner,name ?p", CONF_PREFIX.'_files', $qwhere, $result['pages']['limit'] );
		foreach ( $result['items'] as &$item )
		{
			if ( $item['idowner'] )
				if ( isset( $paths[ $item['idowner']] ))
					$item['name'] = $paths[ $item['idowner']].'/'.$item['name'];
				else
					$item['name'] = getfullname( $item['idowner'] ).'/'.$item['name'];
			$item['hashok'] = ( $item['hash'] && $item['nhash'] && $item['hash'] != $item['nhash'] ? -1 : 
				 ( !$item['hash'] || !$item['nhash'] ? 0 : 1 ));
			$item['time'] = $item['time'] ? strftime("%Y-%m-%d %H:%M:%S", $item['time'] ): ' ';
			if ( !$item['size'])
				$item['size'] = ' ';
			$item['ntime'] = strftime("%Y-%m-%d %H:%M:%S", $item['ntime'] );
			$item['perm'] = $item['perm'] ? substr(sprintf('%o', $item['perm'] ), -4) : ' ';
			$item['nperm'] = substr(sprintf('%o', $item['nperm'] ), -4);
			if ( $item['status'] == 1 )
			{
				$item['nperm'] = ' ';
				$item['nsize'] = ' ';
				$item['ntime'] = ' ';
			}
			$item['timeok'] = $item['time'] == $item['ntime'] ? 1 : 0;
			$item['sizeok'] = $item['size'] == $item['nsize'] ? 1 : 0;
			$item['permok'] = $item['perm'] == $item['nperm'] ? 1 : 0;
		}
		$result['uptime'] = $db->getone("select value from ?n where name=?s", CONF_PREFIX.'_app', 'uptime' );
	}

//	print_r( $result['pages'] ); 

/*	$task = $db->getrow("select * from ".CONF_PREFIX."_task order by id desc");
	if ( $task )
	{
		$result['result'] = $task;
	}
	else
		$result['result'] = array( 'ext' => '', 'hash' => 0, 'ignext' => 'jpg', 'ignpath' => "temp\r\ntmp", 'limit' => 25 );
*/
}
print json_encode( $result );


?>