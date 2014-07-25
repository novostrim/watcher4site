<?php
/*
   Watcher4site 
   (c) 2014 Novostrim, OOO. http://www.novostrim.com
   License: MIT
*/

define('SITEURL', $_SERVER['HTTP_HOST']);
define('LOCALHOST', $_SERVER['SERVER_ADDR'] == '127.0.0.1');

define('FT_NUMBER', 1);
define('FT_VAR', 2);
define('FT_TIME', 3);

$FTYPES = array(
    1 => array("name" => 'fnumber', 'sql' => 'int(10)'),
    2 => array("name" => 'ftext', 'sql' => 'varchar(%par%)'),
    3 => array("name" => 'fdatetime', 'sql' => 'datetime')
);

error_reporting(LOCALHOST ? E_ALL | E_STRICT : E_ALL & ~E_NOTICE);

$USER = array();

function alias($pars, $prefix = '')
{
    return $pars['alias'] ? $pars['alias'] : $prefix . $pars['id'];
}

function cookie($name)
{
    $icook = '__' . $name;

    return isset($_COOKIE[$icook]) ? $_COOKIE[$icook] : '';
}

function cookie_set($name, $value = '', $time = 1)
{
    $icook = '__' . $name;
    setcookie($icook, $value, $value ? time() + 3600 * $time : 0, CONF_DIR); //, SITEURL );
    if ($value)
        $_COOKIE[$icook] = $value;
    elseif (isset($_COOKIE[$icook]))
        unset($_COOKIE[$icook]);
}

function login()
{
    global $db, $USER;

// id=1 && 
    $USER = $db->getrow("select id, login, email, TIMESTAMPDIFF( HOUR, uptime, NOW()) as lastdif, lang from ?n where pass=?s",
        CONF_PREFIX . '_users', pass_md5(cookie('pass')));
    if ($USER && $USER['lastdif'] > 6) {
        $db->query("update ?n set uptime = NOW() where id=?i",
            CONF_PREFIX . '_users', $USER['id']);
        cookie_set('pass', cookie('pass'), 120);
        cookie_set('iduser', $USER['id'], 120);
    }

    return $USER ? true : false;
}

function pages($query, $page, $link)
{
    global $db;
    /*
        $page = array(
          in
           onpage => items on page
           page => the current page
           middle => the pages at the center
        )
        link => the name of callback function link( page );
    */

    $result = array();
    $pages = 1;
    $count = (is_numeric($query) ? $query : $db->getOne($query));
    $onpage = max(1, isset($page['onpage']) ? $page['onpage'] : 50);
    $pages = max(1, ceil($count / $onpage));
    $curpage = min((isset($page['page']) ? max($page['page'], 1) : 1), $pages);
    $result['curpage'] = $curpage;
    $result['found'] = $count;
    $result['limit'] = '';
    $middle = isset($page['center']) ? $page['center'] : 5;
    $ret = array();
    if ($pages > 1) {
        $ret[] = array(-1, $curpage == 1 ? '' : $link($curpage - 1));
        if ($curpage >= $middle) {
            $ret[] = array(1, $curpage == 1 ? '' : $link(1));
            $ret[] = array(0, '');
        }
        if ($curpage < $middle) {
            $off = 1;
        } elseif ($pages - $curpage < $middle - 1) {
            $off = max(1, $pages - $middle + 1);
        } else
            $off = $curpage - 2;

//        print "$curpage=$off";
        for ($i = $off; $i <= min($off + $middle - 1, $pages); $i++) {
            if ($i == $curpage)
                $ret[] = array($i, '');
            else
                $ret[] = array($i, $link($i));
        }
        if ($off + $middle < $pages) {
            $ret[] = array(0, '');
            $ret[] = array($pages, $curpage == $pages ? '' : $link($pages));
        }
        $ret[] = array(-2, $curpage < $pages ? $link($curpage + 1) : '');
        $result['limit'] = " limit " . ($onpage * ($curpage - 1)) . "," . $onpage;
    }
    $result['plist'] = $ret;

    return $result;
}

function pass_generate($i = 6)
{
    $newpass = '';
    while ($i--) {
        do {
            $char = chr(mt_rand(0x32, 0x7A));
            if (!is_bool(strrpos('l10OoI', $char)))
                $char = '=';
        } while (($char > '9' && $char < 'A') ||
            ($char > 'Z' && $char < 'a'));
        $newpass .= $char;
    }

    return $newpass;
}

function pass_md5($pass, $full = false)
{
    if ($full)
        $pass = md5($pass);

    return md5(md5(CONF_SALT) . $pass);
}

function post_val($val)
{
//   if ( !( $flag & DBF_NOHTML ))
    if (is_array($val)) {
        foreach ($val as &$ival)
            $ival = post_val($ival);
    } else {
        if (CONF_QUOTES)
            $val = stripslashes($val);
//  		$val = htmlspecialchars( $val, ENT_QUOTES /*ENT_NOQUOTES*/, 'UTF-8' );
    }
    //stripslashes($val));
//   $val = mysqli_real_escape_string( $val );
    return $val;
}

function post($name, $default = '', $get = false)
{
    if ($get)
        $val = (!isset($_GET[$name]) ? $default : $_GET[$name]);
    else
        $val = (!isset($_POST[$name]) ? $default : $_POST[$name]);

    return post_val($val);
}

function get($name, $default = '')
{
    return post($name, $default, true);
}

function pars_list($list, $src)
{
    $alist = explode(',', $list);
    $ret = array();
    foreach ($alist as $ival)
        $ret[$ival] = isset($src[$ival]) ? $src[$ival] : '';

    return $ret;
}

function url_params($ignore = '', $only = '')
{
    $pars = '';
    $ignarr = array();
    $onarr = array();
    if ($ignore)
        $ignarr = explode(',', $ignore);
    if ($only)
        $onarr = explode(',', $only);
    foreach ($_GET as $gkey => $gval) {
        if (in_array($gkey, $ignarr) || $gval == '')
            continue;
        if ($onarr && !in_array($gkey, $onarr))
            continue;
        if (is_array($gval)) {
            foreach ($gval as $ival)
                $pars .= ($pars ? '&' : '') . "$gkey" . "[]=$ival";
        } else
            $pars .= ($pars ? '&' : '') . "$gkey=" . urlencode($gval);
    }

    return $pars;
}

/*
function lib_posts( $names, $named = false )
{
	$aname = explode( ',', $names );
	$ret = array();
	foreach ( $aname as $aval )
	{
		$val =  isset( $_POST[ $aval ] ) ? lib_postval( $_POST[ $aval ] ) : '';
		if ( $named )
			$ret[ $aval ] = $val;
		else
			$ret[] = $val;
	}
	return $ret;
}

function lib_get( $name, $default = '' )
{
	return lib_post( $name, $default, true );
}
*/

?>