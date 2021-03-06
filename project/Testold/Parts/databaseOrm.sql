SET FOREIGN_KEY_CHECKS =0;

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- phpMyAdmin SQL Dump
-- version 3.3.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 09, 2011 at 01:09 AM
-- Server version: 5.1.49
-- PHP Version: 5.3.3-7+squeeze3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `CRUDsaderdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `address`
--

CREATE TABLE IF NOT EXISTS `address` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `Fstreet` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `FstreetNumber` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `Fcity` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `Fcountry` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `FKcontact` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKcontact` (`FKcontact`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- Dumping data for table `address`
--


-- --------------------------------------------------------

--
-- Table structure for table `C2Wb`
--

CREATE TABLE IF NOT EXISTS `C2Wb` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `FK2website` bigint(20) NOT NULL,
  `FK2contact` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK2website` (`FK2website`),
  KEY `FK2contact` (`FK2contact`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- Dumping data for table `C2Wb`
--


-- --------------------------------------------------------

--
-- Table structure for table `email`
--

CREATE TABLE IF NOT EXISTS `email` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `Faddress` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `Fwhen` DATE DEFAULT NULL,
  `FKcontact` bigint(20) DEFAULT NULL,
  `FKwebSite` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKcontact` (`FKcontact`),
  KEY `FKwebSite` (`FKwebSite`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- Dumping data for table `email`
--


-- --------------------------------------------------------

--
-- Table structure for table `group`
--

CREATE TABLE IF NOT EXISTS `group` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `Fname` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- Dumping data for table `group`
--


-- --------------------------------------------------------

--
-- Table structure for table `Tcontact`
--

CREATE TABLE IF NOT EXISTS `Tcontact` (
  `PKcontactid` bigint(20) NOT NULL AUTO_INCREMENT,
  `Fname` varchar(32) COLLATE utf8_bin NOT NULL,
  `FKgroup` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`PKcontactid`),
  UNIQUE KEY `unicity` (`Fname`),
  KEY `FKgroup` (`FKgroup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- Dumping data for table `Tcontact`
--


-- --------------------------------------------------------

--
-- Table structure for table `Tperson`
--

CREATE TABLE IF NOT EXISTS `Tperson` (
  `PKpersonid` bigint(20) NOT NULL AUTO_INCREMENT,
    `Ftitle` varchar(32) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`PKpersonid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- Dumping data for table `Tperson`
--


-- --------------------------------------------------------

--
-- Table structure for table `webSite`
--

CREATE TABLE IF NOT EXISTS `webSite` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `Furl` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- Dumping data for table `webSite`
--


--
-- Constraints for dumped tables
--

--
-- Constraints for table `address`
--
ALTER TABLE `address`
  ADD CONSTRAINT `address_ibfk_1` FOREIGN KEY (`FKcontact`) REFERENCES `Tcontact` (`PKcontactid`) ON DELETE CASCADE;
ALTER TABLE  `email` CHANGE  `Fwhen`  `Fwhen` DATE NULL DEFAULT NULL;
--
-- Constraints for table `C2Wb`
--
ALTER TABLE `C2Wb`
  ADD CONSTRAINT `C2Wb_ibfk_2` FOREIGN KEY (`FK2contact`) REFERENCES `Tcontact` (`PKcontactid`) ON DELETE CASCADE,
  ADD CONSTRAINT `C2Wb_ibfk_1` FOREIGN KEY (`FK2website`) REFERENCES `webSite` (`id`) ON DELETE CASCADE;

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

--
-- Dumping data for table `Tperson`
--



INSERT INTO `address` (`id`, `Fstreet`, `FstreetNumber`, `Fcity`, `Fcountry`, `FKcontact`) VALUES
(5, 'street1', '1', 'city1', 'country1', 1);

INSERT INTO `C2Wb` (`id`, `FK2webSite`, `FK2contact`) VALUES
(7, 6, 1);


INSERT INTO `email` (`id`, `Faddress`, `FKcontact`, `FKwebSite`) VALUES
(2, 'contact1@msn.com', 1, 6),
(7, 'contact1-2@msn.com', 1, NULL);


INSERT INTO `group` (`id`, `Fname`) VALUES
(3, 'group1');
INSERT INTO `group` (`id`, `Fname`) VALUES ('14', 'group2');


INSERT INTO `Tcontact` (`PKcontactid`, `Fname`, `FKgroup`) VALUES
(1, 'contact1', 14);

INSERT INTO `Tperson` (`PKpersonid`,`Ftitle`) VALUES ('1','Mrs');

INSERT INTO `Tcontact` (`PKcontactid`, `Fname`, `FKgroup`) VALUES ('13', 'bernardo', '3');
INSERT INTO `Tperson` (`PKpersonid`, `Ftitle`) VALUES ('13', 'mr.');

INSERT INTO `webSite` (`id`, `Furl`) VALUES (6, 'http://web0');
INSERT INTO `Tcontact` (`PKcontactid`, `Fname`, `FKgroup`) VALUES ('9', 'contact2', '3');
INSERT INTO `Tperson` (`PKpersonid` ,`Ftitle`)VALUES ('9','Mr');

INSERT INTO `email` (`id`, `Faddress`, `FKcontact`, `FKwebSite`) VALUES ('11', 'contact2@msn.com', '9', '6');
INSERT INTO `email` (`id`, `Faddress`, `FKcontact`, `FKwebSite`) VALUES ('12', 'contact1-3@msn.com', '1', '6');

UPDATE `email` SET  `Fwhen` =  '2011-09-23' WHERE  `email`.`id` =2;

INSERT INTO `webSite` (`id`, `Furl`) VALUES ('15', 'http://web2');
INSERT INTO `C2Wb` (`id`, `FK2webSite`, `FK2contact`) VALUES ('16', '15', '1');
INSERT INTO `webSite` (`id`, `Furl`) VALUES ('17', 'http://web1');
INSERT INTO `C2Wb` (`id`, `FK2webSite`, `FK2contact`) VALUES ('18', '17', '13');

SET FOREIGN_KEY_CHECKS =1;