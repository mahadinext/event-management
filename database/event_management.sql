-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 02, 2025 at 07:49 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `event_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `auth_logs`
--

CREATE TABLE `auth_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auth_logs`
--

INSERT INTO `auth_logs` (`id`, `user_id`, `action`, `ip_address`, `created_at`) VALUES
(1, 2, 'logout', '::1', '2025-01-28 17:45:06'),
(2, 2, 'logout', '::1', '2025-01-28 18:02:24'),
(3, 2, 'logout', '::1', '2025-01-29 01:37:08'),
(4, 2, 'logout', '::1', '2025-01-29 02:20:48'),
(5, 2, 'logout', '::1', '2025-01-30 16:53:37'),
(6, 2, 'logout', '::1', '2025-01-30 17:13:00'),
(7, 2, 'logout', '::1', '2025-01-31 00:58:15'),
(8, 2, 'logout', '::1', '2025-01-31 16:11:56'),
(9, 2, 'logout', '::1', '2025-01-31 16:24:40'),
(10, 2, 'logout', '::1', '2025-01-31 16:42:05'),
(11, 2, 'logout', '::1', '2025-01-31 17:44:24'),
(12, 1, 'logout', '::1', '2025-02-01 12:20:21'),
(13, 2, 'logout', '::1', '2025-02-01 12:23:21'),
(14, 1, 'logout', '::1', '2025-02-01 13:13:48'),
(15, 1, 'logout', '::1', '2025-02-01 13:47:42'),
(16, 1, 'logout', '::1', '2025-02-01 13:49:55'),
(17, 2, 'logout', '::1', '2025-02-01 13:50:09'),
(18, 2, 'logout', '::1', '2025-02-02 02:45:20'),
(19, 3, 'logout', '::1', '2025-02-02 02:47:46'),
(20, 1, 'logout', '::1', '2025-02-02 03:21:00'),
(21, 1, 'logout', '::1', '2025-02-02 03:21:23');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `registration_deadline` datetime DEFAULT NULL,
  `max_attendees` int(11) NOT NULL DEFAULT 0,
  `event_location` text NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1: Active, 2: Inactive',
  `registration_type` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1: User Only, 2: All Allowed, 3: Guest Only',
  `event_type` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1: Free, 2: Paid',
  `ticket_price` decimal(10,2) DEFAULT 0.00,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `name`, `slug`, `description`, `event_date`, `registration_deadline`, `max_attendees`, `event_location`, `status`, `registration_type`, `event_type`, `ticket_price`, `created_by`, `updated_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'T1', 't1', 'T', '2025-01-31 12:12:00', '2025-01-30 12:12:00', 100, 'Dhaka', 2, 2, 1, 0.00, 2, 2, 2, '2025-01-29 06:33:15', '2025-01-29 11:53:39', '2025-01-29 11:53:39'),
