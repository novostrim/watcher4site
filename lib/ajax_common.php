<?php
/*
	Watcher4site 
	(c) 2014 Novostrim, OOO. http://www.novostrim.com
	License: MIT
*/

$result = array( 'success'=> true, 'err' => 1, 'result' => 0, 'temp' => '' );
$dir = dirname( dirname( $_SERVER['SCRIPT_FILENAME'] ));
require_once $dir.'/app.inc.php';
require_once $dir.'/conf.inc.php';
require_once 'lib.php';
require_once "extmysql.class.php";

function api_error( $err, $temp ='' )
{
	global $result;
   	$result['success'] = false;      
   	$result['err'] = $err;
   	$result['temp'] = $temp;
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

$db = new ExtMySQL( array( 'host' => defined( 'CONF_DBHOST' ) ? CONF_DBHOST : 'localhost',
	               'db' => CONF_DB, 'user' => defined( 'CONF_USER' ) ? CONF_USER : '',
		         'pass' => defined( 'CONF_PASS' ) ? CONF_PASS : '' ));
login();

if ( !$USER )
{
	api_error( 'err_login' );
//	$result['err'] = 'err_login';
//	$result['code'] = false;
}

?>
