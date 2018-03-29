--

CREATE TABLE `status_entries` (
  `status_id` int(10) NOT NULL,
  `issue_id` int(10) NOT NULL,
  `status_timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_user_id` int(10) NOT NULL,
  `status_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `status_type`
--

CREATE TABLE `status_type` (
  `status_type_id` int(11) NOT NULL,
  `status_type_text` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systems`
--

CREATE TABLE `systems` (
  `system_id` int(10) NOT NULL,
  `system_name` varchar(255) NOT NULL,
  `system_url` varchar(255) NOT NULL,
  `building` enum('Mary Idema Pew','Steelcase','Frey Foundation Learning Center','Curriculum Materials','Seidman House') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `system_users`
--

CREATE TABLE `system_users` (
  `system_id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `updates`
--

CREATE TABLE `updates` (
  `update_id` int(10) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(10) NOT NULL,
  `text` text NOT NULL,
  `public` bit(1) NOT NULL DEFAULT b'1',
  `system_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(10) NOT NULL,
  `user_username` varchar(255) NOT NULL,
  `user_fn` varchar(255) NOT NULL,
  `user_ln` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_delete` tinyint(1) NOT NULL,
  `user_notifications` tinyint(1) NOT NULL,
  `user_access` tinyint(4) NOT NULL COMMENT '0 = none, 1 = systems, 2= buildings, 9 = all'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `issue_entries`
--
ALTER TABLE `issue_entries`
  ADD PRIMARY KEY (`issue_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `issue_entries_ibfk_2` (`system_id`),
  ADD KEY `issue_entries_ibfk_3` (`status_type_id`);

--
-- Indexes for table `status_entries`
--
ALTER TABLE `status_entries`
  ADD PRIMARY KEY (`status_id`),
  ADD KEY `status_user_id` (`status_user_id`),
  ADD KEY `issue_id` (`issue_id`);

--
-- Indexes for table `status_type`
--
ALTER TABLE `status_type`
  ADD PRIMARY KEY (`status_type_id`);

--
-- Indexes for table `systems`
--
ALTER TABLE `systems`
  ADD PRIMARY KEY (`system_id`);

--
-- Indexes for table `system_users`
--
ALTER TABLE `system_users`
  ADD KEY `user_id` (`user_id`),
  ADD KEY `system_id` (`system_id`);

--
-- Indexes for table `updates`
--
ALTER TABLE `updates`
  ADD PRIMARY KEY (`update_id`),
  ADD KEY `system_id` (`system_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `issue_entries`
--
ALTER TABLE `issue_entries`
  MODIFY `issue_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=871;
--
-- AUTO_INCREMENT for table `status_entries`
--
ALTER TABLE `status_entries`
  MODIFY `status_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1397;
--
-- AUTO_INCREMENT for table `status_type`
--
ALTER TABLE `status_type`
  MODIFY `status_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `systems`
--
ALTER TABLE `systems`
  MODIFY `system_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
--
-- AUTO_INCREMENT for table `updates`
--
ALTER TABLE `updates`
  MODIFY `update_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `issue_entries`
--
ALTER TABLE `issue_entries`
  ADD CONSTRAINT `issue_entries_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `issue_entries_ibfk_2` FOREIGN KEY (`system_id`) REFERENCES `systems` (`system_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `issue_entries_ibfk_3` FOREIGN KEY (`status_type_id`) REFERENCES `status_type` (`status_type_id`) ON DELETE CASCADE;

--
-- Constraints for table `status_entries`
--
ALTER TABLE `status_entries`
  ADD CONSTRAINT `status_entries_ibfk_2` FOREIGN KEY (`status_user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `status_entries_ibfk_3` FOREIGN KEY (`issue_id`) REFERENCES `issue_entries` (`issue_id`) ON DELETE CASCADE;

--
-- Constraints for table `system_users`
--
ALTER TABLE `system_users`
  ADD CONSTRAINT `system_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `system_users_ibfk_2` FOREIGN KEY (`system_id`) REFERENCES `systems` (`system_id`);

--
-- Constraints for table `updates`
--
ALTER TABLE `updates`
  ADD CONSTRAINT `updates_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `updates_ibfk_2` FOREIGN KEY (`system_id`) REFERENCES `systems` (`system_id`) ON DELETE CASCADE;
