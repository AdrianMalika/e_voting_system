-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 22, 2025 at 05:38 AM
-- Server version: 8.2.0
-- PHP Version: 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `e_voting_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `application_period`
--

DROP TABLE IF EXISTS `application_period`;
CREATE TABLE IF NOT EXISTS `application_period` (
  `id` int NOT NULL AUTO_INCREMENT,
  `end` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_period` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_period`
--

INSERT INTO `application_period` (`id`, `end`) VALUES
(1, 1741844220),
(2, 1741844340),
(3, 1741954680),
(4, 1744460340),
(5, 1742370900),
(6, 1743321660);

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=565 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'register', NULL, '127.0.0.1', '2025-01-07 12:17:00'),
(2, 1, 'login', NULL, '127.0.0.1', '2025-01-07 12:17:08'),
(3, 1, 'LOGIN', 'User logged in successfully', '::1', '2025-01-07 12:26:59'),
(4, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-01-07 12:54:41'),
(5, 3, 'REGISTRATION', 'New candidate registration', '::1', '2025-01-07 13:14:13'),
(6, 3, 'LOGIN_FAILED', 'Candidate account not yet approved', '::1', '2025-01-07 13:14:28'),
(7, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-01-07 13:14:51'),
(8, 2, 'CANDIDATE_APPROVE', 'Candidate Noordin was approved', '::1', '2025-01-07 13:19:48'),
(9, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: Noordin@gmail.com', '::1', '2025-01-07 13:20:08'),
(10, 3, 'LOGIN', 'User logged in successfully', '::1', '2025-01-07 13:20:18'),
(11, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-01-07 13:32:53'),
(12, 2, 'ELECTION_CREATE', 'Created election: Predential elections', '::1', '2025-01-07 13:36:31'),
(13, 2, 'CANDIDATES_ADDED', 'Added candidates to election: Predential elections', '::1', '2025-01-07 13:42:09'),
(14, 2, 'ELECTION_CREATE', 'Created election: class leader', '::1', '2025-01-07 14:23:31'),
(15, 2, 'ELECTION_DELETE', 'Deleted election: Predential elections', '::1', '2025-01-07 15:02:59'),
(16, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: Noordin@gmail.com', '127.0.0.1', '2025-01-08 14:31:46'),
(17, 3, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-08 14:32:04'),
(18, 3, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-08 14:48:40'),
(19, 3, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-08 14:57:03'),
(20, 3, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-08 15:01:57'),
(21, 3, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-08 15:02:28'),
(22, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-08 15:03:15'),
(23, 2, 'ADD_TO_ELECTION', 'Added candidate ID: 1 to election ID: 5', '127.0.0.1', '2025-01-08 15:27:50'),
(24, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-08 15:27:59'),
(25, 3, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-08 15:28:09'),
(26, 3, 'REQUEST_JOIN_ELECTION', 'Requested to join election ID: 2', '127.0.0.1', '2025-01-08 15:53:49'),
(27, 3, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-08 15:56:23'),
(28, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-08 15:56:58'),
(29, 2, 'CANDIDATE_REQUEST_APPROVE', 'Election ID: 3, Candidate ID: 1', '127.0.0.1', '2025-01-08 15:57:58'),
(30, 2, 'CANDIDATE_REQUEST_APPROVE', 'Election ID: 4, Candidate ID: 1', '127.0.0.1', '2025-01-08 15:58:11'),
(31, 2, 'CANDIDATE_REQUEST_APPROVE', 'Election ID: 2, Candidate ID: 1', '127.0.0.1', '2025-01-08 15:58:20'),
(32, 2, 'CANDIDATE_REQUEST_APPROVE', 'Election ID: 5, Candidate ID: 1', '127.0.0.1', '2025-01-08 15:58:46'),
(33, 2, 'UPDATE_ELECTION', 'Updated election: Voting for leader (ID: 5)', '127.0.0.1', '2025-01-08 16:01:54'),
(34, 2, 'UPDATE_ELECTION', 'Updated election: Voting for leader (ID: 5)', '127.0.0.1', '2025-01-08 16:02:12'),
(35, 2, 'UPDATE_ELECTION', 'Updated election: Voting for leader (ID: 5) - Status changed to: active', '127.0.0.1', '2025-01-08 16:06:02'),
(36, 2, 'UPDATE_ELECTION', 'Updated election: Voting for leader (ID: 5) - Status changed to: active', '127.0.0.1', '2025-01-08 16:06:15'),
(37, 2, 'UPDATE_ELECTION', 'Updated election: Voting for leader (ID: 5) - Status changed to: active', '127.0.0.1', '2025-01-08 16:07:11'),
(38, 2, 'UPDATE_ELECTION', 'Updated election: Voting for leader (ID: 5) - Status changed to: active', '127.0.0.1', '2025-01-08 16:09:38'),
(39, 2, 'UPDATE_ELECTION', 'Updated election: class leader (ID: 2) - Status changed to: active', '127.0.0.1', '2025-01-08 16:10:11'),
(40, 2, 'UPDATE_ELECTION', 'Updated election: Voting for leader (ID: 5) - Status changed to: active', '127.0.0.1', '2025-01-08 16:10:20'),
(41, 2, 'UPDATE_ELECTION', 'Updated election: Voting for leader (ID: 5) - Status changed to: active', '127.0.0.1', '2025-01-08 16:14:50'),
(42, 2, 'UPDATE_ELECTION', 'Updated election: Voting for leader (ID: 5) - Status changed to: active', '127.0.0.1', '2025-01-08 16:15:49'),
(43, 2, 'UPDATE_ELECTION', 'Updated election: Voting for leader (ID: 5) - Status changed to: active', '127.0.0.1', '2025-01-08 16:28:46'),
(44, 2, 'UPDATE_ELECTION', 'Updated election: Voting for leader (ID: 5) - Status changed to: active', '127.0.0.1', '2025-01-08 16:29:00'),
(45, 2, 'UPDATE_ELECTION', 'Updated election: Voting for leader (ID: 5) - Status changed to: upcoming', '127.0.0.1', '2025-01-08 16:31:40'),
(46, 2, 'UPDATE_ELECTION', 'Updated election: Voting for leader (ID: 5) - Status changed to: upcoming', '127.0.0.1', '2025-01-08 16:34:30'),
(47, 2, 'UPDATE_ELECTION', 'Updated election: Voting for leader (ID: 5) - Status changed to: active', '127.0.0.1', '2025-01-08 16:38:15'),
(48, 2, 'UPDATE_ELECTION', 'Updated election: Voting for leader (ID: 5) - Status changed to: active', '127.0.0.1', '2025-01-08 16:46:16'),
(49, 2, 'UPDATE_ELECTION', 'Updated election: class leader (ID: 2) - Status changed to: active', '127.0.0.1', '2025-01-08 16:46:56'),
(50, 2, 'UPDATE_ELECTION', 'Updated election: Voting for leader (ID: 5) - Status changed to: active', '127.0.0.1', '2025-01-08 16:47:03'),
(51, 2, 'UPDATE_ELECTION', 'Updated election: Voting for leader (ID: 5) - Status changed to: active', '127.0.0.1', '2025-01-08 16:51:48'),
(52, 2, 'UPDATE_ELECTION', 'Updated election: Voting for leader (ID: 5) - Status changed to: active', '127.0.0.1', '2025-01-08 16:58:33'),
(53, 2, 'UPDATE_ELECTION', 'Updated election: Voting for leader (ID: 5) - Status changed to: active', '127.0.0.1', '2025-01-08 16:59:05'),
(54, 2, 'UPDATE_ELECTION', 'Updated election: Voting for leader (ID: 5) - Status changed to: active', '127.0.0.1', '2025-01-08 17:03:43'),
(55, 2, 'UPDATE_ELECTION', 'Updated election: Voting for leader (ID: 5) - Status changed to: upcoming', '127.0.0.1', '2025-01-08 17:03:55'),
(56, 2, 'UPDATE_ELECTION', 'Updated election: Voting for leader (ID: 5) - Status changed to: active', '127.0.0.1', '2025-01-08 17:04:10'),
(57, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-08 17:06:21'),
(58, 1, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-08 17:06:49'),
(59, 1, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-08 17:07:35'),
(60, 1, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-08 17:10:38'),
(61, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-08 17:10:48'),
(62, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-08 17:17:05'),
(63, 3, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-08 17:17:14'),
(64, 3, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-08 17:19:14'),
(65, 1, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-08 17:19:23'),
(66, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-13 16:48:24'),
(67, 2, 'UPDATE_ELECTION', 'Updated election: leader (ID: 4) - Status changed to: upcoming', '127.0.0.1', '2025-01-13 17:04:56'),
(68, 2, 'UPDATE_ELECTION', 'Updated election: leader (ID: 4) - Status changed to: active', '127.0.0.1', '2025-01-13 17:05:07'),
(69, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-13 17:05:21'),
(70, 1, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-13 17:05:15'),
(71, 1, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-13 17:10:13'),
(72, 1, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-13 17:10:26'),
(73, 1, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-13 17:30:03'),
(74, 4, 'REGISTRATION', 'New student registration', '127.0.0.1', '2025-01-13 17:30:35'),
(75, 4, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-13 17:30:44'),
(76, 4, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-13 17:31:30'),
(77, 5, 'REGISTRATION', 'New candidate registration', '127.0.0.1', '2025-01-13 17:32:18'),
(78, 5, 'LOGIN_FAILED', 'Candidate account not yet approved', '127.0.0.1', '2025-01-13 17:32:32'),
(79, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-13 17:32:44'),
(80, 2, 'CANDIDATE_APPROVE', 'Candidate bright was approved', '127.0.0.1', '2025-01-13 17:33:47'),
(81, 2, 'ADD_TO_ELECTION', 'Added candidate ID: 2 to election ID: 5', '127.0.0.1', '2025-01-13 17:36:11'),
(82, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-13 17:38:01'),
(83, 1, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-13 17:38:07'),
(84, 1, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-13 17:38:51'),
(85, 5, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-13 17:38:57'),
(86, 5, 'REQUEST_JOIN_ELECTION', 'Requested to join election ID: 4', '127.0.0.1', '2025-01-13 17:39:43'),
(87, 5, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-13 17:43:33'),
(88, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-13 17:43:46'),
(89, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-13 17:46:44'),
(90, 5, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-13 17:46:50'),
(91, 5, 'REQUEST_JOIN_ELECTION', 'Requested to join election ID: 2', '127.0.0.1', '2025-01-13 17:46:56'),
(92, 5, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-13 17:47:16'),
(93, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-13 17:47:21'),
(94, 2, 'CANDIDATE_REQUEST_APPROVE', 'Election ID: 2, Candidate ID: 2', '127.0.0.1', '2025-01-13 17:52:07'),
(95, 2, 'CANDIDATE_REQUEST_APPROVE', 'Election ID: 4, Candidate ID: 2', '127.0.0.1', '2025-01-13 17:52:13'),
(96, 2, 'CANDIDATE_REQUEST_APPROVE', 'Election ID: 5, Candidate ID: 2', '127.0.0.1', '2025-01-13 17:52:18'),
(97, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-13 17:52:27'),
(98, 1, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-13 17:52:34'),
(99, 1, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-13 18:20:18'),
(100, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-13 18:20:27'),
(101, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-13 18:28:02'),
(102, 1, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-13 18:28:08'),
(103, 1, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-13 18:29:06'),
(104, 4, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-13 18:29:17'),
(105, 4, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-13 18:29:38'),
(106, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-13 18:29:46'),
(107, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-13 18:31:03'),
(108, 6, 'REGISTRATION', 'New student registration', '127.0.0.1', '2025-01-13 18:31:37'),
(109, 6, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-13 18:31:45'),
(110, 6, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-13 18:31:58'),
(111, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-13 18:32:06'),
(112, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-13 18:33:32'),
(113, 5, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-13 18:33:42'),
(114, 5, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-13 18:34:06'),
(115, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-13 18:34:12'),
(116, 2, 'UPDATE_ELECTION', 'Updated election: leader (ID: 4) - Status changed to: completed', '127.0.0.1', '2025-01-13 18:34:28'),
(117, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-13 18:34:34'),
(118, 5, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-13 18:34:41'),
(119, 3, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-13 18:42:18'),
(120, 3, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-13 18:46:58'),
(121, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-13 18:47:07'),
(122, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-13 18:49:28'),
(123, 5, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-13 18:49:33'),
(124, 5, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-13 18:50:36'),
(125, 1, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 08:02:39'),
(126, 1, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 08:04:54'),
(127, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 08:05:02'),
(128, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 08:07:57'),
(129, 5, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 08:08:03'),
(130, 5, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 08:17:28'),
(131, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 08:17:52'),
(132, 2, 'ELECTION_DELETE', 'Deleted election: teacher', '127.0.0.1', '2025-01-14 08:32:03'),
(133, 2, 'ELECTION_DELETE', 'Deleted election: teacher', '127.0.0.1', '2025-01-14 08:32:07'),
(134, 2, 'ELECTION_DELETE', 'Deleted election: teacher', '127.0.0.1', '2025-01-14 08:32:11'),
(135, 2, 'ELECTION_DELETE', 'Deleted election: teacher', '127.0.0.1', '2025-01-14 08:32:14'),
(136, 2, 'CREATE_ELECTION', 'Created election: CEO', '127.0.0.1', '2025-01-14 08:35:14'),
(137, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 08:35:32'),
(138, 5, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 08:35:39'),
(139, 5, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 08:35:54'),
(140, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 08:36:00'),
(141, 2, 'UPDATE_ELECTION', 'Updated election: CEO (ID: 10) - Status changed to: active', '127.0.0.1', '2025-01-14 08:36:18'),
(142, 2, 'CREATE_ELECTION', 'Created election: stricker', '127.0.0.1', '2025-01-14 08:37:28'),
(143, 2, 'UPDATE_ELECTION', 'Updated election: stricker (ID: 12) - Status changed to: active', '127.0.0.1', '2025-01-14 08:39:34'),
(144, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 08:39:40'),
(145, 5, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 08:39:52'),
(146, 5, 'REQUEST_JOIN_ELECTION', 'Requested to join election ID: 10', '127.0.0.1', '2025-01-14 08:39:56'),
(147, 5, 'REQUEST_JOIN_ELECTION', 'Requested to join election ID: 12', '127.0.0.1', '2025-01-14 08:40:01'),
(148, 5, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 08:40:04'),
(149, 3, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 08:40:14'),
(150, 3, 'REQUEST_JOIN_ELECTION', 'Requested to join election ID: 10', '127.0.0.1', '2025-01-14 08:40:18'),
(151, 3, 'REQUEST_JOIN_ELECTION', 'Requested to join election ID: 12', '127.0.0.1', '2025-01-14 08:40:21'),
(152, 3, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 08:40:25'),
(153, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 08:40:30'),
(154, 2, 'CREATE_ELECTION', 'Created election: goal keeper', '127.0.0.1', '2025-01-14 08:47:45'),
(155, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 09:14:53'),
(156, 3, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 09:14:59'),
(157, 3, 'REQUEST_JOIN_ELECTION', 'Requested to join election ID: 13', '127.0.0.1', '2025-01-14 09:15:08'),
(158, 3, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 09:15:15'),
(159, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 09:15:25'),
(160, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 09:16:28'),
(161, 5, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 09:16:36'),
(162, 5, 'REQUEST_JOIN_ELECTION', 'Requested to join election ID: 13', '127.0.0.1', '2025-01-14 09:16:49'),
(163, 5, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 09:16:54'),
(164, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 09:16:59'),
(165, 2, 'CANDIDATE_REQUEST_APPROVE', 'Election ID: 13, Candidate ID: 2', '127.0.0.1', '2025-01-14 09:21:25'),
(166, 2, 'CANDIDATE_REQUEST_APPROVE', 'Election ID: 13, Candidate ID: 1', '127.0.0.1', '2025-01-14 09:21:28'),
(167, 2, 'CANDIDATE_REQUEST_APPROVE', 'Election ID: 10, Candidate ID: 1', '127.0.0.1', '2025-01-14 09:21:36'),
(168, 2, 'CANDIDATE_REQUEST_APPROVE', 'Election ID: 10, Candidate ID: 2', '127.0.0.1', '2025-01-14 09:21:39'),
(169, 2, 'CANDIDATE_REQUEST_APPROVE', 'Election ID: 12, Candidate ID: 1', '127.0.0.1', '2025-01-14 09:21:48'),
(170, 2, 'CANDIDATE_REQUEST_APPROVE', 'Election ID: 12, Candidate ID: 2', '127.0.0.1', '2025-01-14 09:21:52'),
(171, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 09:23:19'),
(172, 1, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 09:23:25'),
(173, 1, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 09:23:49'),
(174, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 09:23:56'),
(175, 2, 'UPDATE_ELECTION', 'Updated election: goal keeper (ID: 13) - Status changed to: active', '127.0.0.1', '2025-01-14 09:24:10'),
(176, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 09:24:22'),
(177, 1, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 09:24:28'),
(178, 1, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 09:36:34'),
(179, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 09:36:39'),
(180, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 09:41:28'),
(181, 3, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 09:41:42'),
(182, 3, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 09:50:05'),
(183, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 09:50:16'),
(184, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 11:05:51'),
(185, 3, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 11:08:39'),
(186, 3, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 11:10:20'),
(187, 1, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 11:10:30'),
(188, 1, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 11:10:40'),
(189, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 11:10:55'),
(190, 2, 'UPDATE_ELECTION', 'Updated election: goal keeper (ID: 13) - Status changed to: completed', '127.0.0.1', '2025-01-14 11:11:14'),
(191, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 11:11:21'),
(192, 6, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 11:11:36'),
(193, 6, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 11:12:25'),
(194, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 11:12:44'),
(195, 2, 'UPDATE_ELECTION', 'Updated election: stricker (ID: 12) - Status changed to: completed', '127.0.0.1', '2025-01-14 11:12:59'),
(196, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 11:13:09'),
(197, 6, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 11:13:14'),
(198, 6, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 11:13:34'),
(199, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 11:13:43'),
(200, 2, 'CREATE_ELECTION', 'Created election: football', '127.0.0.1', '2025-01-14 11:17:29'),
(201, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 11:18:19'),
(202, 6, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 11:18:25'),
(203, 6, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 11:24:45'),
(204, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 11:24:56'),
(205, 2, 'CREATE_ELECTION', 'Created election: driver', '127.0.0.1', '2025-01-14 11:26:22'),
(206, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 11:26:29'),
(207, 6, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 11:26:37'),
(208, 6, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 11:29:53'),
(209, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 11:29:59'),
(210, 2, 'CREATE_ELECTION', 'Created election: therapy', '127.0.0.1', '2025-01-14 11:31:16'),
(211, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 11:31:20'),
(212, 6, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 11:31:26'),
(213, 6, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 11:31:43'),
(214, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 11:31:51'),
(215, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 11:32:55'),
(216, 6, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 11:33:41'),
(217, 6, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 11:37:37'),
(218, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 11:37:42'),
(219, 2, 'CREATE_ELECTION', 'Created election: jjjjjjjjjjjjjjjjj', '127.0.0.1', '2025-01-14 11:38:40'),
(220, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 11:38:46'),
(221, 6, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 11:39:12'),
(222, 6, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 11:44:28'),
(223, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 11:44:36'),
(224, 2, 'CREATE_ELECTION', 'Created election: mememmememem', '127.0.0.1', '2025-01-14 11:45:45'),
(225, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 11:46:00'),
(226, 6, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 11:46:24'),
(227, 6, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 11:51:49'),
(228, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 11:51:55'),
(229, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 11:54:28'),
(230, 6, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 11:54:35'),
(231, 6, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 12:12:09'),
(232, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 12:12:16'),
(233, 2, 'CREATE_ELECTION', 'Created election: Public Health', '127.0.0.1', '2025-01-14 12:13:05'),
(234, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 12:15:08'),
(235, 6, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 12:15:14'),
(236, 6, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 12:19:21'),
(237, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 12:19:31'),
(238, 2, 'CREATE_ELECTION', 'Created election: In-Home Health Care Management', '127.0.0.1', '2025-01-14 12:20:08'),
(239, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 12:20:56'),
(240, 1, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 12:21:14'),
(241, 6, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 13:03:38'),
(242, 6, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 13:10:11'),
(243, 6, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 13:39:41'),
(244, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: gwen@gmail.com', '127.0.0.1', '2025-01-14 13:40:00'),
(245, 7, 'REGISTRATION', 'New student registration', '127.0.0.1', '2025-01-14 13:41:08'),
(246, 7, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 13:41:17'),
(247, 7, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 13:41:25'),
(248, 8, 'REGISTRATION', 'New candidate registration', '127.0.0.1', '2025-01-14 13:42:26'),
(249, 8, 'LOGIN_FAILED', 'Candidate account not yet approved', '127.0.0.1', '2025-01-14 13:42:37'),
(250, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 13:42:47'),
(251, 2, 'CANDIDATE_APPROVE', 'Candidate bester was approved', '127.0.0.1', '2025-01-14 13:43:15'),
(252, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 13:43:21'),
(253, 8, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 13:43:30'),
(254, 8, 'REQUEST_JOIN_ELECTION', 'Requested to join election ID: 2', '127.0.0.1', '2025-01-14 13:43:39'),
(255, 8, 'REQUEST_JOIN_ELECTION', 'Requested to join election ID: 5', '127.0.0.1', '2025-01-14 13:44:33'),
(256, 8, 'REQUEST_JOIN_ELECTION', 'Requested to join election ID: 10', '127.0.0.1', '2025-01-14 13:44:38'),
(257, 8, 'REQUEST_JOIN_ELECTION', 'Requested to join election ID: 14', '127.0.0.1', '2025-01-14 13:44:44'),
(258, 8, 'REQUEST_JOIN_ELECTION', 'Requested to join election ID: 15', '127.0.0.1', '2025-01-14 13:44:47'),
(259, 8, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 13:45:04'),
(260, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 13:45:11'),
(261, 2, 'CANDIDATE_REQUEST_APPROVE', 'Election ID: 2, Candidate ID: 3', '127.0.0.1', '2025-01-14 13:46:05'),
(262, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 13:46:19'),
(263, 6, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 13:46:25'),
(264, 6, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 13:46:36'),
(265, 9, 'REGISTRATION', 'New student registration', '127.0.0.1', '2025-01-14 13:47:18'),
(266, 9, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 13:47:29'),
(267, 9, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 13:48:28'),
(268, 1, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 13:48:37'),
(269, 1, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-14 13:50:33'),
(270, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-14 13:50:37'),
(271, 2, 'CANDIDATE_REQUEST_APPROVE', 'Election ID: 5, Candidate ID: 3', '127.0.0.1', '2025-01-18 10:51:38'),
(272, 2, 'CANDIDATE_REQUEST_APPROVE', 'Election ID: 15, Candidate ID: 3', '127.0.0.1', '2025-01-18 10:51:42'),
(273, 2, 'CANDIDATE_REQUEST_APPROVE', 'Election ID: 14, Candidate ID: 3', '127.0.0.1', '2025-01-18 10:51:48'),
(274, 2, 'CANDIDATE_REQUEST_APPROVE', 'Election ID: 10, Candidate ID: 3', '127.0.0.1', '2025-01-18 10:51:54'),
(275, 2, 'UPDATE_ELECTION', 'Updated election: class leader (ID: 2) - Status changed to: completed', '127.0.0.1', '2025-01-18 10:52:07'),
(276, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-20 06:28:05'),
(277, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-20 08:39:22'),
(278, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-20 08:39:32'),
(279, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-20 08:39:38'),
(280, 2, 'CREATE_ELECTION', 'Created election: Class president', '127.0.0.1', '2025-01-20 08:47:18'),
(281, 2, 'UPDATE_ELECTION', 'Updated election: Class president (ID: 24) - Status changed to: active', '127.0.0.1', '2025-01-20 08:47:40'),
(282, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-20 08:47:44'),
(283, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: imran@gmail.com', '127.0.0.1', '2025-01-20 08:47:53'),
(284, 10, 'REGISTRATION', 'New student registration', '127.0.0.1', '2025-01-20 08:48:14'),
(285, 10, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-20 08:48:21'),
(286, 10, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-20 08:48:54'),
(287, 11, 'REGISTRATION', 'New candidate registration', '127.0.0.1', '2025-01-20 08:49:17'),
(288, 11, 'LOGIN_FAILED', 'Candidate account not yet approved', '127.0.0.1', '2025-01-20 08:49:24'),
(289, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-20 08:49:31'),
(290, 2, 'APPROVE_CANDIDATE', 'Approved candidate ID: 4', '127.0.0.1', '2025-01-20 08:49:35'),
(291, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-20 08:49:41'),
(292, 11, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-20 08:49:47'),
(293, 11, 'REQUEST_JOIN_ELECTION', 'Requested to join election ID: 5', '127.0.0.1', '2025-01-20 08:50:32'),
(294, 11, 'REQUEST_JOIN_ELECTION', 'Requested to join election ID: 10', '127.0.0.1', '2025-01-20 08:50:37'),
(295, 11, 'REQUEST_JOIN_ELECTION', 'Requested to join election ID: 14', '127.0.0.1', '2025-01-20 08:50:39'),
(296, 11, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-20 08:50:55'),
(297, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-20 08:51:01'),
(298, 2, 'ADD_TO_ELECTION', 'Added candidate ID: 4 to election ID: 24', '127.0.0.1', '2025-01-20 08:51:20'),
(299, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-20 08:52:52'),
(300, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-21 06:37:35'),
(301, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-22 06:08:19'),
(302, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin1@gmail.com', '127.0.0.1', '2025-01-22 09:38:18'),
(303, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-22 09:38:23'),
(304, 1, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-01-28 06:59:27'),
(305, 10, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-01-28 06:59:38'),
(306, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: imran@gmail.com', '127.0.0.1', '2025-02-03 10:02:43'),
(307, 10, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-03 10:02:54'),
(308, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-03 10:04:00'),
(309, 10, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-04 06:32:53'),
(310, 10, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-02-04 06:33:16'),
(311, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-04 06:33:25'),
(312, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-02-04 07:04:51'),
(313, 10, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-04 07:05:27'),
(314, 10, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-02-04 07:05:31'),
(315, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-02-04 17:25:13'),
(316, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-02-04 17:25:38'),
(317, 10, 'LOGIN', 'User logged in successfully', '::1', '2025-02-04 17:38:53'),
(318, 10, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-05 06:56:17'),
(319, 10, 'LOGIN', 'User logged in successfully', '::1', '2025-02-05 07:32:03'),
(320, 10, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-07 08:25:15'),
(321, 10, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-02-07 08:26:11'),
(322, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-07 08:26:19'),
(323, 10, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-11 09:01:46'),
(324, 10, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-02-11 09:02:11'),
(325, 12, 'REGISTRATION', 'New student registration', '127.0.0.1', '2025-02-12 07:52:33'),
(326, 12, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-12 07:52:41'),
(327, 12, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-02-12 07:53:08'),
(328, 13, 'REGISTRATION', 'New candidate registration', '127.0.0.1', '2025-02-12 07:54:26'),
(329, 14, 'REGISTRATION', 'New candidate registration', '127.0.0.1', '2025-02-12 07:55:08'),
(330, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-12 07:55:22'),
(331, 2, 'CREATE_ELECTION', 'Created election: Class president', '127.0.0.1', '2025-02-12 07:56:40'),
(332, 2, 'UPDATE_ELECTION', 'Updated election: Class president (ID: 25) - Status changed to: active', '127.0.0.1', '2025-02-12 07:57:26'),
(333, 2, 'CANDIDATE_APPROVE', 'Candidate oscar was approved', '127.0.0.1', '2025-02-12 07:57:50'),
(334, 2, 'CANDIDATE_APPROVE', 'Candidate paul was approved', '127.0.0.1', '2025-02-12 07:57:53'),
(335, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-02-12 07:58:01'),
(336, 14, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-12 07:58:19'),
(337, 14, 'REQUEST_JOIN_ELECTION', 'Requested to join election ID: 25', '127.0.0.1', '2025-02-12 07:59:21'),
(338, 14, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-02-12 07:59:48'),
(339, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-12 07:59:54'),
(340, 2, 'CANDIDATE_REQUEST_APPROVE', 'Election ID: 25, Candidate ID: 6', '127.0.0.1', '2025-02-12 08:00:19'),
(341, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-02-12 08:00:24'),
(342, 12, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-12 08:00:30'),
(343, 12, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-02-12 08:02:04'),
(344, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-12 08:02:09'),
(345, 2, 'UPDATE_ELECTION', 'Updated election: Class president (ID: 25) - Status changed to: completed', '127.0.0.1', '2025-02-12 08:02:23'),
(346, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-02-12 08:02:31'),
(347, 14, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-12 08:02:38'),
(348, 14, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-02-12 08:05:19'),
(349, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-12 08:05:27'),
(350, 2, 'UPDATE_ELECTION', 'Updated election: Class president (ID: 24) - Status changed to: completed', '127.0.0.1', '2025-02-12 08:05:57'),
(351, 2, 'UPDATE_ELECTION', 'Updated election: Voting for leader (ID: 5) - Status changed to: completed', '127.0.0.1', '2025-02-12 08:08:26'),
(352, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-02-12 08:10:16'),
(353, 15, 'REGISTRATION', 'New student registration', '127.0.0.1', '2025-02-12 08:12:47'),
(354, 15, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-12 08:12:57'),
(355, 15, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-02-12 08:13:32'),
(356, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin1@gmail.com', '127.0.0.1', '2025-02-12 08:13:43'),
(357, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-12 08:13:48'),
(358, 2, 'CREATE_ELECTION', 'Created election: intertainment prefect', '127.0.0.1', '2025-02-12 08:18:51'),
(359, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-02-12 08:40:15'),
(360, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-12 08:40:31'),
(361, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-02-12 08:42:19'),
(362, 12, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-12 08:42:27'),
(363, 12, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-02-12 08:42:51'),
(364, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-12 08:42:57'),
(365, 2, 'UPDATE_ELECTION', 'Updated election: intertainment prefect (ID: 26) - Status changed to: active', '127.0.0.1', '2025-02-12 08:43:05'),
(366, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-02-12 08:43:08'),
(367, 12, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-12 08:43:13'),
(368, 1, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-02-12 09:02:13'),
(369, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-12 09:03:08'),
(370, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-02-12 09:03:19'),
(371, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-12 09:35:28'),
(372, 2, 'UPDATE_ELECTION', 'Updated election: intertainment prefect', '127.0.0.1', '2025-02-12 09:36:27'),
(373, 2, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-02-12 09:40:12'),
(374, 2, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-02-12 10:18:47'),
(375, 16, 'REGISTRATION', 'New student registration', '::1', '2025-02-24 09:31:51'),
(376, 16, 'LOGIN', 'User logged in successfully', '::1', '2025-02-24 09:32:03'),
(377, 16, 'LOGOUT', 'User logged out successfully', '::1', '2025-02-24 09:33:01'),
(378, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: Admin@evoting.com', '::1', '2025-02-25 16:51:46'),
(379, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: Admin@evoting.Com', '::1', '2025-02-25 16:52:16'),
(380, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@evoting.com', '::1', '2025-02-25 16:53:05'),
(381, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-02-25 16:53:31'),
(382, 17, 'LOGIN', 'User logged in successfully', '::1', '2025-02-25 17:38:37'),
(383, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-02-25 17:41:33'),
(384, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-02-25 17:44:31'),
(385, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-02-25 19:31:56'),
(386, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-02-25 19:32:19'),
(387, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-07 05:06:30'),
(388, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-07 06:30:26'),
(389, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: snow@mail.com', '::1', '2025-03-07 06:30:37'),
(390, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-07 06:30:54'),
(391, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-07 06:31:18'),
(392, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: snow2@mail.com', '::1', '2025-03-07 06:31:26'),
(393, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-07 06:31:49'),
(394, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-07 06:32:27'),
(395, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-07 06:32:36'),
(396, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-07 06:47:11'),
(397, 20, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-07 06:50:29'),
(398, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-07 06:52:07'),
(399, 20, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-07 06:56:17'),
(400, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-07 06:56:37'),
(401, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-07 06:57:45'),
(402, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-07 06:57:54'),
(403, 21, 'REGISTRATION', 'New candidate registration', '::1', '2025-03-07 07:08:32'),
(404, 21, 'LOGIN_FAILED', 'Candidate account not yet approved', '::1', '2025-03-07 07:08:49'),
(405, 20, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-07 07:09:01'),
(406, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-07 07:09:18'),
(407, 2, 'CANDIDATE_APPROVE', 'Candidate Adrian Malika was approved', '::1', '2025-03-07 07:09:30'),
(408, 21, 'LOGIN', 'User logged in successfully', '::1', '2025-03-07 07:09:49'),
(409, 21, 'REQUEST_JOIN_ELECTION', 'Requested to join election ID: 26', '::1', '2025-03-07 07:10:51'),
(410, 2, 'CANDIDATE_APPROVE', 'Candidate Adrian Malika was approved', '::1', '2025-03-07 07:11:04'),
(411, 2, 'CANDIDATE_REQUEST_APPROVE', 'Election ID: 26, Candidate ID: 7', '::1', '2025-03-07 07:11:27'),
(412, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: snow@mail.com', '127.0.0.1', '2025-03-07 07:12:37'),
(413, 20, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-03-07 07:12:51'),
(414, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-10 07:44:07'),
(415, 20, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-10 07:53:59'),
(416, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-10 07:55:07'),
(417, 20, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-03-10 07:58:13'),
(418, 20, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-03-10 07:58:22'),
(419, 20, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-03-10 08:00:18'),
(420, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-10 08:03:35'),
(421, 20, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-10 08:06:26'),
(422, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-10 08:06:55'),
(423, 20, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-10 08:09:03'),
(424, 21, 'LOGIN', 'User logged in successfully', '::1', '2025-03-10 08:09:17'),
(425, 21, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-10 15:33:21'),
(426, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-10 15:33:31'),
(427, 2, 'REJECT_NOMINATION', 'Rejected nomination ID: 6', '::1', '2025-03-11 07:31:25'),
(428, 2, 'REJECT_NOMINATION', 'Rejected nomination ID: 5', '::1', '2025-03-11 07:31:35'),
(429, 20, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-11 13:53:45'),
(430, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: snow@mail.com', '::1', '2025-03-12 10:02:50'),
(431, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: snow@mail.com', '::1', '2025-03-12 10:03:18'),
(432, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: snow2@mail.com', '::1', '2025-03-12 10:03:36'),
(433, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: snow@mail.com', '::1', '2025-03-12 10:04:35'),
(434, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-12 10:04:45'),
(435, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: swon@gmail.com', '::1', '2025-03-12 10:08:16'),
(436, 20, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-12 10:08:31'),
(437, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: snow@mail.com', '::1', '2025-03-12 10:08:54'),
(438, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: snow@mail.com', '::1', '2025-03-12 10:09:14'),
(439, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: snow2@mail.com', '::1', '2025-03-12 10:09:34'),
(440, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-12 10:10:00'),
(441, 20, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-12 10:10:08'),
(442, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-12 10:10:27'),
(443, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-12 10:14:37'),
(444, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-12 12:29:49'),
(445, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-12 19:33:39'),
(446, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-13 05:37:01'),
(447, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-15 08:52:51'),
(448, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-15 11:44:13'),
(449, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-15 11:47:48'),
(450, 23, 'LOGIN', 'User logged in successfully', '::1', '2025-03-15 11:47:57'),
(451, 23, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-15 11:49:07'),
(452, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: adrianmalika01@gmail.com', '::1', '2025-03-15 11:49:21'),
(453, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-15 11:49:29'),
(454, 20, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-15 11:49:35'),
(455, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: snow2@mail.com', '::1', '2025-03-15 11:49:53'),
(456, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-15 11:50:53'),
(457, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-15 11:51:52'),
(458, 21, 'LOGIN', 'User logged in successfully', '::1', '2025-03-15 11:52:07'),
(459, 21, 'REQUEST_JOIN_ELECTION', 'Requested to join election ID: 26', '::1', '2025-03-15 12:13:01'),
(460, 21, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-15 12:13:40'),
(461, 23, 'LOGIN', 'User logged in successfully', '::1', '2025-03-15 12:14:33'),
(462, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-16 07:25:21'),
(463, 20, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-16 07:25:40'),
(464, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-16 07:25:52'),
(465, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-16 07:36:00'),
(466, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: Admin@evoting.com', '::1', '2025-03-16 08:05:24'),
(467, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-16 08:05:35'),
(468, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-16 08:12:30'),
(469, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-16 08:12:45'),
(470, 20, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-16 08:13:27'),
(471, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-16 08:13:40'),
(472, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-16 08:25:18'),
(473, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-16 08:29:05'),
(474, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-16 08:30:10'),
(475, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-16 08:30:22'),
(476, 20, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-16 08:31:45'),
(477, 21, 'LOGIN', 'User logged in successfully', '::1', '2025-03-16 08:31:58'),
(478, 21, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-16 08:32:30'),
(479, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-16 08:32:43'),
(480, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-17 10:37:35'),
(481, 2, 'CREATE_ELECTION', 'Created election: h', '::1', '2025-03-17 10:43:42'),
(482, 20, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-17 10:57:40'),
(483, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-17 11:05:39'),
(484, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-17 11:06:40'),
(485, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-17 11:06:52'),
(486, 2, 'CANDIDATE_REQUEST_REJECT', 'Election ID: 27, Candidate ID: 7', '::1', '2025-03-17 11:49:12'),
(487, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-17 11:53:00'),
(488, 20, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-17 11:53:38'),
(489, 21, 'LOGIN', 'User logged in successfully', '::1', '2025-03-17 11:55:47'),
(490, 21, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-17 12:01:44'),
(491, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-17 12:02:00'),
(492, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-17 12:03:54'),
(493, 2, 'CREATE_ELECTION', 'Created election: sports', '::1', '2025-03-17 12:20:15'),
(494, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-17 15:18:44'),
(495, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-17 17:58:40'),
(496, 21, 'LOGIN', 'User logged in successfully', '::1', '2025-03-17 17:58:55'),
(497, 21, 'REQUEST_JOIN_ELECTION', 'Requested to join election ID: 28', '::1', '2025-03-17 17:59:02'),
(498, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-17 17:59:42'),
(499, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-17 17:59:55'),
(500, 20, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-17 18:00:48'),
(501, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-17 18:01:18'),
(502, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-18 19:58:33'),
(503, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-18 20:12:48'),
(504, 21, 'LOGIN', 'User logged in successfully', '::1', '2025-03-18 20:13:00'),
(505, 21, 'REQUEST_JOIN_ELECTION', 'Requested to join election ID: 32', '::1', '2025-03-18 20:13:12'),
(506, 21, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-18 20:13:21'),
(507, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-18 20:13:30'),
(508, 2, 'CANDIDATE_REQUEST_APPROVE', 'Election ID: 32, Candidate ID: 7', '::1', '2025-03-18 20:13:45'),
(509, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-18 20:14:55'),
(510, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: snow@mail.com', '::1', '2025-03-18 20:15:06'),
(511, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-18 20:15:19'),
(512, 20, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-18 20:22:46'),
(513, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-18 20:23:06'),
(514, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: Admin@evoting.com', '::1', '2025-03-19 07:32:00'),
(515, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-19 07:32:13'),
(516, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-19 07:39:44'),
(517, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-19 07:43:42'),
(518, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-19 07:44:27'),
(519, 20, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-19 07:48:40'),
(520, 24, 'LOGIN', 'User logged in successfully', '::1', '2025-03-19 07:49:16'),
(521, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-20 20:02:59'),
(522, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-20 20:04:11'),
(523, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-20 20:04:27'),
(524, 20, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-20 20:08:19'),
(525, 21, 'LOGIN', 'User logged in successfully', '::1', '2025-03-20 20:08:30'),
(526, 21, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-20 20:12:10'),
(527, 20, 'LOGIN', 'User logged in successfully', '::1', '2025-03-20 20:12:18'),
(528, 20, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-20 20:21:06'),
(529, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: Admin@evoting.comadmin123', '::1', '2025-03-20 20:21:34'),
(530, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-20 20:21:53'),
(531, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: adrianmalika01@gmail.com', '::1', '2025-03-20 20:22:11'),
(532, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: adrianmalika01@gmail.com', '::1', '2025-03-20 20:22:19'),
(533, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: adrianmalika01@gmail.com', '::1', '2025-03-20 20:22:32'),
(534, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-20 20:22:48'),
(535, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: adrianmalika01@gmail.com', '::1', '2025-03-20 20:23:06'),
(536, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-20 20:23:33'),
(537, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-20 20:25:00'),
(538, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: adrianmalika01@gmail.com', '::1', '2025-03-20 20:25:11'),
(539, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: adrianmalika01@gmail.com', '::1', '2025-03-20 20:25:31'),
(540, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: adrianmalika01@gmail.com', '::1', '2025-03-20 20:25:49'),
(541, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-20 20:26:00'),
(542, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-20 20:27:16'),
(543, 25, 'LOGIN', 'User logged in successfully', '::1', '2025-03-20 20:27:32'),
(544, 25, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-20 20:31:39'),
(545, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-20 20:31:49'),
(546, 20, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-03-21 06:33:51');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(547, 2, 'APPROVE_NOMINATION', 'Approved nomination ID: 9', '::1', '2025-03-21 09:41:21'),
(548, 2, 'APPROVE_NOMINATION', 'Approved nomination ID: 8', '::1', '2025-03-21 09:41:23'),
(549, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-21 11:29:23'),
(550, NULL, 'LOGIN_FAILED', 'Failed login attempt for email: Admin@evoting.com', '::1', '2025-03-21 11:30:30'),
(551, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-21 11:30:46'),
(552, 2, 'LOGOUT', 'User logged out successfully', '::1', '2025-03-21 15:12:33'),
(553, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-21 18:50:22'),
(554, 26, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-03-21 19:04:43'),
(555, 2, 'APPROVE_NOMINATION', 'Approved nomination ID: 10', '::1', '2025-03-21 19:11:39'),
(556, 2, 'LOGIN', 'User logged in successfully', '::1', '2025-03-22 03:34:57'),
(557, 27, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-03-22 03:38:32'),
(558, 2, 'APPROVE_NOMINATION', 'Approved nomination ID: 11', '::1', '2025-03-22 03:52:01'),
(559, 27, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-03-22 05:00:16'),
(560, 28, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-03-22 05:00:36'),
(561, 28, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-03-22 05:11:51'),
(562, 27, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-03-22 05:11:56'),
(563, 27, 'LOGOUT', 'User logged out successfully', '127.0.0.1', '2025-03-22 05:14:24'),
(564, 28, 'LOGIN', 'User logged in successfully', '127.0.0.1', '2025-03-22 05:14:49');

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

DROP TABLE IF EXISTS `candidates`;
CREATE TABLE IF NOT EXISTS `candidates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `position` varchar(100) NOT NULL,
  `manifesto` text,
  `photo_url` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `user_id`, `position`, `manifesto`, `photo_url`, `status`, `created_at`) VALUES
