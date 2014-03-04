<?php

require_once '../lib/ajax_common.php';

$fields = post( 'params' );

if ( $result['success'] )
{
	if ( isset( $fields['pass'] ))
	{
		$ipass = $fields['pass'];
		$fields['pass'] = pass_md5( $fields['pass'], true );
	}
	$result['success'] = $db->query( "update ?n set ?u where id=?s", 
		                         CONF_PREFIX.'_users', $fields, $USER['id']);
	if ( $result['success'] )
	{
		if ( isset( $ipass ))
			cookie_set( 'pass', md5( $ipass ), 120 );
	}
}
print json_encode( $result );
?>