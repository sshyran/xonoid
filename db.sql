SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `crm` DEFAULT CHARACTER SET utf8 COLLATE utf8_swedish_ci ;
USE `crm`;

-- -----------------------------------------------------
-- Table `crm`.`USERS`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `crm`.`USERS` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `companyid` INT UNSIGNED NOT NULL ,
  `firstname` TEXT NOT NULL ,
  `lastname` TEXT NOT NULL ,
  `email` TEXT NOT NULL ,
  `phone` TEXT NULL ,
  `password` VARCHAR(32) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX fk_USERS_companies (`companyid` ASC) ,
  CONSTRAINT `fk_USERS_companies`
    FOREIGN KEY (`companyid` )
    REFERENCES `crm`.`COMPANIES` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
CHECKSUM = 1;


-- -----------------------------------------------------
-- Table `crm`.`COMPANIES`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `crm`.`COMPANIES` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name` TEXT NOT NULL ,
  `resellerid` INT UNSIGNED NOT NULL ,
  `streetaddress` TEXT NOT NULL ,
  `postoffice` TEXT NOT NULL ,
  `postnumber` TEXT NOT NULL ,
  `contactid` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX fk_COMPANIES_COMPANIES (`resellerid` ASC) ,
  INDEX fk_COMPANIES_USERS (`contactid` ASC) ,
  CONSTRAINT `fk_COMPANIES_COMPANIES`
    FOREIGN KEY (`resellerid` )
    REFERENCES `crm`.`COMPANIES` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_COMPANIES_USERS`
    FOREIGN KEY (`contactid` )
    REFERENCES `crm`.`USERS` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
CHECKSUM = 1;


-- -----------------------------------------------------
-- Table `crm`.`BRANCHES`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `crm`.`BRANCHES` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` TEXT NOT NULL ,
  `usize` INT NOT NULL DEFAULT 48 ,
  `companyid` INT UNSIGNED NOT NULL ,
  `contactid` INT NOT NULL ,
  `streetaddress` TEXT NOT NULL ,
  `postoffice` TEXT NOT NULL ,
  `postnumber` TEXT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX fk_RACKS_COMPANIES (`companyid` ASC) ,
  INDEX fk_BRANCH_USERS (`contactid` ASC) ,
  CONSTRAINT `fk_RACKS_COMPANIES`
    FOREIGN KEY (`companyid` )
    REFERENCES `crm`.`COMPANIES` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_BRANCH_USERS`
    FOREIGN KEY (`contactid` )
    REFERENCES `crm`.`USERS` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
CHECKSUM = 1;


-- -----------------------------------------------------
-- Table `crm`.`PORT_TYPES`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `crm`.`PORT_TYPES` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` TEXT NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
CHECKSUM = 1;


-- -----------------------------------------------------
-- Table `crm`.`NETWORK_UNITS`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `crm`.`NETWORK_UNITS` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `branchid` INT NOT NULL ,
  `companyid` INT UNSIGNED NOT NULL ,
  `name` TEXT NOT NULL ,
  `usize` INT NOT NULL DEFAULT 4 ,
  `contactid` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX fk_RACK_SERVERS_COMPANIES (`companyid` ASC) ,
  INDEX fk_RACK_SERVERS_RACKS (`branchid` ASC) ,
  INDEX fk_NETWORK_UNITS_USERS (`contactid` ASC) ,
  CONSTRAINT `fk_RACK_SERVERS_COMPANIES`
    FOREIGN KEY (`companyid` )
    REFERENCES `crm`.`COMPANIES` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_RACK_SERVERS_RACKS`
    FOREIGN KEY (`branchid` )
    REFERENCES `crm`.`BRANCHES` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_NETWORK_UNITS_USERS`
    FOREIGN KEY (`contactid` )
    REFERENCES `crm`.`USERS` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
CHECKSUM = 1;


-- -----------------------------------------------------
-- Table `crm`.`NETWORK_UNIT_PORTS`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `crm`.`NETWORK_UNIT_PORTS` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `networkunitid` INT NOT NULL ,
  `side` ENUM('F','B') NOT NULL ,
  `porttypeid` INT NOT NULL ,
  `name` TEXT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX fk_RACK_SERVER_PORTS_RACK_SERVERS (`networkunitid` ASC) ,
  INDEX fk_RACK_SERVER_PORTS_RACK_PORT_TYPES (`porttypeid` ASC) ,
  CONSTRAINT `fk_RACK_SERVER_PORTS_RACK_SERVERS`
    FOREIGN KEY (`networkunitid` )
    REFERENCES `crm`.`NETWORK_UNITS` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_RACK_SERVER_PORTS_RACK_PORT_TYPES`
    FOREIGN KEY (`porttypeid` )
    REFERENCES `crm`.`PORT_TYPES` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
