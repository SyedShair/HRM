-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 23, 2026 at 10:13 PM
-- Server version: 11.8.8-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u775564071_hrm`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-zoom_access_token', 's:634:\"eyJzdiI6IjAwMDAwMiIsImFsZyI6IkhTNTEyIiwidiI6IjIuMCIsImtpZCI6IjVjODQwY2YyLTlkMmItNGY1Ny1iMzU4LWUzNjFlODZkNjE5MCJ9.eyJhdWQiOiJodHRwczovL29hdXRoLnpvb20udXMiLCJ1aWQiOiI4T0ZGbWpRY1I2S2NWWmwwZmRVZ2F3IiwidmVyIjoxMCwiYXVpZCI6IjA0OTJjMWMwNTNjOTRjYTVkMTM1YjRkYjVjYTU0YjYzOTkxNzdjMDhlYjA5MTU3ZWM2MTQyYjQ3ZmJlZmY4ODgiLCJuYmYiOjE3ODc0NDE0NjEsImNvZGUiOiJ4ME5PYW1DOFNvV3pKM2thTEdDV0N3Y1JyNWp4amc3c3MiLCJpc3MiOiJ6bTpjaWQ6OXZkR3hrMXJUc3laSkliMVVHYXhoZyIsImdubyI6MCwiZXhwIjoxNzg3NDQ1MDYxLCJ0eXBlIjozLCJpYXQiOjE3ODc0NDE0NjEsImFpZCI6IndhNUV5emRlVGVPVDYyT2ROaWlPV1EifQ.LIvM9dNzopxLAT3-KLqsFuOCrKfdA7Ry5ETawLnoAq8nsNQCCtlrfxR2fqAjd9kpFQ8Anga-hrwPLeMQ35AHZw\";', 1787444761);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daily_salaries`
--

CREATE TABLE `daily_salaries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `idno` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `total_hours` decimal(5,2) NOT NULL,
  `rate` decimal(8,2) NOT NULL,
  `daily_salary` decimal(10,2) NOT NULL,
  `status` enum('Pending','Paid') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_types`
--

