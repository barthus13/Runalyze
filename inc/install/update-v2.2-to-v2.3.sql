/* 18.10.2015 - drop 'types' flag for sport */
ALTER TABLE `runalyze_sport` DROP `types`;

/* 08.11.2015 -  add tables for tags */
CREATE TABLE IF NOT EXISTS `runalyze_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(50) NOT NULL,
  `accountid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY  `tagAccount` (`accountid`,`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `runalyze_activity_tag` (
  `activityid` int(11) NOT NULL,
  `tagid` int(11) NOT NULL,
  PRIMARY KEY `tagActivity` (`activityid`,`tagid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