CHECKSUM = 1;


-- -----------------------------------------------------
-- Table `crm`.`NETWORK_SERVER_PORT_CONNECTIONS`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `crm`.`NETWORK_SERVER_PORT_CONNECTIONS` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `connected_from` INT NOT NULL ,
  `connected_to` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX fk_RACK_SERVER_PORT_CONNECTIONS_RACK_UNIT_PORTS (`connected_from` ASC) ,
  INDEX fk_RACK_SERVER_PORT_CONNECTIONS_RACK_UNIT_PORTS1 (`connected_to` ASC) ,
  CONSTRAINT `fk_RACK_SERVER_PORT_CONNECTIONS_RACK_UNIT_PORTS`
    FOREIGN KEY (`connected_from` )
    REFERENCES `crm`.`NETWORK_UNIT_PORTS` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_RACK_SERVER_PORT_CONNECTIONS_RACK_UNIT_PORTS1`
    FOREIGN KEY (`connected_to` )
    REFERENCES `crm`.`NETWORK_UNIT_PORTS` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
CHECKSUM = 1;


-- -----------------------------------------------------
-- Table `crm`.`COMPANY_NETWORKS`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `crm`.`COMPANY_NETWORKS` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `ipaddress` BIGINT NOT NULL ,
  `cidr` INT NOT NULL ,
  `companyid` INT UNSIGNED NULL ,
  PRIMARY KEY (`id`) ,
  INDEX fk_COMPANY_NETWORKS_COMPANIES (`companyid` ASC) ,
  CONSTRAINT `fk_COMPANY_NETWORKS_COMPANIES`
    FOREIGN KEY (`companyid` )
    REFERENCES `crm`.`COMPANIES` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
CHECKSUM = 1;


-- -----------------------------------------------------
-- Table `crm`.`VLANS`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `crm`.`VLANS` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `vlanid` INT NOT NULL ,
  `name` TEXT NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
CHECKSUM = 1;


-- -----------------------------------------------------
-- Table `crm`.`IP_ADDRESSES`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `crm`.`IP_ADDRESSES` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `ipaddress` BIGINT NOT NULL ,
  `cidr` INT NOT NULL ,
  `name` TEXT NOT NULL ,
  `vlanrefid` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX fk_IP_ADDRESSES_VLANS (`vlanrefid` ASC) ,
  CONSTRAINT `fk_IP_ADDRESSES_VLANS`
    FOREIGN KEY (`vlanrefid` )
    REFERENCES `crm`.`VLANS` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
CHECKSUM = 1;


-- -----------------------------------------------------
-- Table `crm`.`PORT_IP_ADDRESSES`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `crm`.`PORT_IP_ADDRESSES` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `portid` INT NOT NULL ,
  `ipaddressrefid` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX fk_IP_ADDRESSES_NETWORK_UNIT_PORTS (`portid` ASC) ,
  INDEX fk_PORT_IP_ADDRESSES_IP_ADDRESSES (`ipaddressrefid` ASC) ,
  CONSTRAINT `fk_IP_ADDRESSES_NETWORK_UNIT_PORTS`
    FOREIGN KEY (`portid` )
    REFERENCES `crm`.`NETWORK_UNIT_PORTS` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_PORT_IP_ADDRESSES_IP_ADDRESSES`
    FOREIGN KEY (`ipaddressrefid` )
    REFERENCES `crm`.`IP_ADDRESSES` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
