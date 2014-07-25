<?php
/*
	Watcher4site 
	(c) 2014 Novostrim, OOO. http://www.novostrim.com
	License: MIT
*/

require_once '../lib/ajax_common.php';

$form = post('form');
$user = $db->getrow("select id, login,lang from ?n where pass=?s",
    CONF_PREFIX . '_users', pass_md5($form['psw'], true));

if (!$user) {
    $result['err'] = 'err_login';
} else {
    $result['success'] = true;
    $result['user'] = $user;
    cookie_set('pass', md5($form['psw']), 120);
    cookie_set('iduser', $user['id'], 120);
}

print_json($result);