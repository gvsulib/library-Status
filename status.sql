-- phpMyAdmin SQL Dump
-- version 3.5.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 29, 2013 at 08:49 PM
-- Server version: 5.5.29
-- PHP Version: 5.4.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `library_status`
--

-- --------------------------------------------------------

--
-- Table structure for table `issue_entries`
--

CREATE TABLE `issue_entries` (
  `issue_id` int(10) NOT NULL AUTO_INCREMENT,
  `system_id` int(10) NOT NULL,
  `status_type_id` int(10) NOT NULL,
  `start_time` int(13) NOT NULL,
  `end_time` int(13) NOT NULL,
  PRIMARY KEY (`issue_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=67 ;

--
-- Dumping data for table `issue_entries`
--

-- --------------------------------------------------------

--
-- Table structure for table `status_entries`
--

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=107 ;

--
-- Dumping data for table `status_entries`
--


-- --------------------------------------------------------

--
-- Table structure for table `status_type`
--

CREATE TABLE `status_type` (
  `status_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `status_type_text` varchar(255) NOT NULL,
  PRIMARY KEY (`status_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `status_type`
--

INSERT INTO `status_type` (`status_type_id`, `status_type_text`) VALUES
(1, 'Minor Issue'),
(2, 'Outage'),
(3, 'Resolution');

-- --------------------------------------------------------

--
-- Table structure for table `systems`
--

CREATE TABLE `systems` (
  `system_id` int(10) NOT NULL AUTO_INCREMENT,
  `system_name` varchar(255) NOT NULL,
  `system_url` varchar(255) NOT NULL,
  PRIMARY KEY (`system_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;


-- --------------------------------------------------------

--
-- Table structure for table `system_users`
--

CREATE TABLE `system_users` (
  `system_id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  KEY `user_id` (`user_id`),
  KEY `system_id` (`system_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(10) NOT NULL AUTO_INCREMENT,
  `user_username` varchar(255) NOT NULL,
  `user_fn` varchar(255) NOT NULL,
  `user_ln` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_delete` tinyint(1) NOT NULL,
  `user_notifications` tinyint(1) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `system_users`
--
ALTER TABLE `system_users`
  ADD CONSTRAINT `system_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `system_users_ibfk_2` FOREIGN KEY (`system_id`) REFERENCES `systems` (`system_id`);