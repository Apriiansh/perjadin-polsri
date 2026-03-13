-- Adminer 4.8.1 MySQL 10.11.16-MariaDB dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `api_employee_id` varchar(100) DEFAULT NULL,
  `nik` varchar(30) DEFAULT NULL,
  `nip` varchar(30) NOT NULL,
  `nuptk` varchar(30) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `pangkat_golongan` varchar(100) DEFAULT NULL,
  `jabatan` varchar(150) DEFAULT NULL,
  `jafun` varchar(150) DEFAULT NULL,
  `rekening_bank` varchar(100) DEFAULT NULL,
  `id_jurusan` varchar(100) DEFAULT NULL,
  `nama_jurusan` varchar(150) DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `synced_at` datetime DEFAULT NULL,
  `api_created_at` datetime DEFAULT NULL,
  `api_updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nip` (`nip`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `api_employee_id` (`api_employee_id`),
  UNIQUE KEY `employees_nik_unique` (`nik`),
  CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=300 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `class` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(31) NOT NULL DEFAULT 'string',
  `context` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `signatories`;
CREATE TABLE `signatories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jabatan` varchar(150) NOT NULL,
  `employee_id` bigint(20) unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `signatories_employee_id_foreign` (`employee_id`),
  CONSTRAINT `signatories_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `signatories` (`id`, `jabatan`, `employee_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1,	'Bendahara Pengeluaran',	170,	1,	'2026-03-08 05:04:46',	'2026-03-11 15:34:07'),
(2,	'Kuasa Pengguna Anggaran (KPA)',	8,	1,	'2026-03-08 05:14:27',	'2026-03-11 15:33:51'),
(3,	'Pejabat Pembuat Komitmen (PPK)',	217,	1,	'2026-03-08 06:35:28',	'2026-03-11 15:32:59'),
(4,	'Bendahara Pengeluaran Pembantu (BPP)',	5,	1,	'2026-03-11 15:34:16',	'2026-03-11 15:34:16');

DROP TABLE IF EXISTS `tariffs`;
CREATE TABLE `tariffs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `province` varchar(100) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `tingkat_biaya` enum('A','B','C','D') NOT NULL,
  `uang_harian` decimal(15,2) NOT NULL DEFAULT 0.00,
  `uang_representasi` decimal(15,2) NOT NULL DEFAULT 0.00,
  `penginapan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `jenis_penginapan` varchar(100) DEFAULT 'Standar Hotel',
  `tahun_berlaku` int(4) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tariffs_unique_rate` (`province`,`city`,`tingkat_biaya`,`jenis_penginapan`,`tahun_berlaku`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `travel_completeness`;
CREATE TABLE `travel_completeness` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `travel_request_id` bigint(20) unsigned NOT NULL,
  `item_name` varchar(255) NOT NULL COMMENT 'e.g., Tiket Pesawat PP, Nota Hotel',
  `payment_method` enum('reimbursement','vendor','non_reimbursement') DEFAULT NULL,
  `remark` text DEFAULT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `uploaded_by` bigint(20) unsigned DEFAULT NULL,
  `uploaded_at` datetime DEFAULT NULL,
  `status` enum('pending','uploaded','verified','rejected') NOT NULL DEFAULT 'pending',
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `verification_note` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `travel_completeness_travel_request_id_foreign` (`travel_request_id`),
  CONSTRAINT `travel_completeness_travel_request_id_foreign` FOREIGN KEY (`travel_request_id`) REFERENCES `travel_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `travel_expenses`;
CREATE TABLE `travel_expenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `travel_member_id` bigint(20) unsigned NOT NULL,
  `tariff_id` bigint(20) unsigned DEFAULT NULL,
  `uang_harian` decimal(15,2) NOT NULL DEFAULT 0.00,
  `uang_representasi` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tiket` decimal(15,2) DEFAULT 0.00,
  `penginapan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `transport_darat` decimal(15,2) NOT NULL DEFAULT 0.00,
  `transport_lokal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_biaya` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `travel_expenses_tariff_id_foreign` (`tariff_id`),
  KEY `travel_expenses_travel_member_id_foreign` (`travel_member_id`),
  CONSTRAINT `travel_expenses_tariff_id_foreign` FOREIGN KEY (`tariff_id`) REFERENCES `tariffs` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `travel_expenses_travel_member_id_foreign` FOREIGN KEY (`travel_member_id`) REFERENCES `travel_members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `travel_expense_items`;
CREATE TABLE `travel_expense_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `travel_member_id` bigint(20) unsigned NOT NULL,
  `category` enum('tiket','penginapan','transport_darat','transport_lokal','lain-lain') NOT NULL DEFAULT 'tiket',
  `item_name` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `travel_expense_items_travel_member_id_foreign` (`travel_member_id`),
  CONSTRAINT `travel_expense_items_travel_member_id_foreign` FOREIGN KEY (`travel_member_id`) REFERENCES `travel_members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `travel_members`;
CREATE TABLE `travel_members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `travel_request_id` bigint(20) unsigned NOT NULL,
  `employee_id` bigint(20) unsigned NOT NULL,
  `kode_golongan` varchar(100) DEFAULT NULL,
  `nama_golongan` varchar(150) DEFAULT NULL,
  `no_sppd` varchar(100) DEFAULT NULL,
  `tgl_sppd` date DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `travel_members_travel_request_id_foreign` (`travel_request_id`),
  KEY `travel_members_employee_id_foreign` (`employee_id`),
  CONSTRAINT `travel_members_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `travel_members_travel_request_id_foreign` FOREIGN KEY (`travel_request_id`) REFERENCES `travel_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `travel_requests`;
CREATE TABLE `travel_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_surat_tugas` varchar(100) NOT NULL,
  `tgl_surat_tugas` date NOT NULL,
  `nomor_surat_rujukan` varchar(100) DEFAULT NULL,
  `tgl_surat_rujukan` date DEFAULT NULL,
  `instansi_pengirim_rujukan` varchar(200) DEFAULT NULL,
  `perihal_surat_rujukan` text DEFAULT NULL,
  `mak` varchar(100) DEFAULT NULL,
  `transportation_type` enum('udara','darat','laut') DEFAULT NULL,
  `destination_province` varchar(100) NOT NULL,
  `destination_city` varchar(100) NOT NULL,
  `lokasi` varchar(255) DEFAULT NULL,
  `departure_place` varchar(255) DEFAULT NULL,
  `departure_date` date NOT NULL,
  `return_date` date NOT NULL,
  `duration_days` int(11) NOT NULL DEFAULT 1,
  `total_budget` decimal(15,2) NOT NULL DEFAULT 0.00,
  `lampiran_path` varchar(255) DEFAULT NULL,
  `lampiran_original_name` varchar(255) DEFAULT NULL,
  `status` enum('draft','active','completed','cancelled') NOT NULL DEFAULT 'draft',
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `budget_burden_by` varchar(100) NOT NULL,
  `tahun_anggaran` year(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_surat_tugas` (`no_surat_tugas`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `status_message` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `last_active` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 2026-03-13 08:24:15
