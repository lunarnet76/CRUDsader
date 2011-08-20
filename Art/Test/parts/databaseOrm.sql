-- phpMyAdmin SQL Dump
-- version 3.3.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 05, 2011 at 06:34 PM
-- Server version: 5.1.49
-- PHP Version: 5.3.3-7+squeeze3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `artdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `address`
--

CREATE TABLE IF NOT EXISTS `address` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `company` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company` (`company`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `address2contact`
--

CREATE TABLE IF NOT EXISTS `address2contact` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `contact` bigint(20) NOT NULL,
  `address` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contact` (`contact`),
  KEY `address` (`address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE IF NOT EXISTS `company` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `hasAddress`
--

CREATE TABLE IF NOT EXISTS `hasAddress` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `address` bigint(20) NOT NULL,
  `contact` bigint(20) NOT NULL,
  `company` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `address` (`address`),
  KEY `contact` (`contact`),
  KEY `company` (`company`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

CREATE TABLE IF NOT EXISTS `login` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `hasLogin` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hasLogin` (`hasLogin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `photo`
--

CREATE TABLE IF NOT EXISTS `photo` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `contact` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contact` (`contact`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Tcontact`
--

CREATE TABLE IF NOT EXISTS `Tcontact` (
  `contact_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `designation` varchar(32) COLLATE utf8_bin NOT NULL,
  `company` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`contact_id`),
  UNIQUE KEY `unicity` (`designation`),
  KEY `company` (`company`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `address`
--
ALTER TABLE `address`
  ADD CONSTRAINT `address_ibfk_1` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `address2contact`
--
ALTER TABLE `address2contact`
  ADD CONSTRAINT `address2contact_ibfk_2` FOREIGN KEY (`address`) REFERENCES `address` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `address2contact_ibfk_1` FOREIGN KEY (`contact`) REFERENCES `Tcontact` (`contact_id`) ON DELETE CASCADE;

--
-- Constraints for table `hasAddress`
--
ALTER TABLE `hasAddress`
  ADD CONSTRAINT `hasAddress_ibfk_3` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `hasAddress_ibfk_1` FOREIGN KEY (`address`) REFERENCES `address` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hasAddress_ibfk_2` FOREIGN KEY (`contact`) REFERENCES `Tcontact` (`contact_id`) ON DELETE CASCADE;

--
-- Constraints for table `login`
--
ALTER TABLE `login`
  ADD CONSTRAINT `login_ibfk_1` FOREIGN KEY (`hasLogin`) REFERENCES `Tcontact` (`contact_id`) ON DELETE CASCADE;

--
-- Constraints for table `photo`
--
ALTER TABLE `photo`
  ADD CONSTRAINT `photo_ibfk_1` FOREIGN KEY (`contact`) REFERENCES `Tcontact` (`contact_id`) ON DELETE CASCADE;

--
-- Constraints for table `Tcontact`
--
ALTER TABLE `Tcontact`
  ADD CONSTRAINT `Tcontact_ibfk_1` FOREIGN KEY (`company`) REFERENCES `company` (`id`) ON DELETE SET NULL;


INSERT INTO `Tcontact` (`contact_id`, `designation`, `company`) VALUES
(1, 'contact1', 6),
(2, 'contact2', 6),
(3, 'contact3', 7),
(4, 'contact4', 7),
(5, 'contact5', 8);

INSERT INTO `company` (`id`, `name`) VALUES
(6, 'company1'),
(7, 'company2'),
(8, 'company3'),
(9, 'company4'),
(10, 'company5')
;

INSERT INTO `artdb`.`login` (`id`, `name`, `hasLogin`) VALUES 
('11', 'jb', '1'),
('12', 'paula', '2'),
('13', 'bea', '3');


INSERT INTO `artdb`.`photo` (`id`, `name`, `contact`) VALUES ('14', 'photojb1', '1');

INSERT INTO `artdb`.`photo` (`id`, `name`, `contact`) VALUES ('15', 'photopaula1', '2');
INSERT INTO `artdb`.`photo` (`id`, `name`, `contact`) VALUES ('16', 'photopaula1', '2');

INSERT INTO `artdb`.`photo` (`id`, `name`, `contact`) VALUES ('17', 'photobea1', '3');
INSERT INTO `artdb`.`photo` (`id`, `name`, `contact`) VALUES ('18', 'photobea2', '3');
INSERT INTO `artdb`.`photo` (`id`, `name`, `contact`) VALUES ('19', 'photobea3', '3');

INSERT INTO `artdb`.`hasAddress` (`id`, `address`, `contact`) VALUES ('20', '23', '1');
INSERT INTO `artdb`.`hasAddress` (`id`, `address`, `contact`) VALUES ('21', '24', '2');
INSERT INTO `artdb`.`hasAddress` (`id`, `address`, `contact`) VALUES ('22', '25', '3');
INSERT INTO `artdb`.`hasAddress` (`id`, `address`, `contact`,`company`) VALUES ('23', '26', '1',6);

INSERT INTO `artdb`.`address` (`id`, `name`,`company`) VALUES ('23', 'addressjb1',7);
INSERT INTO `artdb`.`address` (`id`, `name`) VALUES ('24', 'addresspaula');
INSERT INTO `artdb`.`address` (`id`, `name`) VALUES ('25', 'addressbea');
INSERT INTO `artdb`.`address` (`id`, `name`) VALUES ('26', 'addressjb2');