(1, 3, 'Manager', 'i will hep', 'uploads/candidates/candidate_3_1736347985.jpg', 'approved', '2025-01-07 13:14:13'),
(2, 5, 'class leader', 'OK so my name is Bright and I\'m running for the cross leader and my manifesto is if you vote for me definitely everything will change the oral problems that I noted which only stone which I plan to change if I am the cross reader so begin with the class doesn\'t have enough resources most of them are a boxer you have seen them and nobody is doing anything about it and I believe that I\'m the right person for these opportunity', 'uploads/candidates/candidate_5_1736790185.jpg', 'approved', '2025-01-13 17:32:18'),
(3, 8, 'Manager', 'I will give you what you want', 'uploads/candidates/candidate_8_1736862264.jpg', 'approved', '2025-01-14 13:42:26'),
(4, 11, 'learder', 'vote for me', 'uploads/candidates/candidate_11_1737363023.jpe', 'approved', '2025-01-20 08:49:17'),
(5, 13, 'Unspecified', NULL, NULL, 'approved', '2025-02-12 07:54:26'),
(6, 14, 'leader', 'Vote for me', 'uploads/candidates/candidate_14_1739347144.jpg', 'approved', '2025-02-12 07:55:08'),
(7, 21, 'Unspecified', 'just a chilled guy!!', 'uploads/candidates/candidate_21_1741331419.jpg', 'approved', '2025-03-07 07:08:32');