CHECKSUM = 1;


-- -----------------------------------------------------
-- Table `crm`.`PORT_VLANS`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `crm`.`PORT_VLANS` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `portid` INT NOT NULL ,
  `vlanrefid` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX fk_VLANS_NETWORK_UNIT_PORTS (`portid` ASC) ,
  INDEX fk_PORT_VLANS_VLANS (`vlanrefid` ASC) ,
  CONSTRAINT `fk_VLANS_NETWORK_UNIT_PORTS`
    FOREIGN KEY (`portid` )
    REFERENCES `crm`.`NETWORK_UNIT_PORTS` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_PORT_VLANS_VLANS`
    FOREIGN KEY (`vlanrefid` )
    REFERENCES `crm`.`VLANS` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
CHECKSUM = 1;


-- -----------------------------------------------------
-- Table `crm`.`TICKET_STATUSES`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `crm`.`TICKET_STATUSES` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` TEXT NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
CHECKSUM = 1;


-- -----------------------------------------------------
-- Table `crm`.`TICKETS`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `crm`.`TICKETS` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `subject` TEXT NOT NULL ,
  `added` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
CHECKSUM = 1;


-- -----------------------------------------------------
-- Table `crm`.`TICKET_REPLY`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `crm`.`TICKET_REPLY` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `ticketid` BIGINT NOT NULL ,
  `descr` LONGTEXT NOT NULL ,
  `userid` INT NOT NULL ,
  `statusid` INT NOT NULL ,
  `added` DATETIME NOT NULL DEFAULT NOW() ,
  PRIMARY KEY (`id`) ,
  INDEX fk_TICKET_REPLY_TICKETS (`ticketid` ASC) ,
  INDEX fk_TICKET_REPLY_USERS (`userid` ASC) ,
  INDEX fk_TICKET_REPLY_TICKET_STATUSES (`statusid` ASC) ,
  CONSTRAINT `fk_TICKET_REPLY_TICKETS`
    FOREIGN KEY (`ticketid` )
    REFERENCES `crm`.`TICKETS` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_TICKET_REPLY_USERS`
    FOREIGN KEY (`userid` )
    REFERENCES `crm`.`USERS` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_TICKET_REPLY_TICKET_STATUSES`
    FOREIGN KEY (`statusid` )
    REFERENCES `crm`.`TICKET_STATUSES` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
CHECKSUM = 1;


-- -----------------------------------------------------
-- Table `crm`.`REPLY_ATTACHMENTS`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `crm`.`REPLY_ATTACHMENTS` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `replyid` INT NOT NULL ,
  `mimetype` TEXT NOT NULL ,
  `filedata` LONGBLOB NOT NULL ,
  `md5sum` VARCHAR(45) NOT NULL ,
  `filesize` INT NOT NULL ,
  `filename` TEXT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX fk_TICKET_ATTACHMENTS_TICKET_REPLY (`replyid` ASC) ,
  CONSTRAINT `fk_TICKET_ATTACHMENTS_TICKET_REPLY`
    FOREIGN KEY (`replyid` )
    REFERENCES `crm`.`TICKET_REPLY` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
CHECKSUM = 1;


-- -----------------------------------------------------
-- Table `crm`.`CALENDAR`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `crm`.`CALENDAR` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `starttime` DATETIME NOT NULL ,
  `endtime` DATETIME NOT NULL ,
  `name` TEXT NOT NULL ,
  `descr` LONGTEXT NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
CHECKSUM = 1;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
