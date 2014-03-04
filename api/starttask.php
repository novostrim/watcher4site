<?php
/*
	Watcher4site 
	(c) 2014 Novostrim, OOO. http://www.novostrim.com
	License: MIT
*/

require_once '../lib/ajax_common.php';

if ( $result['success'] )
{
	$form = post( 'form' );
	if ( isset( $form['ignpath'] ))
	{
		$atemp = explode( "\n", $form['ignpath'] );
		foreach ( $atemp as &$ia )
			$ia = trim( $ia );
		$form['ignpath'] = implode(',', $atemp );
	}
	require_once '../api/watcher.php';
	$result['result'] = watcher( $form );
}
print json_encode( $result );
?>