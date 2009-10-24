-- MySQL dump 10.11
--
-- Host: localhost    Database: ocs_auth
-- ------------------------------------------------------
-- Server version       5.0.51a-24+lenny1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `sessions` (
  `session_id` varchar(40) NOT NULL default '0',
  `ip_address` varchar(16) NOT NULL default '0',
  `user_agent` varchar(50) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL default '0',
  `user_data` text NOT NULL,
  PRIMARY KEY  (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `user_groups`
--

DROP TABLE IF EXISTS `user_groups`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `user_groups` (
  `group_id` tinyint(3) unsigned NOT NULL,
  `name` varchar(20) default NULL,
  `description` varchar(100) default NULL,
  PRIMARY KEY  (`group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `user_meta`
--

DROP TABLE IF EXISTS `user_meta`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `user_meta` (
  `user_id` mediumint(8) NOT NULL,
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `users` (
  `user_id` mediumint(8) unsigned NOT NULL auto_increment,
  `group_id` mediumint(8) unsigned NOT NULL,
  `ip_address` char(16) NOT NULL,
  `username` varchar(20) NOT NULL default '',
  `password` varchar(40) NOT NULL,
  `email` varchar(40) NOT NULL default '',
  `activation_code` varchar(40) NOT NULL default '0',
  `forgotten_password_code` varchar(40) NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`email`,`username`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-10-24 11:29:31
