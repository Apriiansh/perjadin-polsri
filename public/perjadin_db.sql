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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tariffs` (`id`, `province`, `city`, `tingkat_biaya`, `uang_harian`, `uang_representasi`, `penginapan`, `jenis_penginapan`, `tahun_berlaku`, `is_active`, `created_at`, `updated_at`) VALUES
(2,	'DKI JAKARTA',	'KOTA JAKARTA SELATAN',	'A',	500000.00,	200000.00,	800000.00,	'Hotel Bintang 4',	2026,	1,	'2026-03-08 04:41:51',	'2026-03-08 04:41:51'),
(3,	'DKI JAKARTA',	'KOTA JAKARTA SELATAN',	'B',	120000.00,	100000.00,	800000.00,	'Hotel Bintang 4',	2026,	1,	'2026-03-09 04:55:36',	'2026-03-09 04:55:36'),
(4,	'ACEH',	'KABUPATEN SIMEULUE',	'A',	1200000.00,	1000000.00,	1500000.00,	'Hotel',	2026,	1,	'2026-03-13 17:55:34',	'2026-03-13 17:55:34'),
(5,	'ACEH',	'KABUPATEN SIMEULUE',	'B',	1000000.00,	800000.00,	800000.00,	'Hotel',	2026,	1,	'2026-03-13 17:56:01',	'2026-03-13 17:56:01');

DROP TABLE IF EXISTS `travel_completeness`;
CREATE TABLE `travel_completeness` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `travel_request_id` bigint(20) unsigned NOT NULL,
  `member_id` int(11) unsigned DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `travel_completeness` (`id`, `travel_request_id`, `member_id`, `item_name`, `payment_method`, `remark`, `document_path`, `original_name`, `file_size`, `uploaded_by`, `uploaded_at`, `status`, `verified_by`, `verified_at`, `verification_note`, `created_at`, `updated_at`) VALUES
(15,	7,	NULL,	'Laporan Perjalanan',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'uploaded',	NULL,	NULL,	NULL,	'2026-03-14 15:20:57',	'2026-03-15 06:22:27'),
(16,	7,	NULL,	'Dokumentasi Kegiatan',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'uploaded',	NULL,	NULL,	NULL,	'2026-03-14 15:20:57',	'2026-03-15 06:22:27'),
(17,	7,	NULL,	'Tiket & Boarding Pass',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'uploaded',	NULL,	NULL,	NULL,	'2026-03-14 15:20:57',	'2026-03-15 06:22:27'),
(18,	7,	NULL,	'Daftar Pengeluaran Riil',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'uploaded',	NULL,	NULL,	NULL,	'2026-03-14 15:20:57',	'2026-03-15 06:22:27');

DROP TABLE IF EXISTS `travel_completeness_files`;
CREATE TABLE `travel_completeness_files` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `completeness_id` bigint(20) unsigned NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `uploaded_by` int(11) unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `travel_completeness_files_completeness_id_foreign` (`completeness_id`),
  CONSTRAINT `travel_completeness_files_completeness_id_foreign` FOREIGN KEY (`completeness_id`) REFERENCES `travel_completeness` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `travel_completeness_files` (`id`, `completeness_id`, `file_path`, `original_name`, `file_size`, `uploaded_by`, `created_at`) VALUES
(36,	15,	'completeness/7/laporan-perjalanan-11-1-4b4de649.pdf',	'Laporan Perjalanan - 1.pdf',	12604,	13,	'2026-03-15 06:22:27'),
(37,	16,	'completeness/7/dokumentasi-kegiatan-11-1-743ee9b8.pdf',	'Dokumentasi Kegiatan - 1.pdf',	12604,	13,	'2026-03-15 06:22:27'),
(38,	17,	'completeness/7/tiket-boarding-pass-11-1-3fd7622b.pdf',	'Tiket & Boarding Pass - 1.pdf',	12604,	13,	'2026-03-15 06:22:27'),
(39,	18,	'completeness/7/daftar-pengeluaran-riil-11-1-1ab3f226.pdf',	'Daftar Pengeluaran Riil - 1.pdf',	12604,	13,	'2026-03-15 06:22:27'),
(40,	15,	'completeness/7/laporan-perjalanan-user13-mbr11-2-1756e09a.pdf',	'Laporan Perjalanan - 2.pdf',	12604,	13,	'2026-03-15 06:35:42'),
(41,	17,	'completeness/7/tiket-boarding-pass-user13-mbr11-2-dd4201f0.png',	'Tiket & Boarding Pass - 2.png',	112299,	13,	'2026-03-15 07:07:53');

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
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `travel_expenses` (`id`, `travel_member_id`, `tariff_id`, `uang_harian`, `uang_representasi`, `tiket`, `penginapan`, `transport_darat`, `transport_lokal`, `total_biaya`, `created_at`, `updated_at`) VALUES
(30,	11,	NULL,	12000000.00,	5000000.00,	1200000.00,	0.00,	0.00,	300000.00,	18500000.00,	'2026-03-14 15:19:02',	'2026-03-14 15:20:57'),
(31,	12,	NULL,	10000000.00,	4000000.00,	1200000.00,	0.00,	0.00,	40000.00,	15240000.00,	'2026-03-14 15:19:02',	'2026-03-14 15:20:57');

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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `travel_expense_items` (`id`, `travel_member_id`, `category`, `item_name`, `amount`, `created_at`, `updated_at`) VALUES
(19,	11,	'tiket',	'Tiket Pesawat',	1200000.00,	'2026-03-14 15:20:57',	'2026-03-14 15:20:57'),
(20,	11,	'transport_lokal',	'Angkot',	300000.00,	'2026-03-14 15:20:57',	'2026-03-14 15:20:57'),
(21,	12,	'tiket',	'Tiket Pesawait',	1200000.00,	'2026-03-14 15:20:57',	'2026-03-14 15:20:57'),
(22,	12,	'transport_lokal',	'Bentor',	40000.00,	'2026-03-14 15:20:57',	'2026-03-14 15:20:57');

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
  `report_narrative` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `travel_members_travel_request_id_foreign` (`travel_request_id`),
  KEY `travel_members_employee_id_foreign` (`employee_id`),
  CONSTRAINT `travel_members_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `travel_members_travel_request_id_foreign` FOREIGN KEY (`travel_request_id`) REFERENCES `travel_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `travel_members` (`id`, `travel_request_id`, `employee_id`, `kode_golongan`, `nama_golongan`, `no_sppd`, `tgl_sppd`, `keterangan`, `report_narrative`, `created_at`, `updated_at`) VALUES
(11,	7,	21,	'IV/d',	'Pembina Utama Madya',	NULL,	NULL,	NULL,	'Telak dilaksanakan anu anuan di tempatnya si anu',	'2026-03-14 15:19:02',	'2026-03-15 07:07:53'),
(12,	7,	50,	'IV/c',	'Pembina Utama Muda',	NULL,	NULL,	NULL,	NULL,	'2026-03-14 15:19:02',	'2026-03-14 15:20:57');

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
  `ppk_id` bigint(20) unsigned DEFAULT NULL,
  `kpa_id` bigint(20) unsigned DEFAULT NULL,
  `bpp_id` bigint(20) unsigned DEFAULT NULL,
  `bendahara_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_surat_tugas` (`no_surat_tugas`),
  KEY `travel_requests_ppk_id_foreign` (`ppk_id`),
  KEY `travel_requests_kpa_id_foreign` (`kpa_id`),
  KEY `travel_requests_bendahara_id_foreign` (`bendahara_id`),
  KEY `travel_requests_bpp_id_foreign` (`bpp_id`),
  CONSTRAINT `travel_requests_bendahara_id_foreign` FOREIGN KEY (`bendahara_id`) REFERENCES `signatories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `travel_requests_bpp_id_foreign` FOREIGN KEY (`bpp_id`) REFERENCES `signatories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `travel_requests_kpa_id_foreign` FOREIGN KEY (`kpa_id`) REFERENCES `signatories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `travel_requests_ppk_id_foreign` FOREIGN KEY (`ppk_id`) REFERENCES `signatories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `travel_requests` (`id`, `no_surat_tugas`, `tgl_surat_tugas`, `nomor_surat_rujukan`, `tgl_surat_rujukan`, `instansi_pengirim_rujukan`, `perihal_surat_rujukan`, `mak`, `transportation_type`, `destination_province`, `destination_city`, `lokasi`, `departure_place`, `departure_date`, `return_date`, `duration_days`, `total_budget`, `lampiran_path`, `lampiran_original_name`, `status`, `created_by`, `created_at`, `updated_at`, `budget_burden_by`, `tahun_anggaran`, `ppk_id`, `kpa_id`, `bpp_id`, `bendahara_id`) VALUES
(7,	'12490-81123/123123/2026',	'2026-03-16',	'123414/12412/2025',	'2026-03-05',	'Kementrian HAM',	'Pigai mencekam',	'',	NULL,	'KEPULAUAN RIAU',	'KABUPATEN NATUNA',	'Hotel b5 di Riau',	'Palembang',	'2026-03-17',	'2026-03-16',	2,	33740000.00,	'travel/1773501542_aaa64e8bac2398ee854d.pdf',	'test-2-1.pdf',	'active',	1,	'2026-03-14 15:19:02',	'2026-03-14 15:20:57',	'Anggaran',	'2026',	3,	2,	4,	1);

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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `username`, `status`, `status_message`, `active`, `last_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,	'superadmin',	NULL,	NULL,	1,	'2026-03-15 07:03:52',	'2026-03-07 04:43:44',	'2026-03-07 04:45:45',	NULL),
(2,	'admin',	NULL,	NULL,	1,	'2026-03-07 17:15:19',	'2026-03-07 04:43:44',	'2026-03-07 04:45:46',	NULL),
(4,	'verificator',	NULL,	NULL,	1,	'2026-03-15 07:16:53',	'2026-03-07 04:45:46',	'2026-03-07 04:45:46',	NULL),
(5,	'lecturer',	NULL,	NULL,	1,	'2026-03-13 09:01:25',	'2026-03-07 04:45:46',	'2026-03-07 04:45:46',	NULL),
(11,	'abdul_tekkim1057',	NULL,	NULL,	1,	NULL,	'2026-03-07 17:14:09',	'2026-03-07 17:14:09',	NULL),
(12,	'ade_maninf2032',	NULL,	NULL,	1,	'2026-03-13 17:45:58',	'2026-03-13 09:01:51',	'2026-03-13 09:01:51',	NULL),
(13,	'asnaini_tekkom2001',	NULL,	NULL,	1,	'2026-03-15 07:14:55',	'2026-03-15 06:04:43',	'2026-03-15 06:04:43',	NULL);

-- 2026-03-15 07:17:38
