# Table structure for table `course_module_locks`

CREATE TABLE `prefix_course_module_locks` (
	`id` int(10) unsigned NOT NULL auto_increment,
  	`courseid` int(10) unsigned NOT NULL default '0',
  	`moduleid` int(10) unsigned NOT NULL default '0',
  	`lockid` int(10) unsigned NOT NULL default '0',
  	`requirement` varchar(10) NOT NULL default '',
  	PRIMARY KEY  (id),
  	UNIQUE KEY id (id),
  	KEY lockid (lockid),
  	KEY moduleid (moduleid),
  	KEY courseid (courseid)
	) TYPE=MyISAM;
	

ALTER TABLE `prefix_course_modules` ADD `delay` VARCHAR( 10 ) NOT NULL AFTER `added`;
ALTER TABLE `prefix_course_modules` ADD `visiblewhenlocked` TINYINT( 1 ) UNSIGNED NOT NULL default '1' AFTER `visible`;
ALTER TABLE `prefix_course_modules` ADD `checkboxforcomplete` TINYINT( 1 ) UNSIGNED NOT NULL default '0' AFTER `visible`;
ALTER TABLE `prefix_course_modules` ADD `checkboxesforprereqs` TINYINT( 1 ) UNSIGNED NOT NULL default '1' AFTER `visible`;

# --------------------------------------------------------