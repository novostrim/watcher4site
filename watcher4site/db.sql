CREATE TABLE IF NOT EXISTS `app_db` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `pass` varchar(32) NOT NULL,
  `lang` varchar(10) NOT NULL,
  `ctime` datetime NOT NULL,
  `apitoken` varchar(32) NOT NULL,
  `isalias` tinyint(3) unsigned NOT NULL,
  `prefix` varchar(16) NOT NULL,
  `sqlimport` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
##
CREATE TABLE IF NOT EXISTS `xxx_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(32) NOT NULL,
  `pass` varchar(32) NOT NULL,
  `email` varchar(32) NOT NULL,
  `idgroup` smallint(5) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  `lang` varchar(10) NOT NULL,
  `ctime` datetime NOT NULL,
  `uptime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `login` (`login`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
##
CREATE TABLE IF NOT EXISTS `xxx_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(64) NOT NULL,
  `idowner` int(10) unsigned NOT NULL,
  `sort` int(11) NOT NULL,
  `url` varchar(128) NOT NULL,
  `hint` varchar(128) NOT NULL,
  `idparent` int(10) unsigned NOT NULL,
  `isfolder` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idparent` (`idparent`,`sort`,`title`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
##
CREATE TABLE IF NOT EXISTS `xxx_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idowner` int(10) unsigned NOT NULL,
  `name` varchar(96) NOT NULL,
  `perm` int(10) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `size` int(10) unsigned NOT NULL,
  `hash` varchar(32) NOT NULL,
  `idtask` int(10) unsigned NOT NULL,
  `isfolder` tinyint(3) unsigned NOT NULL,
  `nperm` int(10) unsigned NOT NULL,
  `ntime` int(10) unsigned NOT NULL,
  `nsize` int(10) unsigned NOT NULL,
  `nhash` varchar(32) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `uptime` datetime NOT NULL,
  `nuptime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idowner` (`idowner`,`name`),
  KEY `idtask` (`idtask`,`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
##
CREATE TABLE IF NOT EXISTS `xxx_task` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `start` datetime NOT NULL,
  `finish` datetime NOT NULL,
  `closed` tinyint(3) unsigned NOT NULL,
  `ext` varchar(128) NOT NULL,
  `hash` tinyint(4) NOT NULL,
  `ignext` varchar(128) NOT NULL,
  `ignpath` text NOT NULL,
  `limit` int(10) unsigned NOT NULL,
  `lastrun` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
##
CREATE TABLE IF NOT EXISTS `xxx_app` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `value` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
##
INSERT INTO `xxx_app` (`id`, `name`, `value`) VALUES
(1, 'uptime', ''),
(2, 'nfyemail', ''),
(3, 'nfyurl', ''),
(4, 'nfyscript', ''),
(5, 'latestmod', ''),
(6, 'emailtext', '');