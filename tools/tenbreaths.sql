-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Oct 12, 2015 at 07:40 PM
-- Server version: 5.5.43-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=58 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=73 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=78 ;

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE IF NOT EXISTS `submissions` (
  `id` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `survey_id` varchar(50) NOT NULL,
  `survey_json` text NOT NULL,
  `surveyor_json` text NOT NULL,
  `api_key_id` int(16) NOT NULL,
  `photo` varchar(250) DEFAULT NULL,
  `moderated` int(1) unsigned zerofill NOT NULL DEFAULT '0',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ft_row_id` int(16) DEFAULT NULL,
  `ft_last_updated` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `survey_ids_unique` (`survey_id`),
  KEY `api_key_id` (`api_key_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=187 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `email` varchar(50) NOT NULL,
  `display_name` varchar(50) NOT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;
