-- phpMyAdmin SQL Dump
-- version 3.3.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 06, 2011 at 07:47 AM
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
  `street` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `streetNumber` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `city` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `country` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `FKcontact` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKcontact` (`FKcontact`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=6 ;

--
-- Dumping data for table `address`
--

INSERT INTO `address` (`id`, `street`, `streetNumber`, `city`, `country`, `FKcontact`) VALUES
(5, 'street1', '1', 'city1', 'country1', 1);

-- --------------------------------------------------------

--
-- Table structure for table `C2Wb`
--

CREATE TABLE IF NOT EXISTS `C2Wb` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `webSite` bigint(20) NOT NULL,
  `contact` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `webSite` (`webSite`),
  KEY `contact` (`contact`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=8 ;

--
-- Dumping data for table `C2Wb`
--

INSERT INTO `C2Wb` (`id`, `webSite`, `contact`) VALUES
(7, 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `email`
--

CREATE TABLE IF NOT EXISTS `email` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `address` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `FKcontact` bigint(20) DEFAULT NULL,
  `FKwebSite` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKcontact` (`FKcontact`),
  KEY `FKwebSite` (`FKwebSite`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=8 ;

--
-- Dumping data for table `email`
--

INSERT INTO `email` (`id`, `address`, `FKcontact`, `FKwebSite`) VALUES
(2, 'contact1@msn.com', 1, 6),
(7, 'contact1-2@msn.com', 1, 6);

-- --------------------------------------------------------

--
-- Table structure for table `group`
--

CREATE TABLE IF NOT EXISTS `group` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=4 ;

--
-- Dumping data for table `group`
--

INSERT INTO `group` (`id`, `name`) VALUES
(3, 'group1');

-- --------------------------------------------------------

--
-- Table structure for table `Tcontact`
--

CREATE TABLE IF NOT EXISTS `Tcontact` (
  `PKcontactid` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) COLLATE utf8_bin NOT NULL,
  `FKgroup` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`PKcontactid`),
  UNIQUE KEY `unicity` (`name`),
  KEY `FKgroup` (`FKgroup`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=2 ;

--
-- Dumping data for table `Tcontact`
--

INSERT INTO `Tcontact` (`PKcontactid`, `name`, `FKgroup`) VALUES
(1, 'contact1', 3);

-- --------------------------------------------------------

--
-- Table structure for table `Tperson`
--

CREATE TABLE IF NOT EXISTS `Tperson` (
  `PKpersonid` bigint(20) NOT NULL AUTO_INCREMENT,
  `polymorphism` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`PKpersonid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

INSERT INTO `artdb`.`Tperson` (`PKpersonid`, `polymorphism`) VALUES ('1', NULL);
--
-- Dumping data for table `Tperson`
--


-- --------------------------------------------------------

--
-- Table structure for table `webSite`
--

CREATE TABLE IF NOT EXISTS `webSite` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `url` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=7 ;

--
-- Dumping data for table `webSite`
--

INSERT INTO `webSite` (`id`, `url`) VALUES
(6, 'http://website1');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `address`
--
ALTER TABLE `address`
  ADD CONSTRAINT `address_ibfk_1` FOREIGN KEY (`FKcontact`) REFERENCES `Tcontact` (`PKcontactid`) ON DELETE CASCADE;

--
-- Constraints for table `C2Wb`
--
ALTER TABLE `C2Wb`
  ADD CONSTRAINT `C2Wb_ibfk_2` FOREIGN KEY (`contact`) REFERENCES `Tcontact` (`PKcontactid`) ON DELETE CASCADE,
  ADD CONSTRAINT `C2Wb_ibfk_1` FOREIGN KEY (`webSite`) REFERENCES `webSite` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email`
--
ALTER TABLE `email`
  ADD CONSTRAINT `email_ibfk_2` FOREIGN KEY (`FKwebSite`) REFERENCES `webSite` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `email_ibfk_1` FOREIGN KEY (`FKcontact`) REFERENCES `Tcontact` (`PKcontactid`) ON DELETE CASCADE;

--
-- Constraints for table `Tcontact`
--
ALTER TABLE `Tcontact`
  ADD CONSTRAINT `Tcontact_ibfk_1` FOREIGN KEY (`FKgroup`) REFERENCES `group` (`id`) ON DELETE CASCADE;
INSERT INTO `artdb`.`Tcontact` (`PKcontactid`, `name`, `FKgroup`) VALUES ('9', 'contact2', '3');

INSERT INTO `artdb`.`Tperson` (
`PKpersonid` ,
`polymorphism`
)
VALUES (
'2', NULL
);