(2, 'Private-25 Event', 'user-only', 'UO', '2025-02-08 11:11:00', '2025-02-05 12:12:00', 2, 'Comilla', 1, 1, 2, 100.00, 2, 1, NULL, '2025-01-29 10:39:22', '2025-02-01 03:04:30', NULL),
(3, 'Guest only', 'guest-only', 'GOK', '2025-03-08 12:12:00', '2025-02-07 11:11:00', 5, 'Kushtia', 1, 3, 2, 10.00, 2, 2, 2, '2025-01-29 10:59:07', '2025-01-31 16:26:31', '2025-01-31 16:26:31'),
(4, 'Open For All', 'all-user', 'AUB', '2025-02-07 10:10:00', '2025-01-04 10:10:00', 100, 'Bangladesh', 1, 2, 1, 0.00, 2, 1, NULL, '2025-01-29 11:02:14', '2025-02-02 05:30:27', NULL),
(5, 'Test Event', 'test-event', 'It is a test event', '2025-02-26 12:12:00', '2025-02-11 12:12:00', 100, 'Dhaka', 1, 1, 1, 0.00, 4, 4, NULL, '2025-02-02 03:23:15', '2025-02-02 05:37:06', NULL),
(6, 'February Event', 'february-event', 'February EWU Event', '2025-03-01 12:12:00', '2025-02-28 12:12:00', 100, 'EWU', 2, 2, 1, 0.00, 4, NULL, NULL, '2025-02-02 06:22:21', '2025-02-02 06:22:21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

CREATE TABLE `event_registrations` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `guest_name` varchar(255) DEFAULT NULL,
  `guest_email` varchar(255) DEFAULT NULL,
  `guest_phone` varchar(20) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_registrations`
--

INSERT INTO `event_registrations` (`id`, `event_id`, `user_id`, `guest_name`, `guest_email`, `guest_phone`, `registration_date`, `created_at`, `updated_at`) VALUES
(1, 4, NULL, 'A1', 'a@d.x', '01623421591', '2025-01-31 17:15:58', '2025-01-31 17:15:58', '2025-01-31 17:15:58'),
(2, 4, NULL, 'A1', 'a@d.x1', '01623421591', '2025-01-31 17:39:21', '2025-01-31 17:39:21', '2025-01-31 17:39:21'),
(3, 2, 2, NULL, NULL, NULL, '2025-02-01 17:43:55', '2025-02-01 17:43:55', '2025-02-01 17:43:55'),
(4, 2, 4, NULL, NULL, NULL, '2025-02-02 17:43:55', '2025-02-02 17:43:55', '2025-02-02 17:43:55');

-- --------------------------------------------------------

--
-- Table structure for table `failed_login_attempts`
--

CREATE TABLE `failed_login_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `failed_login_attempts`
--

INSERT INTO `failed_login_attempts` (`id`, `email`, `ip_address`, `created_at`) VALUES
(6, 'm@m.x', '::1', '2025-02-02 02:44:53');

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'User', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL DEFAULT 'web',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'view-users', 'web', 'Can view user listings', '2025-01-27 16:57:26', '2025-01-27 16:57:26'),
(2, 'create-users', 'web', 'Can create new users', '2025-01-27 16:57:26', '2025-01-27 16:57:26'),
(3, 'edit-users', 'web', 'Can edit existing users', '2025-01-27 16:57:26', '2025-01-27 16:57:26'),
(4, 'delete-users', 'web', 'Can delete users', '2025-01-27 16:57:26', '2025-01-27 16:57:26'),
(5, 'view-roles', 'web', 'Can view role listings', '2025-01-27 16:57:26', '2025-01-27 16:57:26'),
(6, 'create-roles', 'web', 'Can create new roles', '2025-01-27 16:57:26', '2025-01-27 16:57:26'),
(7, 'edit-roles', 'web', 'Can edit existing roles', '2025-01-27 16:57:26', '2025-01-27 16:57:26'),
(8, 'delete-roles', 'web', 'Can delete roles', '2025-01-27 16:57:26', '2025-01-27 16:57:26'),
(9, 'view-permissions', 'web', 'Can view permission listings', '2025-01-27 16:57:26', '2025-01-27 16:57:26'),
(10, 'create-permissions', 'web', 'Can create new permissions', '2025-01-27 16:57:26', '2025-01-27 16:57:26'),
(11, 'edit-permissions', 'web', 'Can edit existing permissions', '2025-01-27 16:57:26', '2025-01-27 16:57:26'),
(12, 'delete-permissions', 'web', 'Can delete permissions', '2025-01-27 16:57:26', '2025-01-27 16:57:26'),
(13, 'view-events', 'web', 'Can view event listings', '2025-01-27 16:57:26', '2025-01-27 16:57:26'),
(14, 'create-events', 'web', 'Can create new events', '2025-01-27 16:57:26', '2025-01-27 16:57:26'),
(15, 'edit-events', 'web', 'Can edit existing events', '2025-01-27 16:57:26', '2025-01-27 16:57:26'),
(16, 'delete-events', 'web', 'Can delete events', '2025-01-27 16:57:26', '2025-01-27 16:57:26'),
(17, 'publish-events', 'web', 'Can publish events', '2025-01-27 16:57:26', '2025-01-27 16:57:26'),
(18, 'register-events', 'web', 'Can register for events', '2025-01-27 16:57:26', '2025-01-27 16:57:26');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL DEFAULT 'web',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'super-admin', 'web', 'Super Administrator with full system access', '2025-01-27 16:57:26', '2025-01-27 16:57:26'),
(2, 'admin', 'web', 'Administrator with elevated privileges', '2025-01-27 16:57:26', '2025-01-27 16:57:26'),
(3, 'manager', 'web', 'Manager with specific area access', '2025-01-27 16:57:26', '2025-01-27 16:57:26'),
(4, 'user', 'web', 'Standard user account', '2025-01-27 16:57:26', '2025-01-27 16:57:26');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 2),
(3, 1),
(3, 2),
(4, 1),
(5, 1),
(5, 2),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(9, 2),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(13, 2),
(13, 3),
(13, 4),
(14, 1),
(14, 2),
(14, 3),
(15, 1),
(15, 2),
(15, 3),
(16, 1),
(16, 2),
(17, 1),
(17, 2),
(17, 3),
(18, 1),
(18, 4);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1 COMMENT '1 => "Active", 2 => "Inactive"',
  `user_type` tinyint(4) NOT NULL DEFAULT 2 COMMENT '1: Admin, 2: Attendee',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `email_verified_at`, `remember_token`, `status`, `user_type`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Admin', 'admin@admin.com', '$2y$12$aLjH/ZXgscmsypdcfEjPaO3Joo5LF.Jr0m9NF9KPBYu19H1USXnIG', '2025-01-27 16:57:26', NULL, 1, 1, '2025-01-27 16:57:26', '2025-02-02 03:21:20', NULL),