-- --------------------------------------------------------

--
-- Table structure for table `elections`
--

DROP TABLE IF EXISTS `elections`;
CREATE TABLE IF NOT EXISTS `elections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `branch` varchar(50) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `status` enum('upcoming','active','completed') DEFAULT 'upcoming',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `manual_status` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_election` (`title`,`start_date`,`end_date`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `elections`
--

INSERT INTO `elections` (`id`, `title`, `description`, `branch`, `start_date`, `end_date`, `status`, `created_by`, `created_at`, `updated_at`, `manual_status`) VALUES
(34, 'Student Council Elections 2025', 'ubueb', 'Blantyre', '2025-03-22 14:58:00', '2025-03-30 14:58:00', 'upcoming', 2, '2025-03-21 12:59:06', '2025-03-22 05:37:53', 0),
(35, 'Student Council Elections 2025', 'bubhhu', 'Lilongwe', '2025-03-22 07:13:00', '2025-03-22 07:15:00', 'upcoming', 2, '2025-03-22 05:14:12', '2025-03-22 05:37:53', 0);

-- --------------------------------------------------------

--
-- Table structure for table `election_candidates`
--

DROP TABLE IF EXISTS `election_candidates`;
CREATE TABLE IF NOT EXISTS `election_candidates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `election_id` int NOT NULL,
  `candidate_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_election_candidate` (`election_id`,`candidate_id`),
  KEY `candidate_id` (`candidate_id`)
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `election_candidates`
--

INSERT INTO `election_candidates` (`id`, `election_id`, `candidate_id`, `created_at`, `status`) VALUES
(1, 1, 1, '2025-01-07 13:42:09', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `election_positions`
--

DROP TABLE IF EXISTS `election_positions`;
CREATE TABLE IF NOT EXISTS `election_positions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `election_id` int NOT NULL,
  `position_name` varchar(100) NOT NULL,
  `position_description` text,
  `required_year` int DEFAULT NULL,
  `max_candidates` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_position_per_election` (`election_id`,`position_name`),
  KEY `election_id` (`election_id`),
  KEY `idx_election_position` (`election_id`,`position_name`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `election_positions`
--

INSERT INTO `election_positions` (`id`, `election_id`, `position_name`, `position_description`, `required_year`, `max_candidates`, `created_at`) VALUES
(21, 34, 'Sports Prefect ', 'look over all sports events', 2, 7, '2025-03-21 12:59:06'),
(22, 35, 'Sports Prefect ', 'look over all sports events', 0, 1, '2025-03-22 05:14:12');

-- --------------------------------------------------------

--
-- Table structure for table `nominations`
--

DROP TABLE IF EXISTS `nominations`;
CREATE TABLE IF NOT EXISTS `nominations` (
  `nomination_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `surname` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `program` varchar(255) NOT NULL,
  `year_of_study` int NOT NULL,
  `role` text NOT NULL,
  `branch` text NOT NULL,
  `photo_path` varchar(255) NOT NULL,
  `manifesto` text NOT NULL,
  `submission_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `election_id` int NOT NULL,
  PRIMARY KEY (`nomination_id`),
  KEY `user_id` (`user_id`),
  KEY `election_id` (`election_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `nominations`
--

INSERT INTO `nominations` (`nomination_id`, `user_id`, `first_name`, `surname`, `email`, `phone`, `student_id`, `program`, `year_of_study`, `role`, `branch`, `photo_path`, `manifesto`, `submission_date`, `status`, `election_id`) VALUES
(11, 27, 'Adrian', 'Mailka', 'adrianmalika01@gmail.com', '0889545477', '88585', 'Phamactical', 2, 'Sports Prefect', 'Blantyre', '../uploads/profile_photos/67de33cb38903_1658787782673.jpg', 'I love Sports!!!', '2025-03-22 03:51:39', 'approved', 34),
(10, 26, 'Jon', 'Snow', 'snow@mail.com', '0889545477', '1777', 'Phamactical', 2, 'Sports Prefect', 'Blantyre', '../uploads/profile_photos/67ddb9d0e5dce_JonSnow.jpg', 'I am the rocket!!', '2025-03-21 19:11:12', 'approved', 34);

-- --------------------------------------------------------

--
-- Table structure for table `nomination_documents`
--

DROP TABLE IF EXISTS `nomination_documents`;
CREATE TABLE IF NOT EXISTS `nomination_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nomination_id` int NOT NULL,
  `document_type` enum('photo','transcript') NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` int NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `nomination_id` (`nomination_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `nomination_documents`
--

INSERT INTO `nomination_documents` (`id`, `nomination_id`, `document_type`, `file_path`, `file_name`, `file_size`, `mime_type`, `created_at`) VALUES
(1, 7, 'photo', '../uploads/profile_photos/67cfda02895d8_1658787782673.jpg', '67cfda02895d8_1658787782673.jpg', 19397, 'image/jpeg', '2025-03-11 06:36:50'),
(2, 7, 'transcript', '../uploads/academic_transcripts/67cfda0289fe1_Academic Transcript.docx', '67cfda0289fe1_Academic Transcript.docx', 18572, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '2025-03-11 06:36:50'),
(3, 8, 'photo', '../uploads/profile_photos/67cfe8135c3b8_JonSnow.jpg', '67cfe8135c3b8_JonSnow.jpg', 116643, 'image/jpeg', '2025-03-11 07:36:51'),
(4, 8, 'transcript', '../uploads/academic_transcripts/67cfe8135c89f_Academic Transcript.docx', '67cfe8135c89f_Academic Transcript.docx', 18572, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '2025-03-11 07:36:51'),
(5, 9, 'photo', '../uploads/profile_photos/67dc7b0814b37_1658787782673.jpg', '67dc7b0814b37_1658787782673.jpg', 19397, 'image/jpeg', '2025-03-20 20:31:04'),
(6, 10, 'photo', '../uploads/profile_photos/67ddb9d0e5dce_JonSnow.jpg', '67ddb9d0e5dce_JonSnow.jpg', 116643, 'image/jpeg', '2025-03-21 19:11:12'),
(7, 11, 'photo', '../uploads/profile_photos/67de33cb38903_1658787782673.jpg', '67de33cb38903_1658787782673.jpg', 19397, 'image/jpeg', '2025-03-22 03:51:39');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` enum('new_candidate','election_request','new_vote','election_ended','new_election','election_started') NOT NULL,
  `message` text NOT NULL,
  `reference_id` int DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `is_read` (`is_read`)
) ENGINE=InnoDB AUTO_INCREMENT=155 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `message`, `reference_id`, `reference_type`, `is_read`, `created_at`, `user_id`) VALUES
(132, 'election_ended', 'Election \'h\' has ended', 27, 'election', 1, '2025-03-17 11:41:35', NULL),
(133, 'election_ended', 'Election \'h\' has ended', 27, 'election', 1, '2025-03-17 11:48:22', NULL),
(134, 'election_ended', 'Election \'h\' has ended', 27, 'election', 1, '2025-03-17 11:48:43', NULL),
(135, 'new_election', 'New election created: sports (Starting: Mar 19, 2025)', 28, 'election', 1, '2025-03-17 12:20:15', 2),
(136, 'new_election', 'A new election \'sports\' has been created and will start on Mar 19, 2025', 28, 'election', 1, '2025-03-17 12:20:15', 1),
(137, 'new_election', 'A new election \'sports\' has been created and will start on Mar 19, 2025', 28, 'election', 1, '2025-03-17 12:20:15', 4),
(138, 'new_election', 'A new election \'sports\' has been created and will start on Mar 19, 2025', 28, 'election', 1, '2025-03-17 12:20:15', 6),
(139, 'new_election', 'A new election \'sports\' has been created and will start on Mar 19, 2025', 28, 'election', 1, '2025-03-17 12:20:15', 19),
(140, 'new_election', 'A new election \'sports\' has been created and will start on Mar 19, 2025', 28, 'election', 1, '2025-03-17 12:20:15', 9),
(141, 'new_election', 'A new election \'sports\' has been created and will start on Mar 19, 2025', 28, 'election', 1, '2025-03-17 12:20:15', 10),
(142, 'new_election', 'A new election \'sports\' has been created and will start on Mar 19, 2025', 28, 'election', 1, '2025-03-17 12:20:15', 12),
(143, 'new_election', 'A new election \'sports\' has been created and will start on Mar 19, 2025', 28, 'election', 1, '2025-03-17 12:20:15', 15),
(144, 'new_election', 'A new election \'sports\' has been created and will start on Mar 19, 2025', 28, 'election', 1, '2025-03-17 12:20:15', 20),
(145, 'new_election', 'A new election \'sports\' has been created and will start on Mar 19, 2025', 28, 'election', 1, '2025-03-17 12:20:15', 23),
(146, 'new_election', 'New election created: Student Council Elections 2025', 32, 'election', 1, '2025-03-17 20:05:47', 2),
(147, 'new_election', 'New election created: Student Council Elections 2025', 33, 'election', 1, '2025-03-19 07:58:26', 2),
(148, 'election_ended', 'Election \'Student Council Elections 2025\' has ended', 32, 'election', 1, '2025-03-20 20:26:06', NULL),
(149, 'election_ended', 'Election \'Student Council Elections 2025\' has ended', 32, 'election', 1, '2025-03-20 20:31:51', NULL),
(150, 'election_ended', 'Election \'Student Council Elections 2025\' has ended', 32, 'election', 1, '2025-03-20 20:32:12', NULL),
(151, 'election_ended', 'Election \'Student Council Elections 2025\' has ended', 32, 'election', 1, '2025-03-21 06:30:41', NULL),
(152, 'new_election', 'New election created: Student Council Elections 2025', 34, 'election', 0, '2025-03-21 12:59:06', 2),
(153, 'new_election', 'New election created: Student Council Elections 2025 (Lilongwe Branch)', 35, 'election', 0, '2025-03-22 05:14:12', 2),
(154, 'election_ended', 'Election \'Student Council Elections 2025\' has ended', 35, 'election', 0, '2025-03-22 05:37:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `student_number` varchar(20) DEFAULT NULL,
  `branch` varchar(50) DEFAULT NULL,
  `year_of_study` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('student','admin','candidate') NOT NULL DEFAULT 'student',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `student_number` (`student_number`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `student_number`, `branch`, `year_of_study`, `password_hash`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Noordin', 'haroonchirimba2@gmail.com', '213123', NULL, NULL, '$2y$10$uBUHaXOJlV..Z3MFwCTouetgYJsmaU1napVb/zF6z1ZZs3./bmDri', 'student', '2025-01-07 12:17:00', '2025-03-07 06:27:29'),
(2, 'System Admin', 'admin@evoting.com', NULL, 'Blantyre', NULL, '$2y$10$.h8kWeeWhH.eWIYgfPDVuugDgEs3GDSdIdX09I70ETPYaBQ5VtciS', 'admin', '2025-01-07 12:53:46', '2025-03-22 04:29:07'),
(3, 'Noordin', 'Noordin@gmail.com', '123123132', NULL, NULL, '$2y$10$Uv8COfuKJrSKWc9HyJD3Zu5BdZr9yg46N/zgrT4gABYWUnHzGoIiK', 'candidate', '2025-01-07 13:14:13', '2025-01-07 13:14:13'),
(5, 'bright', 'bright@gmail.com', '21314324', NULL, NULL, '$2y$10$U42o.5h3wh7jAEKsy/sMneDCbpjynhwiHvdQ.jFYG62cTEKxPK.RK', 'candidate', '2025-01-13 17:32:18', '2025-01-13 17:32:18'),
(6, 'marry', 'marry@gmail.com', '123123', NULL, NULL, '$2y$10$mpvFpvgNqgkqYxFQu4k39OUeGsTgyPTQZhvNvb7Bi5AYM9tuO7yja', 'student', '2025-01-13 18:31:37', '2025-01-13 18:31:37'),
(27, 'Adrian Mailka', 'adrianmalika01@gmail.com', '55554', 'Blantyre', 'Third Year', '$2y$10$c9P/UOL9pB5I92ZdA8HU..1khCLqqVTvI2PjL11HTSThaH9GzoYI2', 'student', '2025-03-22 03:36:11', '2025-03-22 03:36:11'),
(8, 'bester', 'bester@gmail.com', '32423423', NULL, NULL, '$2y$10$NPZEyczOhfcgx4aNikKS5utnA7JR6VuXS0fXFBxjna2.3Vn5RARI.', 'candidate', '2025-01-14 13:42:26', '2025-01-14 13:42:26'),
(9, 'gon frics', 'gon@gmail.com', '12345', NULL, NULL, '$2y$10$n5lSdP91W7RfUR4KHsDn8.re53LGz8ZWRjY/HJKv3lOA1hyi7vZHK', 'student', '2025-01-14 13:47:18', '2025-01-14 13:47:18'),
(10, 'imran', 'imran@gmail.com', '567894', NULL, NULL, '$2y$10$Ud3c/vgTYmHDSGA6HFRzWetgdkMyrSi6uHChgI/0ulONB6Ouf4Xki', 'student', '2025-01-20 08:48:14', '2025-01-20 08:48:14'),
(11, 'trevor', 'trevor@gmail.com', '6748484', NULL, NULL, '$2y$10$g5E3QtwOBJjMPLRTX0Gx/OypWBWf6zyW3MB...85bLbPJs2tnmtMS', 'candidate', '2025-01-20 08:49:17', '2025-01-20 08:49:17'),
(12, 'paul', 'paul@gmail.com', '12345676', NULL, NULL, '$2y$10$EO5er8eus87Pf9PEuN5vwOfjCxwZ/3zPiiuoQ2Lvs3adp6yq5m.k6', 'student', '2025-02-12 07:52:33', '2025-02-12 07:52:33'),
(13, 'paul', 'paul1@gmail.com', '12345678', NULL, NULL, '$2y$10$3JwCcbS7KlVC/TrBsuI3rebddgabf4tbawjEFewa/zFv6b.2..ZoC', 'candidate', '2025-02-12 07:54:26', '2025-02-12 07:54:26'),
(14, 'oscar', 'oscar@gmail.com', '12345677', NULL, NULL, '$2y$10$6LpKVuxXaBxiqBRK4lFdbucdCtgJiuztkKiQuHXgXNX7UBwMo0fP6', 'candidate', '2025-02-12 07:55:08', '2025-02-12 07:55:08'),
(15, 'khash', 'khash@gmail.com', '1234556', NULL, NULL, '$2y$10$u/hSUaYfsfceP1x/vgB3z.lX3v/EyjNit8mrAZcJZeNz5rwIkdwOa', 'student', '2025-02-12 08:12:47', '2025-02-12 08:12:47'),
(21, 'Adrian Malika', 'adrianmalika@gmail.com', '5111', NULL, NULL, '$2y$10$Py8ZaXkFh2Er5VkEodSRKOz0uwUycKXtAoH8KPfRbIlLg6HPGDYLO', 'candidate', '2025-03-07 07:08:32', '2025-03-07 07:08:32'),
(24, 'Imran', 'imrantawakali55@gmail.com', '55522', NULL, NULL, '$2y$10$NskTwOAgZ6WYVF0nXnDTmuzXib90oLvGx6gkrXQAN52zqNSMLpo.K', 'student', '2025-03-19 07:46:10', '2025-03-19 07:46:10'),
(26, 'Jon snow', 'snow@mail.com', '1777', 'Blantyre', 'Fourth Year', '$2y$10$bc9sZxBcDHWGzuD0zOpweOsxrih91OZDXhLolmfhMLoju.9b3HOKe', 'student', '2025-03-21 19:04:02', '2025-03-21 19:04:02'),
(28, 'Jack Patrickson', 'jack@mail.com', '78882', 'Lilongwe', 'Second Year', '$2y$10$UQNbWefV6zAoW2gxZNDacuFntqBZ7OJY8cvSnSL7PFYezwO4fMk/y', 'student', '2025-03-22 05:00:01', '2025-03-22 05:00:01');

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

DROP TABLE IF EXISTS `votes`;
CREATE TABLE IF NOT EXISTS `votes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `election_id` int NOT NULL,
  `voter_id` int NOT NULL,
  `candidate_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_vote` (`election_id`,`voter_id`),
  KEY `voter_id` (`voter_id`),
  KEY `candidate_id` (`candidate_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `election_positions`
--
ALTER TABLE `election_positions`
  ADD CONSTRAINT `fk_election_positions_election_id` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
