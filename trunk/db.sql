SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `BRANCHES` (
  `id` int(11) NOT NULL auto_increment,
  `name` text collate utf8_swedish_ci NOT NULL,
  `companyid` int(10) unsigned NOT NULL,
  `contactid` int(11) NOT NULL,
  `streetaddress` text collate utf8_swedish_ci NOT NULL,
  `postoffice` text collate utf8_swedish_ci NOT NULL,
  `postnumber` text collate utf8_swedish_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `fk_RACKS_COMPANIES` (`companyid`),
  KEY `fk_BRANCH_USERS` (`contactid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci CHECKSUM=1;

CREATE TABLE IF NOT EXISTS `COMPANIES` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` text collate utf8_swedish_ci NOT NULL,
  `resellerid` int(10) unsigned default NULL,
  `streetaddress` text collate utf8_swedish_ci NOT NULL,
  `postoffice` text collate utf8_swedish_ci NOT NULL,
  `postnumber` text collate utf8_swedish_ci NOT NULL,
  `contactid` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `fk_COMPANIES_COMPANIES` (`resellerid`),
  KEY `fk_COMPANIES_USERS` (`contactid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci CHECKSUM=1;

CREATE TABLE IF NOT EXISTS `COMPANY_NETWORKS` (
  `id` int(11) NOT NULL auto_increment,
  `ipaddress` bigint(20) NOT NULL,
  `cidr` int(11) NOT NULL,
  `companyid` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `fk_COMPANY_NETWORKS_COMPANIES` (`companyid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci CHECKSUM=1;

CREATE TABLE IF NOT EXISTS `NETWORK_SERVER_PORT_CONNECTIONS` (
  `id` int(11) NOT NULL auto_increment,
  `connected_from` int(11) NOT NULL,
  `connected_to` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `fk_RACK_SERVER_PORT_CONNECTIONS_RACK_UNIT_PORTS` (`connected_from`),
  KEY `fk_RACK_SERVER_PORT_CONNECTIONS_RACK_UNIT_PORTS1` (`connected_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci CHECKSUM=1;

CREATE TABLE IF NOT EXISTS `NETWORK_UNITS` (
  `id` int(11) NOT NULL auto_increment,
  `branchid` int(11) NOT NULL,
  `companyid` int(10) unsigned NOT NULL,
  `name` text collate utf8_swedish_ci NOT NULL,
  `usize` int(11) NOT NULL default '4',
  `contactid` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `fk_RACK_SERVERS_COMPANIES` (`companyid`),
  KEY `fk_RACK_SERVERS_RACKS` (`branchid`),
  KEY `fk_NETWORK_UNITS_USERS` (`contactid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci CHECKSUM=1;


CREATE TABLE IF NOT EXISTS `NETWORK_UNIT_PORTS` (
  `id` int(11) NOT NULL auto_increment,
  `networkunitid` int(11) NOT NULL,
  `side` enum('F','B') collate utf8_swedish_ci NOT NULL,
  `porttypeid` int(11) NOT NULL,
  `name` text collate utf8_swedish_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `fk_RACK_SERVER_PORTS_RACK_SERVERS` (`networkunitid`),
  KEY `fk_RACK_SERVER_PORTS_RACK_PORT_TYPES` (`porttypeid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci CHECKSUM=1;


CREATE TABLE IF NOT EXISTS `PORT_IP_ADDRESSES` (
  `id` int(11) NOT NULL auto_increment,
  `portid` int(11) NOT NULL,
  `ipaddr` bigint(11) unsigned NOT NULL,
  `cidr` int(11) unsigned NOT NULL default '28',
  PRIMARY KEY  (`id`),
  KEY `fk_IP_ADDRESSES_NETWORK_UNIT_PORTS` (`portid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci CHECKSUM=1;


CREATE TABLE IF NOT EXISTS `PORT_TYPES` (
  `id` int(11) NOT NULL auto_increment,
  `name` text collate utf8_swedish_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci CHECKSUM=1;


CREATE TABLE IF NOT EXISTS `PORT_VLANS` (
  `id` int(11) NOT NULL auto_increment,
  `portid` int(11) NOT NULL,
  `vlanid` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `fk_VLANS_NETWORK_UNIT_PORTS` (`portid`),
  KEY `fk_PORT_VLANS_VLANS` (`vlanid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci CHECKSUM=1;


CREATE TABLE IF NOT EXISTS `REPLY_ATTACHMENTS` (
  `id` int(11) NOT NULL auto_increment,
  `replyid` int(11) NOT NULL,
  `mimetype` text collate utf8_swedish_ci NOT NULL,
  `filedata` longblob NOT NULL,
  `md5sum` varchar(45) collate utf8_swedish_ci NOT NULL,
  `filesize` int(11) NOT NULL,
  `filename` text collate utf8_swedish_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `fk_TICKET_ATTACHMENTS_TICKET_REPLY` (`replyid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci CHECKSUM=1;


CREATE TABLE IF NOT EXISTS `TICKETS` (
  `id` bigint(20) NOT NULL auto_increment,
  `subject` text collate utf8_swedish_ci NOT NULL,
  `added` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci CHECKSUM=1;

CREATE TABLE IF NOT EXISTS `TICKET_REPLY` (
  `id` int(11) NOT NULL auto_increment,
  `ticketid` bigint(20) NOT NULL,
  `descr` longtext collate utf8_swedish_ci NOT NULL,
  `userid` int(11) NOT NULL,
  `statusid` int(11) NOT NULL,
  `added` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `fk_TICKET_REPLY_TICKETS` (`ticketid`),
  KEY `fk_TICKET_REPLY_USERS` (`userid`),
  KEY `fk_TICKET_REPLY_TICKET_STATUSES` (`statusid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci CHECKSUM=1;


CREATE TABLE IF NOT EXISTS `TICKET_STATUSES` (
  `id` int(11) NOT NULL auto_increment,
  `name` text collate utf8_swedish_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci CHECKSUM=1;


CREATE TABLE IF NOT EXISTS `USERS` (
  `id` int(11) NOT NULL auto_increment,
  `companyid` int(10) unsigned default NULL,
  `firstname` text collate utf8_swedish_ci NOT NULL,
  `lastname` text collate utf8_swedish_ci NOT NULL,
  `email` text collate utf8_swedish_ci NOT NULL,
  `phone` text collate utf8_swedish_ci,
  `password` varchar(32) collate utf8_swedish_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `fk_USERS_companies` (`companyid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci CHECKSUM=1;


CREATE VIEW `crm`.`view_nup_pt` AS select `nup`.`id` AS `id`,`nup`.`networkunitid` AS `networkunitid`,`nup`.`name` AS `name`,`nup`.`side` AS `side`,`pt`.`name` AS `porttypename`,`nup`.`porttypeid` AS `porttypeid` from (`crm`.`NETWORK_UNIT_PORTS` `nup` join `crm`.`PORT_TYPES` `pt`) where (`pt`.`id` = `nup`.`porttypeid`);

CREATE VIEW `crm`.`view_p_ip` AS select `crm`.`PORT_IP_ADDRESSES`.`id` AS `id`,`crm`.`PORT_IP_ADDRESSES`.`portid` AS `portid`,inet_ntoa(`crm`.`PORT_IP_ADDRESSES`.`ipaddr`) AS `ipaddress`,`crm`.`PORT_IP_ADDRESSES`.`cidr` AS `cidr`,inet_ntoa(((0xffffffff << (32 - `crm`.`PORT_IP_ADDRESSES`.`cidr`)) & 0xffffffff)) AS `mask`,inet_ntoa((`crm`.`PORT_IP_ADDRESSES`.`ipaddr` & ((0xffffffff << (32 - `crm`.`PORT_IP_ADDRESSES`.`cidr`)) & 0xffffffff))) AS `network`,inet_ntoa(((`crm`.`PORT_IP_ADDRESSES`.`ipaddr` & ((0xffffffff << (32 - `crm`.`PORT_IP_ADDRESSES`.`cidr`)) & 0xffffffff)) | (~((0xffffffff << (32 - `crm`.`PORT_IP_ADDRESSES`.`cidr`))) & 0xffffffff))) AS `broadcast`,(nullif(((((`crm`.`PORT_IP_ADDRESSES`.`ipaddr` & (0xffffffff << (32 - `crm`.`PORT_IP_ADDRESSES`.`cidr`))) & 0xffffffff) | (~((0xffffffff << (32 - `crm`.`PORT_IP_ADDRESSES`.`cidr`))) & 0xffffffff)) - ((`crm`.`PORT_IP_ADDRESSES`.`ipaddr` & (0xffffffff << (32 - `crm`.`PORT_IP_ADDRESSES`.`cidr`))) & 0xffffffff)),0) - 1) AS `ipcount`,`crm`.`PORT_IP_ADDRESSES`.`ipaddr` AS `ipaddressint` from `crm`.`PORT_IP_ADDRESSES`;

ALTER TABLE `BRANCHES`
  ADD CONSTRAINT `BRANCHES_ibfk_1` FOREIGN KEY (`companyid`) REFERENCES `COMPANIES` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_BRANCH_USERS` FOREIGN KEY (`contactid`) REFERENCES `USERS` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `COMPANIES`
  ADD CONSTRAINT `COMPANIES_ibfk_1` FOREIGN KEY (`resellerid`) REFERENCES `COMPANIES` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_COMPANIES_USERS` FOREIGN KEY (`contactid`) REFERENCES `USERS` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `COMPANY_NETWORKS`
  ADD CONSTRAINT `fk_COMPANY_NETWORKS_COMPANIES` FOREIGN KEY (`companyid`) REFERENCES `COMPANIES` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `NETWORK_SERVER_PORT_CONNECTIONS`
  ADD CONSTRAINT `fk_RACK_SERVER_PORT_CONNECTIONS_RACK_UNIT_PORTS` FOREIGN KEY (`connected_from`) REFERENCES `NETWORK_UNIT_PORTS` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_RACK_SERVER_PORT_CONNECTIONS_RACK_UNIT_PORTS1` FOREIGN KEY (`connected_to`) REFERENCES `NETWORK_UNIT_PORTS` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `NETWORK_UNITS`
  ADD CONSTRAINT `NETWORK_UNITS_ibfk_1` FOREIGN KEY (`branchid`) REFERENCES `BRANCHES` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `NETWORK_UNITS_ibfk_2` FOREIGN KEY (`companyid`) REFERENCES `COMPANIES` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `NETWORK_UNITS_ibfk_3` FOREIGN KEY (`contactid`) REFERENCES `USERS` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `NETWORK_UNIT_PORTS`
  ADD CONSTRAINT `NETWORK_UNIT_PORTS_ibfk_1` FOREIGN KEY (`networkunitid`) REFERENCES `NETWORK_UNITS` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `NETWORK_UNIT_PORTS_ibfk_2` FOREIGN KEY (`porttypeid`) REFERENCES `PORT_TYPES` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `PORT_IP_ADDRESSES`
  ADD CONSTRAINT `fk_IP_ADDRESSES_NETWORK_UNIT_PORTS` FOREIGN KEY (`portid`) REFERENCES `NETWORK_UNIT_PORTS` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `PORT_VLANS`
  ADD CONSTRAINT `fk_VLANS_NETWORK_UNIT_PORTS` FOREIGN KEY (`portid`) REFERENCES `NETWORK_UNIT_PORTS` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `REPLY_ATTACHMENTS`
  ADD CONSTRAINT `fk_TICKET_ATTACHMENTS_TICKET_REPLY` FOREIGN KEY (`replyid`) REFERENCES `TICKET_REPLY` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `TICKET_REPLY`
  ADD CONSTRAINT `fk_TICKET_REPLY_TICKETS` FOREIGN KEY (`ticketid`) REFERENCES `TICKETS` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_TICKET_REPLY_TICKET_STATUSES` FOREIGN KEY (`statusid`) REFERENCES `TICKET_STATUSES` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_TICKET_REPLY_USERS` FOREIGN KEY (`userid`) REFERENCES `USERS` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `USERS`
  ADD CONSTRAINT `USERS_ibfk_1` FOREIGN KEY (`companyid`) REFERENCES `COMPANIES` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
