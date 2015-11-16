/*
 Navicat MySQL Data Transfer

 Source Server         : AWS WestCoast
 Source Server Version : 50544
 Source Host           : 54.213.68.225
 Source Database       : tenbreaths

 Target Server Version : 50544
 File Encoding         : utf-8

 Date: 11/02/2015 15:58:17 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `api_keys`
-- ----------------------------
DROP TABLE IF EXISTS `api_keys`;
CREATE TABLE `api_keys` (
  `id` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(16) NOT NULL,
  `key` varchar(50) NOT NULL,
  `confirmed` int(1) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `au_nbn_point_buffer`
-- ----------------------------
DROP TABLE IF EXISTS `au_nbn_point_buffer`;
CREATE TABLE `au_nbn_point_buffer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `raw` mediumtext NOT NULL,
  `taxon_count` int(11) NOT NULL,
  `observation_count` int(11) NOT NULL,
  `submission_id` int(16) NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `submission_id_2` (`submission_id`),
  KEY `submission_id` (`submission_id`)
) ENGINE=InnoDB AUTO_INCREMENT=174 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `au_osm_nodes`
-- ----------------------------
DROP TABLE IF EXISTS `au_osm_nodes`;
CREATE TABLE `au_osm_nodes` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `submission_id` int(16) NOT NULL,
  `raw` mediumtext NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `submission_id` (`submission_id`)
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `au_osm_reverse_geocode`
-- ----------------------------
DROP TABLE IF EXISTS `au_osm_reverse_geocode`;
CREATE TABLE `au_osm_reverse_geocode` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `submission_id` int(16) NOT NULL,
  `raw` mediumtext NOT NULL,
  `country_code` varchar(2) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `submission_id` (`submission_id`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `submissions`
-- ----------------------------
DROP TABLE IF EXISTS `submissions`;
CREATE TABLE `submissions` (
  `id` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `survey_id` varchar(50) NOT NULL,
  `survey_json` text NOT NULL,
  `surveyor_json` text NOT NULL,
  `api_key_id` int(16) NOT NULL,
  `moderated` int(1) unsigned zerofill NOT NULL DEFAULT '0',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ft_row_id` int(16) DEFAULT NULL,
  `ft_last_updated` timestamp NULL DEFAULT NULL,
  `photo` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `survey_ids_unique` (`survey_id`),
  KEY `api_key_id` (`api_key_id`)
) ENGINE=InnoDB AUTO_INCREMENT=248 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `email` varchar(50) NOT NULL,
  `display_name` varchar(50) NOT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

SET FOREIGN_KEY_CHECKS = 1;
