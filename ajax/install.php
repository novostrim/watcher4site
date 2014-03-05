<?php
/*
	Watcher4site 
	(c) 2014 Novostrim, OOO. http://www.novostrim.com
	License: MIT
*/
$filename = '../conf.inc.php';
$result = array( 'success'=> false, 'err' => 1, 'result' => 0 );
if ( !file_exists( $filename ))
{
//$test = json_decode(trim(file_get_contents('php://input')), true);
//header('Content-Type: application/json');
	require_once '../app.inc.php';
	require_once '../lib/lib.php';
	require_once '../lib/extmysql.class.php';

	define( 'CONF_QUOTES', get_magic_quotes_gpc());
	$dir = dirname( dirname( $_SERVER['SCRIPT_NAME'] ));
	if ( $dir[ strlen( $dir ) - 1] != '/' )
		$dir .= '/';
	define( 'CONF_DIR', $dir );
	
	$form = post( 'form' );
	$step = 'err_connect';
	$sqlname = '../db.sql';
	$wspath = dirname( dirname( $_SERVER['SCRIPT_FILENAME'] ));
//	print "$wspath=".chmod( $wspath, 0777 );
	try
	{
		$db = new ExtMySQL( array_merge( $form, array( 'errmode' => 'exception' )) );
		$step = 'err_create';
		define( 'CONF_DB', $form['db'] );
		$tables = $db->tables();
		$prefix = post( 'prefix', APP_PREFIX );
		if ( file_exists( $sqlname ) )
		{
			$step = 'err_system';
		}
		else
		{
			if ( !$prefix )
			{
				$latest = in_array( APP_DB, $tables ) ?
				               $db->getone("select id from ?n order by id desc", APP_DB ) : 0;
				$prefix = $latest ? $latest + 1 : 1;
			}
			if ( !in_array( APP_DB, $tables ) || !$db->getone( 'select count(*) from ?n', APP_DB ))
			{
				$sql = str_replace( array( 'xxx', 'app_db' ), array( $prefix, APP_DB ), 
					 		file_get_contents( "$wspath/".APP_NAME."/db.sql" ));
				foreach ( explode( '##', $sql ) as $isql )
				{
					if ( trim( $isql ))
						$db->query( $isql );
				}
			}
		}

		$form['salt'] = pass_generate();
		define( 'CONF_SALT', $form['salt'] );
		
		$ipass = $form['psw'];

		$passmd = pass_md5( $form['psw'], true );
		$form['psw'] = pass_generate();
		$lang = post( 'lang' );
		$db->query("insert into ?n set pass=?s, ctime=NOW(), lang=?s, name=?s", APP_DB,
			        pass_md5( $form['psw'], true ), $lang, APP_NAME );
		$form['dbid'] = $db->insertid();
		if ( !$prefix )
			$prefix = $form['dbid'];
		$db->query("update ?n set prefix=?s where id=?s", APP_DB, $prefix, $form['dbid'] );
		define( 'CONF_DBID', $form['dbid'] );

		$db->query("insert into ?n set login='admin', pass=?s, lang=?s, ctime=NOW(), 
			        uptime=NOW()", $prefix.'_users', $passmd, $lang );
		$iduser = $db->insertid();
		cookie_set( 'iduser', $iduser, 120 );
		cookie_set( 'pass', md5( $ipass ), 120 );

		$form['dir'] = $dir;
		$form['quotes'] = CONF_QUOTES;
		$form['prefix'] = $prefix;
		$form['host'] = $_SERVER['HTTP_HOST'];
		$form['docroot'] = $_SERVER['DOCUMENT_ROOT'];
		foreach ( $form as $kp => $ip )
			$lines[] = "define( 'CONF_".strtoupper($kp)."', '$ip' );";
//				$lines[] = '$CONF['."'$kp'] = '$ip';";
		$result['user'] = $db->getrow( "select id, login,lang from ?n where id=?s", 
		                  $prefix.'_users', $iduser );
		$result['success'] = isset( $lines ) && file_put_contents( $filename, 
		    "<?php \r\n".implode( "\r\n", $lines )."\r\n?>" ) ? 1 : 0;
	}
	catch ( Exception $e )
	{
//		print '='.$e->getMessage();
		$result['err'] = $step;
		if ( $step == 'err_create' )
		{
			$result['temp'] = $_SERVER['HTTP_HOST'].$dir.'db.sql';
			file_put_contents( $sqlname, $sql );
		}
	}
}
print json_encode( $result );
?>
