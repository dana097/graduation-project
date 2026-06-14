-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 06, 2026 at 03:34 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `academic_organizer`
--

-- --------------------------------------------------------

--
-- Table structure for table `college`
--

CREATE TABLE `college` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `college`
--

INSERT INTO `college` (`id`, `name`) VALUES
(1, 'College of Engineering'),
(2, 'College of Medicine'),
(3, 'College of Dentistry'),
(4, 'College of Pharmacy'),
(5, 'College of Nursing'),
(6, 'College of Applied Medical Sciences'),
(7, 'College of Science'),
(8, 'College of Computer and Information Technology'),
(9, 'College of Business Administration'),
(10, 'College of Economics and Administrative Sciences'),
(11, 'College of Education'),
(12, 'College of Arts and Humanities'),
(13, 'College of Law'),
(14, 'College of Architecture and Planning'),
(15, 'College of Designs and Arts'),
(16, 'College of Agriculture'),
(17, 'College of Environmental Sciences'),
(18, 'College of Social Sciences'),
(19, 'College of Languages and Translation'),
(20, 'College of Tourism and Hospitality'),
(21, 'College of Public Health'),
(22, 'College of Physical Education'),
(23, 'College of Fine Arts'),
(24, 'College of Islamic Studies'),
(25, 'College of Sharia and Islamic Studies'),
(26, 'College of Community Services'),
(27, 'College of Computing and Informatics'),
(28, 'College of Biotechnology'),
(29, 'College of Marine Sciences'),
(30, 'College of Aviation');

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `id` int(11) NOT NULL,
  `course` varchar(255) DEFAULT NULL,
  `year` varchar(255) NOT NULL,
  `semester` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `teacher_name` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `college_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`id`, `course`, `year`, `semester`, `name`, `teacher_name`, `user_id`, `college_id`) VALUES
(32, '28361', '2023', '1', 'PM', 'ali', 29, 8),
(34, '74258', '2024', '1', 'DS', 'Abdallah', 29, 27),
(57, '03748', '2025', '1', 'مشروع تخرج 2', 'Ahmed', 29, 27),
(60, '13653', '2025', '1', 'OOP', 'Omar', 30, 27),
(61, '15853', '2025', '1', 'ERP', 'Omar', 30, 27),
(62, '15853', '2025', '1', 'ERP', 'Omar', 47, 27),
(63, '13653', '2025', '1', 'OOP', 'Omar', 47, 27),
(64, '13653', '2025', '1', 'OOP', 'Omar', 29, 27);

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `id` int(11) NOT NULL,
  `recipient` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `timestamp` datetime NOT NULL,
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`id`, `recipient`, `message`, `timestamp`, `status`) VALUES
(13, 29, 'ghala shared a schedule with you', '2025-12-12 18:59:39', 'read'),
(29, 38, 'dana sent you a guardian request', '2025-12-19 15:17:16', 'read'),
(37, 38, 'dalia sent you a guardian request', '2025-12-19 15:49:56', 'read'),
(40, 38, 'dama sent you a guardian request', '2025-12-19 17:28:04', 'read'),
(53, 38, 'dina sent you a guardian request', '2025-12-20 13:58:24', 'read'),
(55, 38, 'dana sent you a guardian request', '2025-12-20 14:24:18', 'read'),
(58, 38, 'dana sent you a guardian request', '2025-12-23 12:52:15', 'read');

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `schedule_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `start_time` varchar(100) NOT NULL,
  `end_time` varchar(100) NOT NULL,
  `day` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`schedule_id`, `course_id`, `start_time`, `end_time`, `day`, `user_id`) VALUES
(80, 57, '18:00', '19:00', 'Sunday', 29),
(83, 60, '08:00', '10:00', 'Sunday', 30),
(84, 61, '08:00', '10:00', 'Monday', 30),
(86, 62, '08:00', '10:00', 'Monday', 47),
(87, 63, '08:00', '10:00', 'Sunday', 47),
(88, 64, '08:00', '10:00', 'Sunday', 29);

-- --------------------------------------------------------

--
-- Table structure for table `scheduleuser`
--

CREATE TABLE `scheduleuser` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `shared` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_guardian`
--

CREATE TABLE `student_guardian` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `guardian_id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `student_guardian`
--

INSERT INTO `student_guardian` (`id`, `student_id`, `guardian_id`, `status`, `created_at`, `updated_at`, `message`) VALUES
(19, 47, 38, 'accepted', '2025-12-20 12:58:24', '2025-12-20 13:13:14', '');

-- --------------------------------------------------------

--
-- Table structure for table `task`
--

