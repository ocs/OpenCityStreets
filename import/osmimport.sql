# 
# OpenStreetMap XML Dump Importer
#
# Created by Nick Stallman <nick@nickstallman.net>
#

CREATE TABLE IF NOT EXISTS `nodes` (
  `node_id` int(10) unsigned NOT NULL,
  `node_lat` double NOT NULL,
  `node_lng` double NOT NULL,
  `node_version` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `nodes_to_ways` (
  `node_id` int(10) unsigned NOT NULL,
  `way_id` int(10) unsigned NOT NULL,
  `order` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`node_id`,`way_id`,`order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tags` (
  `tag_id` int(10) unsigned NOT NULL auto_increment,
  `tag_name` varchar(64) NOT NULL,
  `tag_value` varchar(256) NOT NULL,
  PRIMARY KEY  (`tag_id`),
  KEY `tag_name` (`tag_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tags_to_nodes` (
  `tag_id` int(10) unsigned NOT NULL,
  `node_id` int(10) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tags_to_ways` (
  `tag_id` int(10) unsigned NOT NULL,
  `way_id` int(10) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ways` (
  `way_id` int(10) unsigned NOT NULL,
  `way_name` varchar(255) NOT NULL,
  `way_version` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`way_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ways_intersections` (
  `way_1` int(10) unsigned NOT NULL,
  `way_2` int(10) unsigned NOT NULL,
  KEY `way_1` (`way_1`),
  KEY `way_2` (`way_2`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

