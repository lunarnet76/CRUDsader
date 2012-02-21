SET FOREIGN_KEY_CHECKS =0;


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