(2, 'Test Tets', 'm@m.c', '$2y$12$zDVYqyf9HWVrFidUG8MeS.ab38HiOPCB00J6nx3kDTqkp.aq1g5o6', '2025-01-31 17:42:12', NULL, 1, 2, '2025-01-28 07:04:26', '2025-02-02 02:45:17', NULL),
(3, 'Attendee', 'attendee@attendee.com', '$2y$11$xEk24cj4gYVKxL5nntohp.CPAFpYySiFwiqgQLO6wS9VlZ4KzIruW', NULL, NULL, 1, 2, '2025-02-02 02:46:42', '2025-02-02 02:47:37', NULL),
(4, 'tests fu', 'tfu@f.c', '$2y$11$NhkG/k/l12Woyr88kQVGuuBwxTyZEuPYfJ7k.DPJg9OcglHOhcm5i', NULL, NULL, 1, 1, '2025-02-02 03:21:59', '2025-02-02 03:24:08', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_login_history`
--

CREATE TABLE `user_login_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `login_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `logout_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_login_history`
--

INSERT INTO `user_login_history` (`id`, `user_id`, `ip_address`, `user_agent`, `login_at`, `logout_at`) VALUES
(1, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-01-28 07:13:50', NULL),
(2, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-01-28 07:14:15', NULL),
(3, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-01-28 07:15:43', NULL),
(4, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-01-28 14:16:47', NULL),
(5, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-01-28 14:20:48', NULL),
(6, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-01-28 14:26:46', NULL),
(7, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-01-28 14:31:23', NULL),
(8, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-01-28 14:32:48', NULL),
(9, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-01-28 14:37:16', NULL),
(10, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-01-28 14:42:26', NULL),
(11, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-01-28 18:02:21', NULL),
(12, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-01-29 01:37:05', NULL),
(13, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-01-29 02:20:45', NULL),
(14, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-01-29 02:20:56', NULL),
(15, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-01-30 17:12:15', NULL),
(16, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-01-31 00:47:22', NULL),
(17, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-01-31 01:00:35', NULL),
(18, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-01-31 16:04:27', NULL),
(19, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-01-31 16:19:35', NULL),
(20, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-01-31 16:24:50', NULL),
(21, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-01-31 17:42:33', NULL),
(22, 1, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-01-31 17:44:36', NULL),
(23, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-02-01 12:21:20', NULL),
(24, 1, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-02-01 12:23:33', NULL),
(25, 1, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-02-01 13:42:25', NULL),
(26, 1, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-02-01 13:47:54', NULL),
(27, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-02-01 13:50:06', NULL),
(28, 2, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-02-02 02:45:17', NULL),
(29, 3, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-02-02 02:46:42', NULL),
(30, 1, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-02-02 02:48:39', NULL),
(31, 1, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-02-02 03:21:20', NULL),
(32, 4, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:134.0) Gecko/20100101 Firefox/134.0', '2025-02-02 03:21:59', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auth_logs`
--
ALTER TABLE `auth_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_updated_by` (`updated_by`),
  ADD KEY `idx_deleted_by` (`deleted_by`);

--
-- Indexes for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_registration` (`event_id`,`user_id`,`guest_email`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `failed_login_attempts`
--
ALTER TABLE `failed_login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_ip` (`email`,`ip_address`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`),
  ADD KEY `password_resets_token_index` (`token`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_index` (`role_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_user_type` (`user_type`);

--
-- Indexes for table `user_login_history`
--
ALTER TABLE `user_login_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_login_history_user_id_index` (`user_id`),
  ADD KEY `user_login_history_ip_address_index` (`ip_address`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auth_logs`
--
ALTER TABLE `auth_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `failed_login_attempts`
--
ALTER TABLE `failed_login_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_login_history`
--
ALTER TABLE `user_login_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `auth_logs`
--
ALTER TABLE `auth_logs`
  ADD CONSTRAINT `auth_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`),
  ADD CONSTRAINT `event_registrations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `fk_model_has_permissions_permissions` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `fk_model_has_roles_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `fk_role_has_permissions_permissions` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_role_has_permissions_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
