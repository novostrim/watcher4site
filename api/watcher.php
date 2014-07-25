<?php
/*
	Watcher4site 
	(c) 2014 Novostrim, OOO. http://www.novostrim.com
	License: MIT
*/

define('STAT_OK', 0);
define('STAT_NEW', 3);
define('STAT_MOD', 2);
define('STAT_DEL', 1);


$start = 0;
$task = array();
$ext = array();
$ignext = array();
$ignwild = array();
$ignpath = array();

function match_wildcard($source, $pattern)
{
    $pattern = preg_quote($pattern, '/');
    $pattern = str_replace('\*', '.*', $pattern);

    return preg_match('/^' . $pattern . '$/i', $source);
}

function is_ignore($fname)
{
    global $ignwild;

    if ($ignwild) {
        foreach ($ignwild as $iw)
            if (match_wildcard($fname, $iw))
                return true;
    }

    return false;
}

function search_del($idowner, $dir)
{
    global $db, $task, $ext, $ignpath, $ignext;

    $flist = $db->getall("select id, name, isfolder from ?n where idtask < ?s && idowner=?s",
        CONF_PREFIX . '_files', $task['id'], $idowner);
//	print "$idowner=$dir";
//	print_r( $flist );
    $path = trim(substr($dir, strlen(CONF_DOCROOT)), "/\\");
    foreach ($flist as $ifl) {
        $fname = "$dir/$ifl[name]";
        if ($ifl['isfolder']) {
            if ($ignpath && in_array($path ? "$path/$ifl[name]" : $ifl['name'], $ignpath))
                continue;
            if (!file_exists($fname) || !is_dir($fname)) {
                search_del($ifl['id'], $fname);
                $db->update(CONF_PREFIX . '_files', array('status' => STAT_DEL, 'idtask' => $task['id']),
                    array('nuptime=NOW()'), $ifl['id']);
            }
        } else {
            $extension = pathinfo($ifl['name'], PATHINFO_EXTENSION);
            if ($ext && !in_array($extension, $ext))
                continue;
            if ($ignext && in_array($extension, $ignext))
                continue;
            if (is_ignore($ifl['name']))
                continue;
            if (!file_exists($fname))
                $db->update(CONF_PREFIX . '_files', array('status' => STAT_DEL, 'idtask' => $task['id']),
                    array('nuptime=NOW()'), $ifl['id']);
        }
    }

}

function scan_dir($dir, $idowner, $newfld)
{
    global $db, $task, $ext, $ignpath, $ignext, $start;

    $d = dir($dir);
    if ($d === false) {
        search_del($idowner, $dir);

        return true;
    }
    $ret = true;
    $path = trim(substr($dir, strlen(CONF_DOCROOT)), "/\\");
    while (false !== ($entry = $d->read())) {
        if ($entry != '.' && $entry != '..') {
            if (time() - $start > $task['limit']) {
                $ret = false;
                break;
            }
            $name = $dir . '/' . $entry;
            $isdir = is_dir($name);
            $file = $db->getrow("select * from ?n where idowner=?s && name=?s && isfolder" .
                ($isdir ? '>0' : '=0'), CONF_PREFIX . '_files', $idowner, $entry);
            if ($file && $file['idtask'] == $task['id'])
                continue;
            $perm = fileperms($name);
            $pars = array('idowner' => $idowner, 'name' => $entry, 'nperm' => $perm,
                          'idtask'  => $task['id'], 'status' => STAT_OK);
            $upd = array('idtask' => $task['id'], 'nperm' => $perm, 'status' => STAT_OK);
//		  	$log = array( 'idtask' => $task['id'], 'status' => 100 );
            if ($isdir) {
                if ($ignpath && in_array($path ? "$path/$entry" : $entry, $ignpath))
                    continue;
                if (!$file) {
                    $pars['isfolder'] = $newfld ? 2 : 1;
                    if (!$newfld) //&& $file['perm'] && $file['perm'] != $perm )
                    {
                        $pars['status'] = STAT_NEW;
                        $upd['status'] = STAT_NEW;
                    }
                    $idi = $db->insert(CONF_PREFIX . '_files', $pars, array('uptime=NOW()', 'nuptime=NOW()'), true);
                    $file = $db->getrow("select * from ?n where id=?s", CONF_PREFIX . '_files', $idi);
                }

                if (!scan_dir($name, $file['id'], $file['isfolder'] == 2)) {
                    $ret = false;
                    break;
                }
                if ($file['isfolder'] == 2)
                    $upd['isfolder'] = 1;
                if ($file['perm'] && $file['perm'] != $perm)
                    $upd['status'] = STAT_MOD;
                if ($file && $file['status'] == STAT_NEW)
                    $upd['status'] = STAT_NEW;
                $db->update(CONF_PREFIX . '_files', $upd, array('nuptime=NOW()'), $file['id']);
            } else {
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                if ($ext && !in_array($extension, $ext))
                    continue;
                if ($ignext && in_array($extension, $ignext))
                    continue;
                if (is_ignore($entry))
                    continue;
                $perm = fileperms($name);
                $size = filesize($name);
                $time = filemtime($name);
                $hash = $task['hash'] ? md5_file($name) : 0;
                if (!$file) {
                    $pars['nsize'] = $size;
                    $pars['ntime'] = $time;
                    $pars['nhash'] = $hash;
                    if ($newfld) {
                        $pars['perm'] = $perm;
                        $pars['size'] = $size;
                        $pars['time'] = $time;
                        $pars['hash'] = $hash;
                    }
                    if (!$newfld)
                        $pars['status'] = STAT_NEW;
                    $idi = $db->insert(CONF_PREFIX . '_files', $pars, array('uptime=NOW()', 'nuptime=NOW()'), true);
                } else {
                    $upd['nperm'] = $perm;
                    $upd['nsize'] = $size;
                    $upd['ntime'] = $time;
                    $upd['nhash'] = $hash;
                    if (($file['size'] && $file['size'] != $size) ||
                        ($file['perm'] && $file['perm'] != $perm) ||
                        ($file['time'] && $file['time'] != $time) ||
                        ($file['hash'] && $hash && $file['hash'] != $hash)
                    ) {
                        $upd['status'] = STAT_MOD;
                    }
                    if ($file['status'] == STAT_NEW)
                        $upd['status'] = STAT_NEW;
                    $db->update(CONF_PREFIX . '_files', $upd, array('nuptime=NOW()'), $file['id']);
                }
            }
//		  	if ( $log['status'] <= 1 )
//				$db->insert( 'w_log', $log, array( 'time=NOW()'));
        }
    }
    // Detect deleted files
    search_del($idowner, $dir);

    $d->close();

    return $ret;
}