CREATE TABLE `task` (
  `task_id` int(11) NOT NULL,
  `semester` varchar(100) NOT NULL,
  `course_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `assigment_type` varchar(100) NOT NULL,
  `due_date` date NOT NULL,
  `submission_time` time NOT NULL,
  `complete` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `task`
--

INSERT INTO `task` (`task_id`, `semester`, `course_id`, `description`, `assigment_type`, `due_date`, `submission_time`, `complete`) VALUES
(79, '1', 34, 'project', 'Project', '2024-06-20', '13:00:00', 1),
(87, '1', 32, 'Exam ', 'Individual', '2023-02-03', '13:00:00', 1),
(91, '1', 32, 'homework', 'Individual', '2023-02-08', '08:30:00', 0),
(93, '1', 57, 'project', 'Project', '2025-12-24', '13:00:00', 1),
(94, '1', 60, 'homework2', 'Individual', '2025-12-27', '13:00:00', 0),
(95, '1', 61, 'Exam ', 'Exam', '2026-01-31', '10:00:00', 0),
(100, '1', 57, 'مناقشة نهائيه للمشروع', 'Project', '2025-12-25', '14:00:00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `taskuser`
--

CREATE TABLE `taskuser` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `shared` tinyint(4) NOT NULL,
  `accept` tinyint(1) NOT NULL DEFAULT 0,
  `current_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `taskuser`
--

INSERT INTO `taskuser` (`id`, `task_id`, `user_id`, `shared`, `accept`, `current_date`) VALUES
(80, 79, 29, 1, 1, '2025-12-20 16:10:02'),
(99, 87, 29, 1, 1, '2025-12-20 16:09:25'),
(105, 91, 29, 1, 1, NULL),
(109, 93, 29, 1, 1, '2025-12-23 15:27:06'),
(110, 95, 47, 1, 1, NULL),
(111, 94, 47, 1, 1, '2025-12-23 15:14:06'),
(114, 94, 29, 1, 1, '2025-12-23 15:26:10'),
(117, 100, 29, 1, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `university`
--

CREATE TABLE `university` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `university`
--

INSERT INTO `university` (`id`, `name`) VALUES
(1, 'King Saud University'),
(2, 'King Abdulaziz University'),
(3, 'King Fahd University of Petroleum and Minerals'),
(4, 'King Khalid University'),
(5, 'Imam Mohammad Ibn Saud Islamic University'),
(6, 'King Faisal University'),
(7, 'Umm Al-Qura University'),
(8, 'Islamic University of Madinah'),
(9, 'Taibah University'),
(10, 'Qassim University'),
(11, 'Taif University'),
(12, 'Jazan University'),
(13, 'Hail University'),
(14, 'Al Jouf University'),
(15, 'Northern Border University'),
(16, 'Tabuk University'),
(17, 'Najran University'),
(18, 'Bisha University'),
(19, 'Princess Nourah bint Abdulrahman University'),
(20, 'Prince Sattam bin Abdulaziz University'),
(21, 'Majmaah University'),
(22, 'Shaqra University'),
(23, 'Al-Baha University'),
(24, 'Hafr Al-Batin University'),
(25, 'Saudi Electronic University'),
(26, 'King Abdullah University of Science and Technology');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `user_type` varchar(100) NOT NULL DEFAULT '0',
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `university_id` int(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `town` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `user_type`, `first_name`, `last_name`, `university_id`, `city`, `town`) VALUES
(29, 'dana22@gmail.com', '$2y$10$SVCvrszwGsq17DFpaGfBdu6ILOaNUlo5PIQr57Ir5TFHIp0PJ00Nq', 'student', 'dana', 'Ahmed', 23, 'al-baha', 'al-baha'),
(30, 'omar56@gmail.com', '$2y$10$bHXG3r1q.J2X93Srgk29NOPYQ3MocoX8VDX5.Px9l/5MCZqCp.jVS', 'faculty', 'Omar', 'khaled', 23, 'al-baha', 'al-baha'),
(38, 'ahmed56@gmail.com', '$2y$10$ipPj4ZfrNapImOuITvO52.TcNl1rDfo6Nr2bs.bN4JhOrnZTuZlvi', 'parent', 'Ahmed', 'alghamdi', NULL, 'al-baha', 'al-baha'),
(47, 'dina8593@gmail.com', '$2y$10$Dbi.6sKN.iIWvIoey0I/WOTlB3sz.QSRhxuxHN75TY6R8AgyMKlkm', 'student', 'dina', 'Ahmed', 23, 'al-baha', 'al-baha');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `college`
--
ALTER TABLE `college`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_id` (`user_id`),
  ADD KEY `fk_college_constrains` (`college_id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reception_fk_user` (`recipient`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `fk_couurse_schedual` (`course_id`),
  ADD KEY `fk_user_schedual` (`user_id`);

--
-- Indexes for table `scheduleuser`
--
ALTER TABLE `scheduleuser`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `schedule_id` (`schedule_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `student_guardian`
--
ALTER TABLE `student_guardian`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_pair` (`student_id`,`guardian_id`),
  ADD KEY `guardian_id` (`guardian_id`);

--
-- Indexes for table `task`
--
ALTER TABLE `task`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `fk_course` (`course_id`);

--
-- Indexes for table `taskuser`
--
ALTER TABLE `taskuser`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_task_user` (`task_id`),
  ADD KEY `fk_user_shared` (`user_id`);

--
-- Indexes for table `university`
--
ALTER TABLE `university`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `univerisity_fk` (`university_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `college`
--
ALTER TABLE `college`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `course`
--
ALTER TABLE `course`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `scheduleuser`
--
ALTER TABLE `scheduleuser`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `student_guardian`
--
ALTER TABLE `student_guardian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `task`
--
ALTER TABLE `task`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `taskuser`
--
ALTER TABLE `taskuser`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `university`
--
ALTER TABLE `university`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `course`
--
ALTER TABLE `course`
  ADD CONSTRAINT `fk_college_constrains` FOREIGN KEY (`college_id`) REFERENCES `college` (`id`),
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `reception_fk_user` FOREIGN KEY (`recipient`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `fk_couurse_schedual` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_schedual` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `scheduleuser`
--
ALTER TABLE `scheduleuser`
  ADD CONSTRAINT `scheduleuser_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedule` (`schedule_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `scheduleuser_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_guardian`
--
ALTER TABLE `student_guardian`
  ADD CONSTRAINT `student_guardian_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_guardian_ibfk_2` FOREIGN KEY (`guardian_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task`
--
ALTER TABLE `task`
  ADD CONSTRAINT `fk_course` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `taskuser`
--
ALTER TABLE `taskuser`
  ADD CONSTRAINT `fk_task_user` FOREIGN KEY (`task_id`) REFERENCES `task` (`task_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_shared` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `univerisity_fk` FOREIGN KEY (`university_id`) REFERENCES `university` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
