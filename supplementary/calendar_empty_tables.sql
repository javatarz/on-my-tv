-- phpMyAdmin SQL Dump
-- version 3.3.7deb5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 09, 2012 at 04:04 PM
-- Server version: 5.1.49
-- PHP Version: 5.3.3-7+squeeze3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `calendar`
--

-- --------------------------------------------------------

--
-- Table structure for table `episodes`
--

CREATE TABLE IF NOT EXISTS `episodes` (
  `ep_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ep_tvrage_id` int(11) unsigned DEFAULT NULL,
  `sh_id` smallint(5) unsigned NOT NULL,
  `ep_season` tinyint(3) unsigned NOT NULL,
  `ep_date` datetime DEFAULT NULL,
  `ep_number` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ep_prod_number` varchar(255) DEFAULT NULL,
  `ep_title` varchar(255) NOT NULL,
  `ep_summary_url` varchar(255) DEFAULT NULL,
  `ep_tvrage_url` varchar(255) NOT NULL,
  `ep_screen_cap` varchar(255) DEFAULT NULL,
  `ep_summary` text,
  `ep_ratetotal` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `ep_votes` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ep_id`),
  UNIQUE KEY `show_id` (`sh_id`,`ep_season`,`ep_number`),
  KEY `ep_date` (`ep_date`),
  KEY `sh_id` (`sh_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18992298 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `usr_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usr_uid` char(32) NOT NULL,
  `usr_name` varchar(120) DEFAULT NULL,
  `usr_password` char(32) DEFAULT NULL,
  `usr_style` smallint(3) unsigned NOT NULL,
  `s_daily_numbers` tinyint(1) NOT NULL,
  `s_sunday_first` tinyint(1) NOT NULL DEFAULT '0',
  `s_daily_airtimes` tinyint(1) NOT NULL,
  `s_daily_networks` tinyint(1) NOT NULL,
  `s_daily_epnames` tinyint(1) NOT NULL DEFAULT '0',
  `s_popups` tinyint(1) NOT NULL DEFAULT '1',
  `s_wunwatched` tinyint(1) NOT NULL DEFAULT '1',
  `s_disableads` tinyint(1) NOT NULL DEFAULT '0',
  `s_sortbyname` tinyint(1) NOT NULL DEFAULT '0',
  `s_24hour` tinyint(1) NOT NULL DEFAULT '1',
  `s_premium` tinyint(1) NOT NULL DEFAULT '0',
  `usr_timezone` enum('GMT','Europe/London','CET','US/Alaska','US/Pacific','US/Mountain','America/Phoenix','US/Central','US/Eastern','US/Hawaii','Canada/Atlantic','Brazil/East','Chile/Continental','Europe/Stockholm','Europe/Helsinki','Asia/Jerusalem','Asia/Dubai','Asia/Hong_Kong','Asia/Bangkok','Australia/West','Australia/North','Australia/South','Australia/NSW','Australia/Queensland','Pacific/Auckland') NOT NULL,
  `usr_last_filter_update` datetime NOT NULL,
  `usr_last_login` datetime NOT NULL,
  `usr_ip_address` varchar(25) NOT NULL,
  PRIMARY KEY (`usr_id`),
  KEY `usr_uid` (`usr_uid`(8)),
  KEY `usr_name` (`usr_name`,`usr_password`),
  KEY `usr_style` (`usr_style`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4488386 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_selections`
--

CREATE TABLE IF NOT EXISTS `user_selections` (
  `sh_id` smallint(5) unsigned NOT NULL,
  `usr_id` int(10) unsigned NOT NULL,
  KEY `usr_id` (`usr_id`),
  KEY `sh_id` (`sh_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_votes`
--

CREATE TABLE IF NOT EXISTS `user_votes` (
  `ep_id` int(10) unsigned NOT NULL,
  `usr_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ep_id`,`usr_id`),
  KEY `usr_id` (`usr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_watched`
--

CREATE TABLE IF NOT EXISTS `user_watched` (
  `sh_id` smallint(5) unsigned NOT NULL,
  `usr_id` int(10) unsigned NOT NULL,
  `ep_season` tinyint(3) unsigned NOT NULL,
  `ep_episode` tinyint(3) unsigned NOT NULL,
  `ts` date NOT NULL,
  KEY `usr_id` (`usr_id`),
  KEY `ep_season` (`ep_season`,`ep_episode`),
  KEY `sh_id` (`sh_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `episodes`
--
ALTER TABLE `episodes`
  ADD CONSTRAINT `episodes_ibfk_1` FOREIGN KEY (`sh_id`) REFERENCES `shows` (`sh_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`usr_style`) REFERENCES `styles` (`sty_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_selections`
--
ALTER TABLE `user_selections`
  ADD CONSTRAINT `user_selections_ibfk_3` FOREIGN KEY (`usr_id`) REFERENCES `users` (`usr_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_selections_ibfk_2` FOREIGN KEY (`sh_id`) REFERENCES `shows` (`sh_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_votes`
--
ALTER TABLE `user_votes`
  ADD CONSTRAINT `user_votes_ibfk_4` FOREIGN KEY (`usr_id`) REFERENCES `users` (`usr_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_votes_ibfk_3` FOREIGN KEY (`ep_id`) REFERENCES `episodes` (`ep_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_watched`
--
ALTER TABLE `user_watched`
  ADD CONSTRAINT `user_watched_ibfk_3` FOREIGN KEY (`usr_id`) REFERENCES `users` (`usr_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_watched_ibfk_2` FOREIGN KEY (`sh_id`) REFERENCES `shows` (`sh_id`) ON DELETE CASCADE ON UPDATE CASCADE;