function watcher($params = '')
{
    global $db, $task, $ext, $ignpath, $ignext, $start, $ignwild;

    $dbtask = CONF_PREFIX . '_task';
    $default = array('ext' => '', 'hash' => 0, 'ignext' => '', 'ignpath' => ''); //, 'limit' => 10 );

    $task = $db->getrow("select * from ?n order by id desc", $dbtask);
    $update = false;
    $fpars = array('ext', 'hash', 'ignext', 'ignpath', 'limit');
    if ($params)
        $params = array_intersect_key($params, $default);

    foreach ($fpars as $ipar) {
        if (isset($params[$ipar]))
            $pars[$ipar] = $params[$ipar];
        else
            $pars[$ipar] = isset($task[$ipar]) ? $task[$ipar] : $default[$ipar];
    }
    if ($task) {
        foreach (array('ext', 'hash', 'ignext', 'ignpath') as $ipar) {
            if ($pars[$ipar] != $task[$ipar]) {
                $update = true;
                break;
            }
        }
    }
    $pars['limit'] = 10;
    if (!$task || $task['closed'] || $update) {
        $idtask = $db->insert($dbtask, $pars, array('start=NOW(),lastrun=NOW()'), true);
        $task = $db->getrow("select * from $dbtask where id=?s", $idtask);
    } else
        $db->update($dbtask, '', array('lastrun=NOW()'), $task['id']);

    $ext = $task['ext'] ? explode(',', $task['ext']) : '';
    $ignwild = $task['ignext'] ? explode(',', $task['ignext']) : '';
    if ($ignwild) {
        foreach ($ignwild as $ik => $iv) {
            if (strpos($iv, '*') === false && strpos($iv, '.') === false) {
                $ignext[] = $iv;
                unset($ignwild[$ik]);
            }
        }
    }
    $ignpath = $task['ignpath'] ? explode(',', $task['ignpath']) : '';
    if ($ignpath) {
        foreach ($ignpath as &$ipath)
            $ipath = trim($ipath, "/\\");
    }
    $start = time();
//print __FILE__.'='.$_SERVER['HTTP_HOST'].'='.$_SERVER['SCRIPT_FILENAME'].'='.$_SERVER['DOCUMENT_ROOT'];
    if (scan_dir(CONF_DOCROOT, 0, $task['id'] == 1)) {
        $db->update($dbtask, array('closed' => 1), array('finish=NOW()'), $task['id']);

        return 1;
    }

//	$end = time();
    return 0;
}

?>
