-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jan 08, 2018 at 05:06 PM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `tali_db_template`
--

-- --------------------------------------------------------

--
-- Table structure for table `tali_admin_accounts`
--

CREATE TABLE IF NOT EXISTS `tali_admin_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level` tinyint(4) NOT NULL DEFAULT '1',
  `username` varchar(40) NOT NULL,
  `password` varchar(40) NOT NULL,
  `password_reset_token` varchar(40) NOT NULL,
  `email` varchar(40) NOT NULL,
  `personnel_id` int(40) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=31 ;

-- --------------------------------------------------------

--
-- Table structure for table `tali_admin_permissions`
--

CREATE TABLE IF NOT EXISTS `tali_admin_permissions` (
  `level` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tali_homeslider`
--

CREATE TABLE IF NOT EXISTS `tali_homeslider` (
  `image_id` int(11) NOT NULL AUTO_INCREMENT,
  `image` text NOT NULL,
  `text` text NOT NULL,
  `weight` tinyint(4) NOT NULL,
  PRIMARY KEY (`image_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `tali_mailing_list`
--

CREATE TABLE IF NOT EXISTS `tali_mailing_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `list` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tali_master_history`
--

CREATE TABLE IF NOT EXISTS `tali_master_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` timestamp NULL DEFAULT NULL,
  `username_id` int(11) NOT NULL,
  `module` varchar(40) NOT NULL,
  `item_id` int(11) NOT NULL,
  `event` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1694 ;

-- --------------------------------------------------------

--
-- Table structure for table `tali_modules`
--

CREATE TABLE IF NOT EXISTS `tali_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(40) NOT NULL,
  `permission` varchar(255) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `tali_news`
--

CREATE TABLE IF NOT EXISTS `tali_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` timestamp NULL DEFAULT NULL,
  `author` tinytext NOT NULL,
  `title` tinytext NOT NULL,
  `body` text NOT NULL,
  `history` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=31 ;

-- --------------------------------------------------------

--
-- Table structure for table `tali_pages`
--

CREATE TABLE IF NOT EXISTS `tali_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `body` text NOT NULL,
  `time` timestamp NULL DEFAULT NULL,
  `history` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_awards`
--

CREATE TABLE IF NOT EXISTS `tali_personnel_awards` (
  `award_id` int(11) NOT NULL AUTO_INCREMENT,
  `awardclass_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `weight` int(11) NOT NULL,
  PRIMARY KEY (`award_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=64 ;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_awards_classes`
--

CREATE TABLE IF NOT EXISTS `tali_personnel_awards_classes` (
  `awardclass_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `weight` int(11) NOT NULL,
  PRIMARY KEY (`awardclass_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_awards_record`
--

CREATE TABLE IF NOT EXISTS `tali_personnel_awards_record` (
  `awardrecord_id` int(11) NOT NULL AUTO_INCREMENT,
  `personnel_id` int(11) NOT NULL,
  `award_id` int(11) NOT NULL,
  `date_awarded` date NOT NULL,
  `record` text NOT NULL,
  PRIMARY KEY (`awardrecord_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=766 ;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_competition`
--

CREATE TABLE IF NOT EXISTS `tali_personnel_competition` (
  `competition_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `location` varchar(255) NOT NULL,
  `outcome` text NOT NULL,
  `attended` text NOT NULL,
  PRIMARY KEY (`competition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_competition_record`
--

CREATE TABLE IF NOT EXISTS `tali_personnel_competition_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `personnel_id` int(11) NOT NULL,
  `competition_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_designations`
--

CREATE TABLE IF NOT EXISTS `tali_personnel_designations` (
  `designation_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `leader_personnel_id` int(11) NOT NULL,
  `reportsto_designation_id` int(11) NOT NULL,
  `weight` int(11) NOT NULL,
  `inactive` tinyint(1) NOT NULL,
  PRIMARY KEY (`designation_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=64 ;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_drillreports`
--

CREATE TABLE IF NOT EXISTS `tali_personnel_drillreports` (
  `drillreport_id` int(11) NOT NULL AUTO_INCREMENT,
  `designation_id` int(11) NOT NULL,
  `date_drill` date NOT NULL,
  `date_report` date NOT NULL,
  `attended` varchar(255) NOT NULL,
  `excused` varchar(255) NOT NULL,
  `absent` varchar(255) NOT NULL,
  `comments` text NOT NULL,
  `smf_ids` tinytext,
  PRIMARY KEY (`drillreport_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=80 ;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_ranks`
--

CREATE TABLE IF NOT EXISTS `tali_personnel_ranks` (
  `rank_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `abbreviation` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `weight` int(11) NOT NULL,
  PRIMARY KEY (`rank_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=37 ;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_roles`
--

CREATE TABLE IF NOT EXISTS `tali_personnel_roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `weight` int(11) NOT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_roster`
--

CREATE TABLE IF NOT EXISTS `tali_personnel_roster` (
  `personnel_id` int(11) NOT NULL AUTO_INCREMENT,
  `rank_id` tinyint(4) NOT NULL,
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `nickname` varchar(32) NOT NULL,
  `status_id` tinyint(4) NOT NULL,
  `designation_id` tinyint(4) NOT NULL,
  `role_id` tinyint(4) NOT NULL,
  `email` varchar(255) NOT NULL,
  `othercontact` varchar(255) NOT NULL,
  `location` varchar(32) NOT NULL,
  `biography` text NOT NULL,
  `dateofbirth` date DEFAULT NULL,
  `date_enlisted` date DEFAULT NULL,
  `date_promoted` date DEFAULT NULL,
  `date_discharged` date DEFAULT NULL,
  `points` int(3) NOT NULL,
  `drills_attended` smallint(6) NOT NULL,
  `drills_excused` smallint(6) NOT NULL,
  `drills_absent` smallint(6) NOT NULL,
  `discharged` tinyint(1) NOT NULL,
  `discharged_designation` text NOT NULL,
  PRIMARY KEY (`personnel_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=283 ;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_service_record`
--

CREATE TABLE IF NOT EXISTS `tali_personnel_service_record` (
  `servicerecord_id` int(11) NOT NULL AUTO_INCREMENT,
  `personnel_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `record` text NOT NULL,
  PRIMARY KEY (`servicerecord_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=525 ;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_statuses`
--

CREATE TABLE IF NOT EXISTS `tali_personnel_statuses` (
  `status_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `weight` int(11) NOT NULL,
  PRIMARY KEY (`status_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
