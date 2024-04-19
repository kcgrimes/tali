-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 19, 2024 at 04:41 PM
-- Server version: 8.0.36
-- PHP Version: 7.4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tali_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
CREATE TABLE IF NOT EXISTS `inventory` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` mediumtext NOT NULL,
  `description` mediumtext NOT NULL,
  `manufacturer` mediumtext NOT NULL,
  `model` mediumtext NOT NULL,
  `vendor` mediumtext NOT NULL,
  `item_use` varchar(11) NOT NULL,
  `item_condition` varchar(11) NOT NULL,
  `item_category` varchar(11) NOT NULL,
  `assigned_unit` varchar(11) NOT NULL,
  `location_general` varchar(11) NOT NULL,
  `location_specific` mediumtext NOT NULL,
  `date_in_service` date DEFAULT NULL,
  `lifespan_date` date DEFAULT NULL,
  `date_retired` date DEFAULT NULL,
  `funding_source` varchar(11) NOT NULL,
  `funding_restriction` mediumtext NOT NULL,
  `value` int DEFAULT NULL,
  `annual_cost` int DEFAULT NULL,
  `maintenance` mediumtext NOT NULL,
  `notes` mediumtext NOT NULL,
  `status` int NOT NULL,
  `checkedoutby` int NOT NULL,
  `checkedouttype` int NOT NULL,
  `checkedouttype_id` int NOT NULL,
  `checkedoutpurpose` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=506 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_location_general`
--

DROP TABLE IF EXISTS `inventory_location_general`;
CREATE TABLE IF NOT EXISTS `inventory_location_general` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_location_specific`
--

DROP TABLE IF EXISTS `inventory_location_specific`;
CREATE TABLE IF NOT EXISTS `inventory_location_specific` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_master_history`
--

DROP TABLE IF EXISTS `inventory_master_history`;
CREATE TABLE IF NOT EXISTS `inventory_master_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `username_id` int NOT NULL,
  `item_id` int NOT NULL,
  `event` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=415 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_packages`
--

DROP TABLE IF EXISTS `inventory_packages`;
CREATE TABLE IF NOT EXISTS `inventory_packages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` mediumtext NOT NULL,
  `contents` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tali_admin_accounts`
--

DROP TABLE IF EXISTS `tali_admin_accounts`;
CREATE TABLE IF NOT EXISTS `tali_admin_accounts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `level` tinyint NOT NULL DEFAULT '1',
  `username` varchar(40) NOT NULL,
  `password` varchar(40) NOT NULL,
  `password_reset_token` varchar(40) NOT NULL,
  `email` varchar(40) NOT NULL,
  `personnel_id` varchar(40) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tali_admin_accounts`
--

INSERT INTO `tali_admin_accounts` (`id`, `level`, `username`, `password`, `password_reset_token`, `email`, `personnel_id`) VALUES
(1, 1, 'admin', '5f4dcc3b5aa765d61d8327deb882cf99', '', '', '0');

-- --------------------------------------------------------

--
-- Table structure for table `tali_admin_permissions`
--

DROP TABLE IF EXISTS `tali_admin_permissions`;
CREATE TABLE IF NOT EXISTS `tali_admin_permissions` (
  `level` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tali_admin_permissions`
--

