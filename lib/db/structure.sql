SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

DROP TABLE IF EXISTS `albums`;
CREATE TABLE IF NOT EXISTS `albums` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `usid` int(11) unsigned NOT NULL,
  `picid` int(11) unsigned NOT NULL,
  `src` varchar(255) CHARACTER SET latin1 NOT NULL,
  `name` varchar(35) CHARACTER SET latin1 NOT NULL,
  `description` text CHARACTER SET latin1 NOT NULL,
  `timestamp` int(11) NOT NULL,
  `downloads` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usid` (`usid`),
  KEY `picid` (`picid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci AUTO_INCREMENT=746 ;

DROP TABLE IF EXISTS `blogs`;
CREATE TABLE IF NOT EXISTS `blogs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `usid` int(11) unsigned NOT NULL,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `content` text CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usid` (`usid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=724 ;

DROP TABLE IF EXISTS `chat`;
CREATE TABLE IF NOT EXISTS `chat` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `room` varchar(255) NOT NULL,
  `usid` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `text` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `timestamp` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2058 ;

DROP TABLE IF EXISTS `comItems`;
CREATE TABLE IF NOT EXISTS `comItems` (
  `comid` smallint(5) unsigned NOT NULL,
  `songid` int(11) unsigned NOT NULL,
  `timestamp` int(11) unsigned NOT NULL,
  `usid` int(11) unsigned NOT NULL,
  PRIMARY KEY (`comid`,`songid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `comments`;
CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `usid` int(11) unsigned NOT NULL,
  `byid` int(11) unsigned NOT NULL,
  `timestamp` int(11) NOT NULL,
  `msg` text NOT NULL,
  `replyto` int(11) unsigned NOT NULL,
  `notif` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usid` (`usid`,`byid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1925 ;

DROP TABLE IF EXISTS `compilations`;
CREATE TABLE IF NOT EXISTS `compilations` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `vol` tinyint(11) unsigned NOT NULL,
  `description` varchar(255) NOT NULL,
  `lang` varchar(2) NOT NULL,
  `picid` int(11) unsigned DEFAULT NULL,
  `timestamp` varchar(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

DROP TABLE IF EXISTS `content`;
CREATE TABLE IF NOT EXISTS `content` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `usid` int(11) unsigned NOT NULL,
  `slug` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `timestamp` int(11) NOT NULL,
  `lang` varchar(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=29 ;

DROP TABLE IF EXISTS `contest`;
CREATE TABLE IF NOT EXISTS `contest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `start` int(11) NOT NULL,
  `winner` int(11) NOT NULL,
  `users` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `downloads`;
CREATE TABLE IF NOT EXISTS `downloads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `songid` int(11) unsigned NOT NULL,
  `usid` int(11) unsigned DEFAULT '0',
  `timestamp` int(11) NOT NULL,
  `ip` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `songid` (`songid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3271527 ;

DROP TABLE IF EXISTS `friendships`;
CREATE TABLE IF NOT EXISTS `friendships` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fromid` int(11) unsigned NOT NULL,
  `toid` int(11) unsigned NOT NULL,
  `timestamp` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fromid` (`fromid`,`toid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1885 ;

DROP TABLE IF EXISTS `massMail`;
CREATE TABLE IF NOT EXISTS `massMail` (
  `email` varchar(255) NOT NULL,
  `sent` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `usid` int(11) unsigned NOT NULL,
  `byid` int(11) unsigned NOT NULL,
  `thread` int(11) unsigned NOT NULL DEFAULT '0',
  `msg` text NOT NULL,
  `timestamp` int(11) NOT NULL,
  `read` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `del` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `usid` (`usid`,`byid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1084 ;

DROP TABLE IF EXISTS `music`;
CREATE TABLE IF NOT EXISTS `music` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `usid` int(11) unsigned NOT NULL,
  `albumid` int(11) unsigned NOT NULL,
  `title` varchar(50) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `src` varchar(255) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `duration` smallint(5) unsigned NOT NULL,
  `bitrate` mediumint(11) unsigned NOT NULL,
  `size` mediumint(11) unsigned NOT NULL,
  `genres` varchar(255) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `extra` text CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `download` tinyint(1) unsigned NOT NULL,
  `r_users` int(11) unsigned NOT NULL,
  `r_total` int(11) unsigned NOT NULL,
  `timestamp` int(11) NOT NULL,
  `listens` int(11) unsigned NOT NULL,
  `downloads` int(11) unsigned NOT NULL,
  `contest` int(11) NOT NULL,
  `votes` int(11) NOT NULL,
  `rev` varchar(100) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `usid` (`usid`,`albumid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4144 ;

DROP TABLE IF EXISTS `newLoc`;
CREATE TABLE IF NOT EXISTS `newLoc` (
  `usid` int(11) unsigned NOT NULL,
  `pname` varchar(255) NOT NULL,
  `pdesc` text NOT NULL,
  `ptest` varchar(255) NOT NULL,
  `times` varchar(255) NOT NULL,
  `style` varchar(255) NOT NULL,
  `about` text NOT NULL,
  `timestamp` int(11) NOT NULL,
  UNIQUE KEY `usid` (`usid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `newMusic`;
CREATE TABLE IF NOT EXISTS `newMusic` (
  `id` int(11) unsigned NOT NULL,
  `usid` int(11) unsigned NOT NULL,
  `albumid` int(11) unsigned NOT NULL,
  `title` varchar(50) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `src` varchar(255) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `duration` smallint(5) unsigned NOT NULL,
  `bitrate` mediumint(11) unsigned NOT NULL,
  `size` mediumint(11) unsigned NOT NULL,
  `genres` varchar(255) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `extra` text CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `download` tinyint(1) unsigned NOT NULL,
  `r_users` int(11) unsigned NOT NULL,
  `r_total` int(11) unsigned NOT NULL,
  `timestamp` int(11) NOT NULL,
  `listens` int(11) unsigned NOT NULL,
  `downloads` int(11) unsigned NOT NULL,
  `rev` varchar(100) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `usid` (`usid`,`albumid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `pics`;
CREATE TABLE IF NOT EXISTS `pics` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `src` varchar(255) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `title` varchar(25) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1697 ;

DROP TABLE IF EXISTS `radioDjs`;
CREATE TABLE IF NOT EXISTS `radioDjs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `usid` int(11) unsigned NOT NULL,
  `program` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `timestamp` int(11) unsigned NOT NULL,
  `user` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `level` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usid` (`usid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=65 ;

DROP TABLE IF EXISTS `radioHorario`;
CREATE TABLE IF NOT EXISTS `radioHorario` (
  `day` int(10) unsigned NOT NULL,
  `hour` int(10) unsigned NOT NULL,
  `loc` int(10) unsigned NOT NULL,
  KEY `day` (`day`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ratings`;
CREATE TABLE IF NOT EXISTS `ratings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `usid` int(11) unsigned NOT NULL,
  `songId` int(11) unsigned NOT NULL,
  `rating` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usid` (`usid`,`songId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3808 ;

DROP TABLE IF EXISTS `temp`;
CREATE TABLE IF NOT EXISTS `temp` (
  `id` int(11) unsigned NOT NULL,
  `usid` int(11) unsigned NOT NULL,
  `albumid` int(11) unsigned NOT NULL,
  `title` varchar(50) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `src` varchar(255) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `duration` smallint(5) unsigned NOT NULL,
  `bitrate` mediumint(11) unsigned NOT NULL,
  `size` mediumint(11) unsigned NOT NULL,
  `genres` varchar(255) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `extra` text CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `download` tinyint(1) unsigned NOT NULL,
  `r_users` int(11) unsigned NOT NULL,
  `r_total` int(11) unsigned NOT NULL,
  `timestamp` int(11) NOT NULL,
  `listens` int(11) unsigned NOT NULL,
  `downloads` int(11) unsigned NOT NULL,
  `rev` varchar(100) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `usid` (`usid`,`albumid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(20) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `gender` int(1) NOT NULL,
  `state` varchar(22) NOT NULL,
  `rname` varchar(255) NOT NULL,
  `birth` int(11) NOT NULL DEFAULT '0',
  `jtime` int(11) NOT NULL DEFAULT '0',
  `ltime` int(11) NOT NULL DEFAULT '0',
  `picid` int(11) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(50) NOT NULL,
  `city` varchar(255) NOT NULL,
  `region` varchar(255) NOT NULL,
  `country` varchar(3) NOT NULL,
  `interests` varchar(255) NOT NULL,
  `occupation` varchar(255) NOT NULL,
  `web` varchar(255) NOT NULL,
  `views` smallint(11) unsigned NOT NULL DEFAULT '0',
  `rev` varchar(255) NOT NULL DEFAULT '0',
  `level` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `featured` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `picid` (`picid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8460 ;

DROP TABLE IF EXISTS `votes`;
CREATE TABLE IF NOT EXISTS `votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` bigint(20) NOT NULL,
  `song` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`,`song`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3282 ;
