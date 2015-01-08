SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE TABLE `issue_entries` (
  `issue_id` int(10) NOT NULL AUTO_INCREMENT,
  `system_id` int(10) NOT NULL,
  `status_type_id` int(10) NOT NULL,
  `start_time` int(13) NOT NULL,
  `end_time` int(13) NOT NULL,
  PRIMARY KEY (`issue_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2;
INSERT INTO `issue_entries` (`issue_id`, `system_id`, `status_type_id`, `start_time`, `end_time`) VALUES
(1, 1, 1, 1379443981, 0);
CREATE TABLE `status_entries` (
  `status_id` int(10) NOT NULL AUTO_INCREMENT,
  `issue_id` int(10) NOT NULL,
  `status_timestamp` int(13) NOT NULL,
  `status_public` tinyint(1) NOT NULL,
  `status_type_id` int(10) NOT NULL,
  `status_user_id` int(10) NOT NULL,
  `status_text` text NOT NULL,
  `status_delete` tinyint(1) NOT NULL,
  PRIMARY KEY (`status_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2;
INSERT INTO `status_entries` (`status_id`, `issue_id`, `status_timestamp`, `status_public`, `status_type_id`, `status_user_id`, `status_text`, `status_delete`) VALUES
(1, 103, 1379443981, 1, 1, 1, 'Welcome to Library Status. We are [markdown](http://daringfireball.net/projects/markdown/basics) friendly.', 0);
CREATE TABLE `status_type` (
  `status_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `status_type_text` varchar(255) NOT NULL,
  PRIMARY KEY (`status_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4;
INSERT INTO `status_type` (`status_type_id`, `status_type_text`) VALUES
(1, 'Minor Issue'),
(2, 'Outage'),
(3, 'Resolution');
CREATE TABLE `systems` (
  `system_id` int(10) NOT NULL AUTO_INCREMENT,
  `system_name` varchar(255) NOT NULL,
  `system_url` varchar(255) NOT NULL,
  PRIMARY KEY (`system_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6;
CREATE TABLE `system_users` (
  `system_id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  KEY `user_id` (`user_id`),
  KEY `system_id` (`system_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `user` (
  `user_id` int(10) NOT NULL AUTO_INCREMENT,
  `user_username` varchar(255) NOT NULL,
  `password` varchar(40) NULL,
  `user_fn` varchar(255) NOT NULL,
  `user_ln` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_delete` tinyint(1) NOT NULL,
  `user_notifications` tinyint(1) NOT NULL,
  `user_access` tinyint(4) NOT NULL COMMENT '0 = none, 1 = systems, 2= buildings, 9 = all',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
