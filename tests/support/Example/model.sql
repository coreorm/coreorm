# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.6.19)
# Database: model_test
# Generation Time: 2014-08-16 01:28:25 +0000
# ************************************************************

# Dump of table attachment
# ------------------------------------------------------------

DROP TABLE IF EXISTS `attachment`;

CREATE TABLE `attachment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `filename` varchar(100) DEFAULT NULL,
  `size` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `attachment` WRITE;
/*!40000 ALTER TABLE `attachment` DISABLE KEYS */;

INSERT INTO `attachment` (`id`, `user_id`, `filename`, `size`)
VALUES
  (1,1,'test.jpg',23),
  (2,1,'abc.pdf',34.21),
  (3,2,'low.mov',3020.32),
  (4,3,'page.txt',302.12),
  (5,2,'flow.diagram',23.11);

/*!40000 ALTER TABLE `attachment` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table login
# ------------------------------------------------------------

DROP TABLE IF EXISTS `login`;

CREATE TABLE `login` (
  `user_id` int(11) unsigned NOT NULL,
  `username` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `login` WRITE;
/*!40000 ALTER TABLE `login` DISABLE KEYS */;

INSERT INTO `login` (`user_id`, `username`, `password`)
VALUES
  (1,'jayf','asfsafadf'),
  (2,'brucel','ljalfasdf');

/*!40000 ALTER TABLE `login` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;

INSERT INTO `user` (`id`, `name`, `address`, `birthdate`)
VALUES
  (1,'Jay Faye','80 Illust Rd. Sydney','1981-03-21'),
  (2,'Bruce L','300 Pitt, Sydney','1977-02-21'),
  (3,'Fry Steve','1 Infinite Loop, Redmond','1972-11-23');

/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

# Dump of table combined_key_table
# ------------------------------------------------------------
DROP TABLE IF EXISTS `combined_key_table`;

CREATE TABLE `combined_key_table` (
  `id_1` int(11) unsigned NOT NULL,
  `id_2` int(11) NOT NULL DEFAULT '0',
  `name` varchar(200) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_1`,`id_2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