INSERT INTO `tali_admin_permissions` (`level`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `tali_homeslider`
--

DROP TABLE IF EXISTS `tali_homeslider`;
CREATE TABLE IF NOT EXISTS `tali_homeslider` (
  `image_id` int NOT NULL AUTO_INCREMENT,
  `image` mediumtext NOT NULL,
  `text` mediumtext NOT NULL,
  `weight` tinyint NOT NULL,
  PRIMARY KEY (`image_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tali_mailing_list`
--

DROP TABLE IF EXISTS `tali_mailing_list`;
CREATE TABLE IF NOT EXISTS `tali_mailing_list` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `list` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tali_master_history`
--

DROP TABLE IF EXISTS `tali_master_history`;
CREATE TABLE IF NOT EXISTS `tali_master_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `time` timestamp NULL DEFAULT NULL,
  `username_id` int NOT NULL,
  `module` varchar(40) NOT NULL,
  `item_id` int NOT NULL,
  `event` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1719 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tali_modules`
--

DROP TABLE IF EXISTS `tali_modules`;
CREATE TABLE IF NOT EXISTS `tali_modules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `module` varchar(40) NOT NULL,
  `permission` varchar(255) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tali_modules`
--

INSERT INTO `tali_modules` (`id`, `module`, `permission`) VALUES
(1, 'TALI_Admin_Accounts', '1'),
(2, 'TALI_Admin_Permissions', '1'),
(3, 'TALI_News', '1'),
(4, 'TALI_Pages', '1'),
(5, 'TALI_Versions', '1'),
(6, 'TALI_Master_History', '1'),
(7, 'TALI_Home_Slider', '1'),
(8, 'TALI_Personnel', '1'),
(9, 'TALI_Mailing_List', '1'),
(10, 'Inventory_Browser', '1');

-- --------------------------------------------------------

--
-- Table structure for table `tali_news`
--

DROP TABLE IF EXISTS `tali_news`;
CREATE TABLE IF NOT EXISTS `tali_news` (
  `id` int NOT NULL AUTO_INCREMENT,
  `time` timestamp NULL DEFAULT NULL,
  `author` text NOT NULL,
  `title` text NOT NULL,
  `body` mediumtext NOT NULL,
  `history` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tali_pages`
--

DROP TABLE IF EXISTS `tali_pages`;
CREATE TABLE IF NOT EXISTS `tali_pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` mediumtext NOT NULL,
  `body` mediumtext NOT NULL,
  `time` timestamp NULL DEFAULT NULL,
  `history` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_awards`
--

DROP TABLE IF EXISTS `tali_personnel_awards`;
CREATE TABLE IF NOT EXISTS `tali_personnel_awards` (
  `award_id` int NOT NULL AUTO_INCREMENT,
  `awardclass_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `weight` int NOT NULL,
  PRIMARY KEY (`award_id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_awards_classes`
--

DROP TABLE IF EXISTS `tali_personnel_awards_classes`;
CREATE TABLE IF NOT EXISTS `tali_personnel_awards_classes` (
  `awardclass_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `weight` int NOT NULL,
  PRIMARY KEY (`awardclass_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_awards_record`
--

DROP TABLE IF EXISTS `tali_personnel_awards_record`;
CREATE TABLE IF NOT EXISTS `tali_personnel_awards_record` (
  `awardrecord_id` int NOT NULL AUTO_INCREMENT,
  `personnel_id` int NOT NULL,
  `award_id` int NOT NULL,
  `date_awarded` date NOT NULL,
  `record` mediumtext NOT NULL,
  PRIMARY KEY (`awardrecord_id`)
) ENGINE=InnoDB AUTO_INCREMENT=786 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_competition`
--

DROP TABLE IF EXISTS `tali_personnel_competition`;
CREATE TABLE IF NOT EXISTS `tali_personnel_competition` (
  `competition_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `location` varchar(255) NOT NULL,
  `outcome` mediumtext NOT NULL,
  `attended` mediumtext NOT NULL,
  PRIMARY KEY (`competition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_competition_record`
--

DROP TABLE IF EXISTS `tali_personnel_competition_record`;
CREATE TABLE IF NOT EXISTS `tali_personnel_competition_record` (
  `id` int NOT NULL AUTO_INCREMENT,
  `personnel_id` int NOT NULL,
  `competition_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_designations`
--

DROP TABLE IF EXISTS `tali_personnel_designations`;
CREATE TABLE IF NOT EXISTS `tali_personnel_designations` (
  `designation_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `leader_personnel_id` int NOT NULL,
  `reportsto_designation_id` int NOT NULL,
  `weight` int NOT NULL,
  `inactive` tinyint(1) NOT NULL,
  PRIMARY KEY (`designation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_drillreports`
--

DROP TABLE IF EXISTS `tali_personnel_drillreports`;
CREATE TABLE IF NOT EXISTS `tali_personnel_drillreports` (
  `drillreport_id` int NOT NULL AUTO_INCREMENT,
  `designation_id` int NOT NULL,
  `date_drill` date NOT NULL,
  `date_report` date NOT NULL,
  `attended` varchar(255) NOT NULL,
  `excused` varchar(255) NOT NULL,
  `absent` varchar(255) NOT NULL,
  `comments` mediumtext NOT NULL,
  `special_id` text NOT NULL,
  PRIMARY KEY (`drillreport_id`)
) ENGINE=InnoDB AUTO_INCREMENT=348 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_ranks`
--

DROP TABLE IF EXISTS `tali_personnel_ranks`;
CREATE TABLE IF NOT EXISTS `tali_personnel_ranks` (
  `rank_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `abbreviation` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `weight` int NOT NULL,
  PRIMARY KEY (`rank_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_roles`
--

DROP TABLE IF EXISTS `tali_personnel_roles`;
CREATE TABLE IF NOT EXISTS `tali_personnel_roles` (
  `role_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `weight` int NOT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_roster`
--

DROP TABLE IF EXISTS `tali_personnel_roster`;
CREATE TABLE IF NOT EXISTS `tali_personnel_roster` (
  `personnel_id` int NOT NULL AUTO_INCREMENT,
  `rank_id` tinyint NOT NULL,
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `nickname` varchar(32) NOT NULL,
  `status_id` tinyint NOT NULL,
  `designation_id` tinyint NOT NULL,
  `role_id` tinyint NOT NULL,
  `email` varchar(255) NOT NULL,
  `uniform` varchar(255) NOT NULL,
  `uniform_modifiable` varchar(255) NOT NULL,
  `othercontact` varchar(255) NOT NULL,
  `location` varchar(32) NOT NULL,
  `biography` mediumtext NOT NULL,
  `dateofbirth` date DEFAULT NULL,
  `date_enlisted` date DEFAULT NULL,
  `date_promoted` date DEFAULT NULL,
  `date_discharged` date DEFAULT NULL,
  `points` int NOT NULL,
  `drills_attended` smallint NOT NULL,
  `drills_excused` smallint NOT NULL,
  `drills_absent` smallint NOT NULL,
  `discharged` tinyint(1) NOT NULL,
  `discharged_designation` mediumtext NOT NULL,
  PRIMARY KEY (`personnel_id`)
) ENGINE=InnoDB AUTO_INCREMENT=286 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_service_record`
--

DROP TABLE IF EXISTS `tali_personnel_service_record`;
CREATE TABLE IF NOT EXISTS `tali_personnel_service_record` (
  `servicerecord_id` int NOT NULL AUTO_INCREMENT,
  `personnel_id` int NOT NULL,
  `date` date NOT NULL,
  `record` mediumtext NOT NULL,
  PRIMARY KEY (`servicerecord_id`)
) ENGINE=InnoDB AUTO_INCREMENT=550 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tali_personnel_statuses`
--

DROP TABLE IF EXISTS `tali_personnel_statuses`;
CREATE TABLE IF NOT EXISTS `tali_personnel_statuses` (
  `status_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `weight` int NOT NULL,
  PRIMARY KEY (`status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