CREATE TABLE `document_types` (
  `id` int(10) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `document_types`
--

INSERT INTO `document_types` (`id`, `label`, `is_required`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Passport / Photo ID', 1, 1, 1, '2026-07-31 01:29:06', '2026-07-31 01:29:06'),
(2, 'Right to Work (Share Code)', 1, 1, 2, '2026-07-31 01:29:06', '2026-07-31 01:29:06'),
(3, 'Visa / BRP', 1, 1, 3, '2026-07-31 01:29:06', '2026-07-31 01:29:06'),
(4, 'Certificate of Sponsorship (COS)', 1, 1, 4, '2026-07-31 01:29:06', '2026-07-31 01:29:06'),
(5, 'National Insurance', 1, 1, 5, '2026-07-31 01:29:06', '2026-07-31 01:29:06'),
(6, 'Proof of Address', 1, 1, 6, '2026-07-31 01:29:06', '2026-08-23 22:12:42'),
(7, 'P45 / Starter Checklist', 1, 1, 7, '2026-07-31 01:29:06', '2026-07-31 01:29:06'),
(8, 'Bank Details', 1, 1, 8, '2026-07-31 01:29:06', '2026-07-31 01:29:06'),
(9, 'Signed Employment Contract', 1, 1, 9, '2026-07-31 01:29:06', '2026-07-31 01:29:06'),
(10, 'Emergency Contact / Next of Kin', 1, 1, 10, '2026-07-31 01:29:06', '2026-07-31 01:29:06'),
(11, 'Bank Statement', 1, 1, 11, '2026-08-19 13:02:27', '2026-08-19 13:15:23'),
(12, 'Change of Circumstances', 0, 1, 12, '2026-08-23 22:12:23', '2026-08-23 22:12:23'),
(13, 'Pension Opt In / Opt Out', 0, 1, 13, '2026-08-23 22:12:23', '2026-08-23 22:12:23'),
(14, 'Payslip', 0, 1, 14, '2026-08-23 22:12:23', '2026-08-23 22:12:23');

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference` int(10) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `milestone` varchar(50) DEFAULT NULL,
  `document_date` date DEFAULT NULL,
  `to_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `sent_by` int(10) UNSIGNED DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'sent',
  `error` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_documents`
--

CREATE TABLE `employee_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `people_id` bigint(20) UNSIGNED DEFAULT NULL,
  `document_type_id` int(11) UNSIGNED DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_documents`
--

INSERT INTO `employee_documents` (`id`, `people_id`, `document_type_id`, `file_name`, `file_path`, `file_type`, `created_at`, `updated_at`) VALUES
(1, 2, NULL, 'Passport', 'employee_documents/MOHSIN_KHALID/1787086653_Passport_Mohsin_Khalid.pdf', 'pdf', '2026-08-18 20:57:33', '2026-08-18 20:57:33'),
(2, 2, NULL, 'Right to Work Checked', 'employee_documents/MOHSIN_KHALID/1787086688_04-12-2026_Right_to_Work_Check_-_Mohsin_Khalid.pdf', 'pdf', '2026-08-18 20:58:08', '2026-08-18 20:58:08'),
(3, 2, NULL, 'BRP', 'employee_documents/MOHSIN_KHALID/1787086717_BRP_Mohsin_Khalid.pdf', 'pdf', '2026-08-18 20:58:37', '2026-08-18 20:58:37'),
(4, 2, NULL, 'Home Office Decision', 'employee_documents/MOHSIN_KHALID/1787086737_GWF073421111_BRP_Decision_2023-11-09_Mohsin_Khalid.pdf', 'pdf', '2026-08-18 20:58:57', '2026-08-18 20:58:57'),
(5, 2, 8, 'Bank Statement', 'employee_documents/MOHSIN_KHALID/1787123139_Bank_Statement_March_2026_Mohsin_Khalid.pdf', 'pdf', '2026-08-19 07:05:39', '2026-08-19 07:05:39'),
(6, 2, 6, 'Driving Licence', 'employee_documents/MOHSIN_KHALID/1787123177_Driving_Licence_-_Mohsin_Khalid.pdf', 'pdf', '2026-08-19 07:06:17', '2026-08-19 07:06:17'),
(7, 2, 6, 'National ID Card', 'employee_documents/MOHSIN_KHALID/1787143210_Nadra_ID_Card_Mohsin_Khalid.pdf', 'pdf', '2026-08-19 12:40:10', '2026-08-19 12:40:10'),
(11, 1, 6, 'bank statement', 'employee_documents/RUBY_ZAHID/1787145087_1787144656_1787143265_March_2026_Bank_Statement_Ruby_Zahid.pdf', 'pdf', '2026-08-19 13:11:27', '2026-08-19 13:11:27'),
(16, 3, 8, 'Bank Statement', 'employee_documents/FARHAN_ALI/1787168842_Bank_Statement_April_2026_Farhan_Ali.pdf', 'pdf', '2026-08-19 19:47:22', '2026-08-19 19:47:22'),
(17, 12, 6, 'Bank Statement', 'employee_documents/AYESHA_SANA/1787395405_Bank_Statement_April_2026.pdf', 'pdf', '2026-08-22 10:43:25', '2026-08-22 10:43:25'),
(18, 12, 8, 'Bank Statement', 'employee_documents/AYESHA_SANA/1787395438_Bank_Statement_April_2026.pdf', 'pdf', '2026-08-22 10:43:58', '2026-08-22 10:43:58'),
(19, 11, 8, 'Bank Statement', 'employee_documents/ADNAN_KHAN/1787396066_Bank_Statement_April_2026.pdf', 'pdf', '2026-08-22 10:54:26', '2026-08-22 10:54:26'),
(20, 11, 6, 'Bank Statement', 'employee_documents/ADNAN_KHAN/1787396098_Bank_Statement_April_2026.pdf', 'pdf', '2026-08-22 10:54:58', '2026-08-22 10:54:58'),
(21, 3, 6, 'Driving Licence', 'employee_documents/FARHAN_ALI/1787396179_Proof_of_Address_Driving_Licence_-_Farhan_Ali.pdf', 'pdf', '2026-08-22 10:56:19', '2026-08-22 10:56:19');

-- --------------------------------------------------------

--
-- Table structure for table `meetings`
--

CREATE TABLE `meetings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `zoom_meeting_id` varchar(255) DEFAULT NULL,
  `topic` varchar(255) NOT NULL,
  `agenda` text DEFAULT NULL,
  `category` enum('interview','internal','client','other') NOT NULL DEFAULT 'internal',
  `host_employee_id` int(10) UNSIGNED DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `duration` int(10) UNSIGNED NOT NULL DEFAULT 30,
  `timezone` varchar(255) NOT NULL DEFAULT 'Europe/London',
  `join_url` text DEFAULT NULL,
  `start_url` text DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` enum('scheduled','started','ended','cancelled') NOT NULL DEFAULT 'scheduled',
  `recording_url` text DEFAULT NULL,
  `recording_password` varchar(255) DEFAULT NULL,
  `transcript_url` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `archive` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meetings`
--

INSERT INTO `meetings` (`id`, `zoom_meeting_id`, `topic`, `agenda`, `category`, `host_employee_id`, `start_time`, `duration`, `timezone`, `join_url`, `start_url`, `password`, `status`, `recording_url`, `recording_password`, `transcript_url`, `notes`, `created_by`, `archive`, `created_at`, `updated_at`) VALUES
(1, '86947467696', 'Backend developer', 'about interview', 'interview', 2, '2026-08-23 12:32:00', 30, 'Europe/London', 'https://us05web.zoom.us/j/86947467696?pwd=Ncis6obuDQICXPyY9Ry935QT7tGvsP.1', 'https://us05web.zoom.us/s/86947467696?zak=eyJ0eXAiOiJKV1QiLCJzdiI6IjAwMDAwMiIsInptX3NrbSI6InptX28ybSIsImFsZyI6IkhTMjU2In0.eyJpc3MiOiJ3ZWIiLCJjbHQiOjAsIm1udW0iOiI4Njk0NzQ2NzY5NiIsImF1ZCI6ImNsaWVudHNtIiwidWlkIjoiOE9GRm1qUWNSNktjVlpsMGZkVWdhdyIsInppZCI6IjA2MmUxNmZjNWY3ODRjMzI5ZGRlYjZjZmQ0MDU3ZjI5Iiwic2siOiIwIiwic3R5IjoxLCJ3Y2QiOiJ1czA1IiwiZXhwIjoxNzg3NDQ4NjYyLCJpYXQiOjE3ODc0NDE0NjIsImFpZCI6IndhNUV5emRlVGVPVDYyT2ROaWlPV1EiLCJjaWQiOiIifQ.DOJQygO-dRfhgC-hPJAgVeGv81CtlQiHAnEU0Qrr5VE', 'W1DzAe', 'cancelled', NULL, NULL, NULL, 'cxxvc', 1, 0, '2026-08-22 23:31:02', '2026-08-22 23:36:21');

-- --------------------------------------------------------

--
-- Table structure for table `meeting_participants`
--

CREATE TABLE `meeting_participants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `meeting_id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('interviewer','candidate','attendee') NOT NULL DEFAULT 'attendee',
  `attended` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meeting_participants`
--

INSERT INTO `meeting_participants` (`id`, `meeting_id`, `employee_id`, `name`, `email`, `role`, `attended`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'sher ali', 'shairsoftinnovations@gmail.com', 'attendee', NULL, '2026-08-22 23:31:02', '2026-08-22 23:31:02');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED NOT NULL,
  `receiver_id` bigint(20) UNSIGNED NOT NULL,
  `message` longtext NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `file_type` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_08_14_004907_tbl_address_history', 1),
(2, '2026_08_14_010215_add_doc_fields_to_tbl_address_history_table', 2),
(3, '2026_08_14_011845_create_tbl_company_documents_table', 3),
(4, '2026_08_14_085243_add_company_to_tbl_department_table', 4),
(5, '2026_08_14_162612_add_company_id_to_tbl_form_jobtitle_table', 5),
(6, '2026_08_16_082023_add_fields_to_settings_table', 6),
(7, '2026_08_16_223743_add_late_overtime_to_attendance_table', 7),
(8, '2026_08_17_000959_addearlyinminutestotblattendancetable', 8),
(9, '2026_08_18_075857_payrolls', 9),
(10, '2026_08_18_085226_addcontractedpaytopayrollstable', 10),
(11, '2026_08_18_091128_addpayslipfieldstopayrollstable', 11),
(12, '2026_08_18_112458_create_cache_table', 12),
(13, '2026_08_18_112529_create_sessions_table', 12),
(14, '2026_08_19_005139_addsharecodeidtotblpeopletable', 13),
(15, '2026_08_19_225145_addcompanyidtotblcompanydata', 14),
(16, '2026_08_20_094430_addjobtitle_idintbicompanyfromdatetable', 14),
(17, '2026_08_22_060823_meetings', 15),
(18, '2026_08_22_060846_meeting_participants', 15),
(19, '2026_08_22_142737_email_logs', 16);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) UNSIGNED NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payrolls`
--

CREATE TABLE `payrolls` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference` int(10) UNSIGNED NOT NULL,
  `idno` varchar(20) NOT NULL,
  `department` varchar(255) DEFAULT NULL,
  `ni_number` varchar(255) DEFAULT NULL,
  `tax_code` varchar(255) DEFAULT NULL,
  `period_label` varchar(255) DEFAULT NULL,
  `employee` varchar(255) NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `pay_type` enum('hourly','salaried') NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `contracted_monthly_gross` decimal(10,2) DEFAULT NULL,
  `contracted_breakdown` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`contracted_breakdown`)),
  `scheduled_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `scheduled_days` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `worked_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `regular_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `overtime_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `restday_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `unapproved_absence_days` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `absence_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `overtime_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `restday_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gross_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `taxable_pay` decimal(10,2) DEFAULT NULL,
  `non_taxable_pay` decimal(10,2) DEFAULT NULL,
  `niable_pay` decimal(10,2) DEFAULT NULL,
  `income_tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `employee_ni` decimal(10,2) NOT NULL DEFAULT 0.00,
  `employer_ni` decimal(10,2) DEFAULT NULL,
  `total_deductions` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ytd_gross` decimal(10,2) DEFAULT NULL,
  `ytd_taxable_pay` decimal(10,2) DEFAULT NULL,
  `ytd_tax` decimal(10,2) DEFAULT NULL,
  `ytd_employee_ni` decimal(10,2) DEFAULT NULL,
  `ytd_employer_ni` decimal(10,2) DEFAULT NULL,
  `ytd_niable_pay` decimal(10,2) DEFAULT NULL,
  `status` enum('Pending','Approved','Paid') NOT NULL DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('cxCR8O6hIToeiW0jvsuNtnSPoY9fmna1dHiQgG5I', 1, '78.147.246.233', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoidTNTTHh1VXVITDJUb0lxWUFoOWtBM0l0M3dEZzBlbXNDWDl4ckFNMyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDg6Imh0dHBzOi8vaHJtLmtoYXR0YWtjb25zdWx0YW5jeS5jb20vZW1wbG95ZWVzL25ldyI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE0OiJiaXJ0aGRheV9zaG93biI7YjoxO30=', 1787520616);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) UNSIGNED NOT NULL,
  `app_name` varchar(255) DEFAULT NULL,
  `app_logo` varchar(255) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `timezone` varchar(255) DEFAULT NULL,
  `clock_comment` varchar(255) DEFAULT NULL,
  `rfid` varchar(255) DEFAULT NULL,
  `gps` varchar(5) DEFAULT NULL,
  `time_format` int(11) DEFAULT NULL,
  `iprestriction` varchar(500) DEFAULT NULL,
  `opt` varchar(800) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `app_name`, `app_logo`, `currency`, `country`, `timezone`, `clock_comment`, `rfid`, `gps`, `time_format`, `iprestriction`, `opt`) VALUES
(1, 'Khattak Consultancy', 'logos/p2NsjJxIQmXekqMLuCrd5QmBQ4Dqo9dHykrPraa9.png', 'GBP', 'United Kingdom', 'Europe/London', NULL, NULL, NULL, 2, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_address_history`
--

CREATE TABLE `tbl_address_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference` int(10) UNSIGNED NOT NULL,
  `address_line` varchar(500) NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date DEFAULT NULL,
  `doc_reference` varchar(255) DEFAULT NULL,
  `doc_file` varchar(255) DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_address_history`
--

INSERT INTO `tbl_address_history` (`id`, `reference`, `address_line`, `date_from`, `date_to`, `doc_reference`, `doc_file`, `is_current`, `created_at`, `updated_at`) VALUES
(1, 1, '7 KNIGHTON AVENUE NOTTINGHAM NG7 5PN', '2019-09-01', NULL, NULL, NULL, 1, '2026-08-18 20:39:49', '2026-08-18 21:29:48'),
(2, 2, '79 GLADSTONE STREET NOTTINGHAM NG7 6GU', '2024-03-01', NULL, 'ID DRIVING LICENCE', 'address-documents/fd9757e7-f86a-4321-ad80-e00f6ccb5ac7.pdf', 1, '2026-08-18 20:56:38', '2026-08-20 06:55:25'),
(3, 2, '7 KNIGHTON AVENUE NOTTINGHAM NG7 5QD', '2023-12-18', '2024-03-01', NULL, NULL, 0, '2026-08-18 20:56:38', '2026-08-20 06:55:25'),
(4, 2, 'VILLAGE TORDHER TEHSIL LAHORE DISTRICT SWABI KPK PAKISTAN', '1994-12-01', '2023-12-18', NULL, NULL, 0, '2026-08-18 20:56:38', '2026-08-20 06:55:25'),
(5, 3, 'FLAT B, 628 RADFORD ROAD NOTTINGHAM NG7 7EX', '2025-11-29', NULL, 'ID DRIVING LICENCE', 'address-documents/7461865e-e68c-4b43-93fc-d665394e69b5.pdf', 1, '2026-08-19 19:45:53', '2026-08-21 10:32:03'),
(6, 3, '195 VERNON ROAD NOTTINGHAM NG6 0AZ', '2023-08-23', '2025-11-29', NULL, NULL, 0, '2026-08-19 19:45:53', '2026-08-21 10:32:03'),
(7, 3, 'MIRPUR AJK PAKISTAN', '1996-10-10', '2023-08-23', NULL, NULL, 0, '2026-08-19 19:45:53', '2026-08-21 10:32:03'),
(8, 4, '6 MAYLAND CLOSE NOTTINGHAM NG8 4HX', '2023-07-23', NULL, 'ID DRIVING LICENCE', 'address-documents/2a0d89bb-4ba0-4122-b09b-7aed19a4f2d2.pdf', 1, '2026-08-19 21:09:28', '2026-08-21 10:34:31'),
(9, 4, '11902 JOHANNESBURG NORTON HARARE ZIMBABWE', '1988-02-03', '2023-07-23', NULL, NULL, 0, '2026-08-19 21:09:28', '2026-08-21 10:34:31'),
(10, 11, 'FLAT 1, 578A HYDE ROAD MANCHESTER M18 7EE', '0024-01-01', NULL, 'BANK STATEMENT', 'address-documents/138ba147-2d09-44e3-93a6-e84445a584b2.pdf', 1, '2026-08-21 10:27:57', '2026-08-22 10:53:12'),
(11, 11, '42 BROADWAY PONTYPRIDD CF37 1BD', '2023-01-01', '2024-01-01', 'ID DRIVING LICENCE', 'address-documents/ebc5669c-1e82-41c5-804d-5ea67d42bd4f.pdf', 0, '2026-08-21 10:27:57', '2026-08-22 10:53:12'),
(12, 11, '85 WYEVERNE ROAD CARDIFF CF24 4BG', '2022-05-01', '2023-01-01', NULL, NULL, 0, '2026-08-21 10:27:57', '2026-08-22 10:53:12'),
(13, 11, 'FLAT 411, BLOCK A, ZARKOON HEIGHTS, G15 ISLAMABAD PAKISTAN', '2021-01-01', '2022-05-01', NULL, NULL, 0, '2026-08-21 10:27:57', '2026-08-22 10:53:12'),
(14, 12, '48 MEDINA ROAD BIRMINGHAM B11 3SA', '2025-03-26', NULL, 'BANK STATEMENT', 'address-documents/13b4b29c-85de-4c98-b142-dc88ba04e8a8.pdf', 1, '2026-08-22 10:42:12', '2026-08-22 10:46:42'),
(15, 12, 'CHANGPUR KHAWAS KOTLI, AZAD KASHMIR PAKISTAN', '2006-06-23', '2025-03-26', NULL, NULL, 0, '2026-08-22 10:42:12', '2026-08-22 10:46:42'),
(16, 11, 'FLAT 1, 578A HYDE ROAD MANCHESTER M18 7EE', '2024-01-01', NULL, NULL, NULL, 1, '2026-08-22 10:53:12', '2026-08-22 10:53:12');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_company_data`
--

CREATE TABLE `tbl_company_data` (
  `id` int(11) UNSIGNED NOT NULL,
  `reference` int(11) NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `company` varchar(255) DEFAULT '',
  `department` varchar(255) DEFAULT '0',
  `COSCertificateNo` varchar(255) DEFAULT NULL,
  `visastatus` varchar(255) DEFAULT NULL,
  `cosexpiry` date DEFAULT NULL,
  `visastart` date DEFAULT NULL,
  `visaend` date DEFAULT NULL,
  `jobposition` varchar(255) DEFAULT '',
  `jobtitle_id` int(10) UNSIGNED DEFAULT NULL,
  `jobtype` varchar(255) DEFAULT NULL,
  `companyemail` varchar(255) DEFAULT '',
  `idno` varchar(255) DEFAULT '',
  `startdate` varchar(255) DEFAULT '',
  `dateregularized` varchar(255) DEFAULT '',
  `reason` varchar(455) DEFAULT '',
  `leaveprivilege` int(11) DEFAULT NULL,
  `jobduties` longtext DEFAULT NULL,
  `workchecks` longtext DEFAULT NULL,
  `kinno` varchar(255) DEFAULT NULL,
  `kinname` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_company_data`
--

INSERT INTO `tbl_company_data` (`id`, `reference`, `company_id`, `company`, `department`, `COSCertificateNo`, `visastatus`, `cosexpiry`, `visastart`, `visaend`, `jobposition`, `jobtitle_id`, `jobtype`, `companyemail`, `idno`, `startdate`, `dateregularized`, `reason`, `leaveprivilege`, `jobduties`, `workchecks`, `kinno`, `kinname`) VALUES
(1, 1, 1, 'KHATTAK CONSULTANCY LTD', 'MARKETING', NULL, NULL, NULL, NULL, NULL, 'MARKETING MANAGER', 3, NULL, '', '806143', '2026-03-01', '2026-03-01', '', NULL, NULL, NULL, 'Shahid Khan', 'Spouse'),
(2, 2, 1, 'KHATTAK CONSULTANCY LTD', 'ADMINISTRATION', NULL, 'Dependent Spouse of a Skilled Worker', NULL, '2023-11-03', '2026-09-14', 'OFFICE MANAGER', 1, 'Office Manager', '', '633302', '2026-04-13', '2026-04-13', '', NULL, '<p>Establishing and maintaining office procedures, deals with all regulatory compliance matters. Maintain our HRM system that track attendance, annual leave, holidays and sick days. Handling all business correspondence, deals with matters relating to complaints, queries, preparing letters and responses. Processing invoices and managing office budgets, implementing and maintaining administrative systems, ordering office stocks, plan, organise induction programs and training for clients. Ensuring health and safety policies are updated. Updating financial documents and ensuring insurance policies for business are in place.</p>', NULL, 'Hoor Gulab', 'Spouse'),
(3, 3, 2, 'KHATTAK PROPERTIES GROUP LIMITED', 'BUILDING', NULL, 'Skilled Worker', NULL, '2023-08-10', '2028-07-28', 'PLASTER', NULL, '5321', '', '354803', '2026-05-01', '2026-05-01', '', NULL, NULL, NULL, '07857 432108', 'Alisha Maryam Sarfraz - Wife'),
(4, 4, 2, 'KHATTAK PROPERTIES GROUP LIMITED', 'BUILDING', NULL, 'Dependent Spouse of a Skilled Worker', NULL, '2026-02-20', '2028-03-29', 'BRICKLAYER', NULL, '5313', '', '994179', '2026-04-01', '2026-04-01', '', NULL, NULL, NULL, '0 7889 574722', 'Charity Ruseya Spouse'),
(5, 11, 2, 'KHATTAK PROPERTIES GROUP LIMITED', 'BUILDING', 'C2G9U18493S', 'Skilled Worker', '2024-07-03', '2024-04-10', '2027-04-19', 'BUILDER', NULL, '5319', '', '858744', '2026-04-01', '2026-04-01', '', NULL, NULL, NULL, '07405245456', 'Nazia Yasmin'),
(6, 12, 2, 'KHATTAK PROPERTIES GROUP LIMITED', 'ADMINISTRATION', 'C2G1R68802H', 'Skilled Worker', '2025-05-06', '2025-03-25', '2028-04-14', 'OFFICE MANAGER', NULL, NULL, '', '258434', '2026-01-04', '2026-01-04', '', NULL, NULL, NULL, '07361861408', 'Muhammad Abdullah Zia - Partner');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_company_documents`
--

CREATE TABLE `tbl_company_documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `doc_label` varchar(255) NOT NULL,
  `doc_file` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_form_company`
--

CREATE TABLE `tbl_form_company` (
  `id` int(11) UNSIGNED NOT NULL,
  `company` varchar(250) DEFAULT '',
  `licenceNo` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_form_company`
--

INSERT INTO `tbl_form_company` (`id`, `company`, `licenceNo`, `address`) VALUES
(1, 'KHATTAK CONSULTANCY LTD', NULL, 'Castle Cavendish Works, Dorking Road Nottingham NG7 5PN'),
(2, 'KHATTAK PROPERTIES GROUP LIMITED', NULL, 'Castle Cavendish Works, Dorking Road Nottingham NG7 5PN');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_form_department`
--

CREATE TABLE `tbl_form_department` (
  `id` int(11) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED DEFAULT NULL,
  `department` varchar(250) DEFAULT '',
  `company` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_form_department`
--

INSERT INTO `tbl_form_department` (`id`, `company_id`, `department`, `company`) VALUES
(1, 1, 'MARKETING', NULL),
(2, 1, 'ADMINISTRATION', NULL),
(3, 2, 'ADMINISTRATION', NULL),
(4, 2, 'BUILDING', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_form_jobtitle`
--

CREATE TABLE `tbl_form_jobtitle` (
  `id` int(11) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED DEFAULT NULL,
  `jobtitle` varchar(250) DEFAULT '',
  `dept_code` int(11) DEFAULT NULL,
  `jobduties` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_form_jobtitle`
--

INSERT INTO `tbl_form_jobtitle` (`id`, `company_id`, `jobtitle`, `dept_code`, `jobduties`) VALUES
(1, 1, 'OFFICE MANAGER', 2, NULL),
(3, 1, 'MARKETING MANAGER', 1, NULL),
(4, 2, 'PLASTER', 4, NULL),
(5, 2, 'BRICKLAYER', 4, NULL),
(6, 2, 'OFFICE MANAGER', 3, NULL),
(7, 2, 'BUILDER', 4, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_form_leavegroup`
--

CREATE TABLE `tbl_form_leavegroup` (
  `id` int(11) UNSIGNED NOT NULL,
  `leavegroup` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `leaveprivileges` varchar(255) DEFAULT NULL,
  `status` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_form_leavetype`
--

CREATE TABLE `tbl_form_leavetype` (
  `id` int(11) UNSIGNED NOT NULL,
  `leavetype` varchar(255) DEFAULT NULL,
  `limit` varchar(255) DEFAULT NULL,
  `percalendar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_people`
--

CREATE TABLE `tbl_people` (
  `id` int(6) UNSIGNED NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `mi` varchar(255) DEFAULT '',
  `lastname` varchar(255) DEFAULT NULL,
  `age` int(3) DEFAULT NULL,
  `gender` varchar(255) DEFAULT '',
  `emailaddress` varchar(255) DEFAULT '',
  `civilstatus` varchar(255) DEFAULT '',
  `height` varchar(255) DEFAULT '',
  `weight` varchar(255) DEFAULT '',
  `mobileno` varchar(255) DEFAULT '',
  `birthday` varchar(255) DEFAULT '',
  `nationalid` varchar(255) DEFAULT NULL,
  `idissuedate` date DEFAULT NULL,
  `idexpirydate` date DEFAULT NULL,
  `sharecode` varchar(255) DEFAULT NULL,
  `sharecode_expires_at` timestamp NULL DEFAULT NULL,
  `NI` varchar(255) DEFAULT NULL,
  `birthplace` varchar(255) DEFAULT '',
  `homeaddress` varchar(255) DEFAULT '',
  `employmentstatus` varchar(11) DEFAULT '',
  `employmenttype` varchar(11) DEFAULT '',
  `avatar` varchar(255) DEFAULT NULL,
  `perhourpay` varchar(255) DEFAULT NULL,
  `accountpay` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_people`
--

INSERT INTO `tbl_people` (`id`, `firstname`, `mi`, `lastname`, `age`, `gender`, `emailaddress`, `civilstatus`, `height`, `weight`, `mobileno`, `birthday`, `nationalid`, `idissuedate`, `idexpirydate`, `sharecode`, `sharecode_expires_at`, `NI`, `birthplace`, `homeaddress`, `employmentstatus`, `employmenttype`, `avatar`, `perhourpay`, `accountpay`) VALUES
(1, 'RUBY', '', 'ZAHID', 43, 'FEMALE', 'ruby_zahid3@yahoo.com', 'MARRIED', NULL, NULL, '07424313223', '1983-07-12', '142262737', '2023-08-14', '2033-08-14', NULL, NULL, 'SX941896A', 'SWABI', '', 'Active', 'Regular', NULL, NULL, NULL),
(2, 'MOHSIN', '', 'KHALID', 32, 'MALE', 'mkkhattak193@gmail.com', 'MARRIED', NULL, NULL, '0 7459 263911', '1994-01-12', 'GH4134803', '2023-08-10', '2028-08-08', 'W78 36X 7SB', '2026-11-17 00:00:00', 'TJ850766C', 'SWABI', '', 'Active', 'Regular', 'avatars/16c7f771-20f2-4199-b2c3-16a161bed125.png', NULL, NULL),
(3, 'FARHAN', '', 'ALI', 29, 'MALE', 'farhanalinoorhussain@gmail.com', 'MARRIED', NULL, NULL, '07460 025559', '1996-10-10', 'LX1822082', '2023-05-12', '2033-05-10', 'W5M W6X 7TN', '2026-11-17 00:00:00', 'TJ311047D', 'MIRPUR, PAKISTAN', '', 'Active', 'Part-Time', NULL, '12.71', '1101.58'),
(4, 'EDWARD', 'FINCH', 'MATURURE', 38, 'MALE', 'efinchmaturure@gmail.com', 'MARRIED', NULL, NULL, '07480 407677', '1988-03-02', 'BE471557', '2025-03-24', '2035-03-23', 'WCD 67X 7LH', '2026-11-17 00:00:00', 'TJ111360A', 'BULAWAYO', '', 'Active', NULL, 'avatars/bcb8393f-f9de-4273-8b4e-f5e1e5cb525e.png', NULL, NULL),
(11, 'ADNAN', '', 'KHAN', 56, 'MALE', 'adnankhanyousafzai786@gmail.com', 'MARRIED', NULL, NULL, '07449223571', '1970-01-01', 'AW1401395', '2023-11-30', '2033-11-29', 'WXZ M9X 7NY', '2026-11-18 00:00:00', 'SZ915447D', 'SWABI, PAKISTAN', '', 'Active', 'Part-Time', NULL, '12.71', NULL),
(12, 'AYESHA', '', 'SANA', 56, 'FEMALE', 'ayeshasana22009@gmail.com', 'SINGLE', NULL, NULL, '07405296094', '1970-01-01', 'BE5795912', '2024-07-10', '3034-07-10', 'WCA 99X 7GC', '2026-11-18 00:00:00', 'RZ 71 18 41B', 'KOTLI, AJK PAKISTAN', '', 'Active', 'Part-Time', NULL, '14.00', '1204');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_people_attendance`
--

CREATE TABLE `tbl_people_attendance` (
  `id` int(11) UNSIGNED NOT NULL,
  `reference` int(11) DEFAULT NULL,
  `idno` varchar(11) DEFAULT '',
  `date` date DEFAULT NULL,
  `employee` varchar(255) DEFAULT '',
  `timein` varchar(255) DEFAULT NULL,
  `timeout` varchar(255) DEFAULT NULL,
  `totalhours` varchar(255) DEFAULT '',
  `status_timein` varchar(255) DEFAULT '',
  `late_minutes` int(10) UNSIGNED DEFAULT 0,
  `early_in_minutes` int(11) NOT NULL DEFAULT 0,
  `latitude_in` decimal(10,7) DEFAULT NULL,
  `longitude_in` decimal(10,7) DEFAULT NULL,
  `status_timeout` varchar(255) DEFAULT '',
  `early_minutes` int(10) UNSIGNED DEFAULT 0,
  `overtime_minutes` int(10) UNSIGNED DEFAULT 0,
  `latitude_out` decimal(10,7) DEFAULT NULL,
  `longitude_out` decimal(10,7) DEFAULT NULL,
  `reason` varchar(255) DEFAULT '',
  `comment` varchar(255) DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_people_leaves`
--

CREATE TABLE `tbl_people_leaves` (
  `id` int(11) UNSIGNED NOT NULL,
  `reference` int(11) DEFAULT NULL,
  `idno` varchar(11) DEFAULT NULL,
  `employee` varchar(255) DEFAULT '',
  `typeid` int(11) DEFAULT NULL,
  `type` varchar(255) DEFAULT '',
  `leavefrom` date DEFAULT NULL,
  `leaveto` date DEFAULT NULL,
  `returndate` date DEFAULT NULL,
  `reason` varchar(255) DEFAULT '',
  `status` varchar(255) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `archived` int(11) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_people_schedules`
--

CREATE TABLE `tbl_people_schedules` (
  `id` int(11) UNSIGNED NOT NULL,
  `reference` int(11) DEFAULT NULL,
  `idno` varchar(11) DEFAULT NULL,
  `employee` varchar(255) DEFAULT NULL,
  `intime` text DEFAULT NULL,
  `outime` text DEFAULT NULL,
  `datefrom` date DEFAULT NULL,
  `dateto` date DEFAULT NULL,
  `hours` varchar(11) DEFAULT NULL,
  `restday` varchar(255) DEFAULT NULL,
  `archive` varchar(255) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_report_views`
--

CREATE TABLE `tbl_report_views` (
  `id` int(11) UNSIGNED NOT NULL,
  `report_id` int(11) DEFAULT NULL,
  `last_viewed` varchar(255) DEFAULT NULL,
  `title` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference` int(11) DEFAULT NULL,
  `idno` varchar(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT '',
  `email` varchar(255) DEFAULT '',
  `role_id` int(11) DEFAULT NULL,
  `acc_type` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  `last_seen` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `reference`, `idno`, `name`, `email`, `role_id`, `acc_type`, `status`, `password`, `remember_token`, `created_at`, `updated_at`, `last_seen`) VALUES
(1, 4, '001122', 'MAJID KHATTACK', 'admin@khattakconsultancy.com', 1, 2, 1, '$2y$12$LXSCsKDgb5L5HqzR3OLG4.IMwsR52dvnlcAhTB8yyTX01MsYigsCe', '8aSfkVgVvRTsRexT7umUfbXCcCaPXc2hrvxpLweJuZzzGqqq0wbkrvwAbyJ3', '2018-10-31 12:10:04', '2026-08-13 21:28:12', '2026-08-23 21:27:36');

-- --------------------------------------------------------

--
-- Table structure for table `users_permissions`
--

CREATE TABLE `users_permissions` (
  `id` int(11) UNSIGNED NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `perm_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users_permissions`
--

INSERT INTO `users_permissions` (`id`, `role_id`, `perm_id`) VALUES
(2049, 2, 1),
(2050, 2, 5),
(2051, 2, 52),
(2052, 2, 53),
(2053, 2, 6),
(2054, 2, 61),
(2109, 1, 1),
(2110, 1, 2),
(2111, 1, 22),
(2112, 1, 21),
(2113, 1, 23),
(2114, 1, 24),
(2115, 1, 25),
(2116, 1, 3),
(2117, 1, 31),
(2118, 1, 32),
(2119, 1, 4),
(2120, 1, 41),
(2121, 1, 42),
(2122, 1, 43),
(2123, 1, 44),
(2124, 1, 5),
(2125, 1, 52),
(2126, 1, 53),
(2127, 1, 9),
(2128, 1, 91),
(2129, 1, 16),
(2130, 1, 161),
(2131, 1, 162),
(2132, 1, 163),
(2133, 1, 164),
(2134, 1, 17),
(2135, 1, 171),
(2136, 1, 172),
(2137, 1, 173),
(2138, 1, 7),
(2139, 1, 8),
(2140, 1, 81),
(2141, 1, 82),
(2142, 1, 83),
(2143, 1, 10),
(2144, 1, 101),
(2145, 1, 102),
(2146, 1, 103),
(2147, 1, 104),
(2148, 1, 11),
(2149, 1, 111),
(2150, 1, 112),
(2151, 1, 113),
(2152, 1, 12),
(2153, 1, 121),
(2154, 1, 122),
(2155, 1, 123),
(2156, 1, 13),
(2157, 1, 131),
(2158, 1, 132),
(2159, 1, 133),
(2160, 1, 6),
(2161, 1, 61),
(2162, 1, 14),
(2163, 1, 141),
(2164, 1, 142),
(2165, 1, 15),
(2166, 1, 151),
(2167, 1, 152),
(2168, 1, 153);

-- --------------------------------------------------------

--
-- Table structure for table `users_roles`
--

CREATE TABLE `users_roles` (
  `id` int(11) UNSIGNED NOT NULL,
  `role_name` varchar(255) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users_roles`
--

INSERT INTO `users_roles` (`id`, `role_name`, `state`) VALUES
(1, 'MANAGER', 'Active'),
(2, 'EMPLOYEE', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `weekly_shifts`
--

CREATE TABLE `weekly_shifts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `schedual_id` int(11) NOT NULL,
  `day` varchar(20) NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `is_off` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `active` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `daily_salaries`
--
ALTER TABLE `daily_salaries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `document_types`
--
ALTER TABLE `document_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_logs_reference_index` (`reference`),
  ADD KEY `email_logs_type_milestone_index` (`type`,`milestone`);

--
-- Indexes for table `employee_documents`
--
ALTER TABLE `employee_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_employee_documents_document_type` (`document_type_id`);

--
-- Indexes for table `meetings`
--
ALTER TABLE `meetings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `meetings_zoom_meeting_id_unique` (`zoom_meeting_id`),
  ADD KEY `meetings_start_time_index` (`start_time`),
  ADD KEY `meetings_status_index` (`status`);

--
-- Indexes for table `meeting_participants`
--
ALTER TABLE `meeting_participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meeting_participants_meeting_id_foreign` (`meeting_id`),
  ADD KEY `meeting_participants_email_index` (`email`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conversation` (`sender_id`,`receiver_id`,`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payroll_period_unique` (`reference`,`period_start`,`period_end`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `tbl_address_history`
--
ALTER TABLE `tbl_address_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tbl_address_history_reference_index` (`reference`),
  ADD KEY `tbl_address_history_reference_date_from_index` (`reference`,`date_from`);

--
-- Indexes for table `tbl_company_data`
--
ALTER TABLE `tbl_company_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tbl_company_data_company_id_index` (`company_id`),
  ADD KEY `tbl_company_data_jobtitle_id_index` (`jobtitle_id`);

--
-- Indexes for table `tbl_company_documents`
--
ALTER TABLE `tbl_company_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tbl_company_documents_company_id_index` (`company_id`);

--
-- Indexes for table `tbl_form_company`
--
ALTER TABLE `tbl_form_company`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_form_department`
--
ALTER TABLE `tbl_form_department`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_form_jobtitle`
--
ALTER TABLE `tbl_form_jobtitle`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_form_leavegroup`
--
ALTER TABLE `tbl_form_leavegroup`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_form_leavetype`
--
ALTER TABLE `tbl_form_leavetype`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_people`
--
ALTER TABLE `tbl_people`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tbl_people_sharecode_expires_at_index` (`sharecode_expires_at`);

--
-- Indexes for table `tbl_people_attendance`
--
ALTER TABLE `tbl_people_attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_people_leaves`
--
ALTER TABLE `tbl_people_leaves`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_people_schedules`
--
ALTER TABLE `tbl_people_schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_report_views`
--
ALTER TABLE `tbl_report_views`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users_permissions`
--
ALTER TABLE `users_permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users_roles`
--
ALTER TABLE `users_roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `weekly_shifts`
--
ALTER TABLE `weekly_shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`schedual_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `daily_salaries`
--
ALTER TABLE `daily_salaries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_types`
--
ALTER TABLE `document_types`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_documents`
--
ALTER TABLE `employee_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `meetings`
--
ALTER TABLE `meetings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `meeting_participants`
--
ALTER TABLE `meeting_participants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payrolls`
--
ALTER TABLE `payrolls`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_address_history`
--
ALTER TABLE `tbl_address_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tbl_company_data`
--
ALTER TABLE `tbl_company_data`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_company_documents`
--
ALTER TABLE `tbl_company_documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_form_company`
--
ALTER TABLE `tbl_form_company`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_form_department`
--
ALTER TABLE `tbl_form_department`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_form_jobtitle`
--
ALTER TABLE `tbl_form_jobtitle`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_form_leavegroup`
--
ALTER TABLE `tbl_form_leavegroup`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_form_leavetype`
--
ALTER TABLE `tbl_form_leavetype`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_people`
--
ALTER TABLE `tbl_people`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbl_people_attendance`
--
ALTER TABLE `tbl_people_attendance`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_people_leaves`
--
ALTER TABLE `tbl_people_leaves`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_people_schedules`
--
ALTER TABLE `tbl_people_schedules`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_report_views`
--
ALTER TABLE `tbl_report_views`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users_permissions`
--
ALTER TABLE `users_permissions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2169;

--
-- AUTO_INCREMENT for table `users_roles`
--
ALTER TABLE `users_roles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `weekly_shifts`
--
ALTER TABLE `weekly_shifts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employee_documents`
--
ALTER TABLE `employee_documents`
  ADD CONSTRAINT `fk_employee_documents_document_type` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `meeting_participants`
--
ALTER TABLE `meeting_participants`
  ADD CONSTRAINT `meeting_participants_meeting_id_foreign` FOREIGN KEY (`meeting_id`) REFERENCES `meetings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_company_data`
--
ALTER TABLE `tbl_company_data`
  ADD CONSTRAINT `tbl_company_data_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `tbl_form_company` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tbl_company_data_jobtitle_id_foreign` FOREIGN KEY (`jobtitle_id`) REFERENCES `tbl_form_jobtitle` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
