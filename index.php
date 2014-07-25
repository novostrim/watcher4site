<?php
/*
	Watcher4site 
	(c) 2014 Novostrim, OOO. http://www.novostrim.com
	License: MIT
*/

require_once "app.inc.php";
require_once "lib/lib.php";

$lang = '';

if (file_exists("conf.inc.php")) {
    require_once "conf.inc.php";
    require_once "lib/extmysql.class.php";

    $db = new ExtMySQL(array('host' => defined('CONF_DBHOST') ? CONF_DBHOST : 'localhost',
                             'db'   => CONF_DB, 'user' => defined('CONF_USER') ? CONF_USER : '',
                             'pass' => defined('CONF_PASS') ? CONF_PASS : ''));

    $dbpar = $db->getrow('select * from ?n where id=?s && pass=?s', APP_DB,
        CONF_DBID, pass_md5(CONF_PSW, true));
    if (!$dbpar) {
        print "System Error";
        exit();
    }
//	print pass_md5( '111', true );
    $conf['title'] = $dbpar['name'];
    $conf['dblang'] = $dbpar['lang'];
    $conf['isalias'] = $dbpar['isalias'];
    login();
    if (!$USER) {
        $conf['module'] = 'login';
        $lang = $dbpar['lang'];
    } else {
        $lang = $USER['lang'];
        $conf['apitoken'] = $dbpar['apitoken'];
    }
    $conf['user'] = $USER;
//	REQUEST_URI
} else {
    $langs = array('en', 'ru');
    $ulang = explode(';', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    foreach ($ulang as $iul) {
        $ul = explode(',', $iul);
        foreach ($ul as $iu)
            if (in_array($iu, $langs)) {
                $lang = $iu;
                break;
            }
        if ($lang)
            break;
    }
    $conf['module'] = 'install';
    $conf['title'] = '';
}
$conf['lang'] = $lang ? $lang : 'en';

$template = file_get_contents('tpl/index.tpl');

foreach ($FTYPES as $fkey => $fval) {
    $ftypes[] = array('id' => $fkey, 'name' => $fval['name']);
}

$vars = array(
    'lang'     => $conf['lang'],
    'appname'  => APP_NAME,
    'cfg'      => json_encode($conf),
    'types'    => json_encode($ftypes),
    'langlist' => json_encode($langlist),
);

foreach ($vars as $kvar => $ivar) {
    $afrom[] = '{$' . $kvar . '}';
    $ato[]   = $ivar;
}

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0', false);
header('Pragma: no-cache');

echo str_replace($afrom, $ato, $template);