-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jan 23, 2016 at 07:01 PM
-- Server version: 5.5.44-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `tenbreaths`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

CREATE TABLE IF NOT EXISTS `api_keys` (
  `id` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(16) NOT NULL,
  `key` varchar(50) NOT NULL,
  `confirmed` int(1) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_unique` (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- Table structure for table `au_nbn_point_buffer`
--

CREATE TABLE IF NOT EXISTS `au_nbn_point_buffer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `raw` mediumtext NOT NULL,
  `taxon_count` int(11) NOT NULL,
  `observation_count` int(11) NOT NULL,
  `submission_id` int(16) NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `submission_id_2` (`submission_id`),
  KEY `submission_id` (`submission_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=177 ;

-- --------------------------------------------------------

--
-- Table structure for table `au_osm_nodes`
--

CREATE TABLE IF NOT EXISTS `au_osm_nodes` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `submission_id` int(16) NOT NULL,
  `raw` mediumtext NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `submission_id` (`submission_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=127 ;

-- --------------------------------------------------------

--
-- Table structure for table `au_osm_reverse_geocode`
--

CREATE TABLE IF NOT EXISTS `au_osm_reverse_geocode` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `submission_id` int(16) NOT NULL,
  `raw` mediumtext NOT NULL,
  `country_code` varchar(2) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `submission_id` (`submission_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=135 ;

-- --------------------------------------------------------

--
-- Table structure for table `email_queue`
--

CREATE TABLE IF NOT EXISTS `email_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kind` varchar(32) DEFAULT NULL COMMENT 'e.g. login confirm or registration ',
  `created` timestamp NULL DEFAULT NULL COMMENT 'time row was created',
  `attempt` timestamp NULL DEFAULT NULL COMMENT 'time last attempt to send',
  `success` timestamp NULL DEFAULT NULL COMMENT 'time successfully sent',
  `attempt_count` int(11) NOT NULL DEFAULT '0' COMMENT 'number of attempts to send',
  `error` varchar(255) NOT NULL COMMENT 'last error reported',
  `to_address` varchar(100) DEFAULT NULL,
  `to_name` varchar(50) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL COMMENT 'email subject',
  `body` text NOT NULL COMMENT 'email body',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=25 ;

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE IF NOT EXISTS `submissions` (
  `id` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `survey_key` varchar(50) NOT NULL,
  `survey_json` text NOT NULL,
  `user_id` int(16) NOT NULL,
  `device_key` varchar(50) NOT NULL,
  `moderated` int(1) unsigned zerofill NOT NULL DEFAULT '0',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `public` tinyint(4) DEFAULT NULL,
  `photo` varchar(250) DEFAULT NULL,
  `started` bigint(11) DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `accuracy` float NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `survey_ids_unique` (`survey_key`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=270 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `email` varchar(50) NOT NULL,
  `display_name` varchar(50) NOT NULL,
  `password` varchar(50) DEFAULT NULL,
  `password_new` varchar(50) DEFAULT NULL COMMENT 'new password waiting for validation by email',
  `validation_token` varchar(100) DEFAULT NULL COMMENT 'token sent to validate email and new password',
  `validated` tinyint(1) DEFAULT NULL COMMENT 'email has been validated',
  `key` varchar(50) DEFAULT NULL,
  `access_token` varchar(100) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
