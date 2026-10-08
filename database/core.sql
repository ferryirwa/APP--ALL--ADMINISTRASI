-- ============================================================
-- File    : core.sql
-- Fungsi  : Tabel global untuk semua modul admin
-- Author  : Tim Aplikasi Administrasi
-- Tanggal : 2024
-- ============================================================

CREATE DATABASE IF NOT EXISTS `aplikasi_administrasi`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `aplikasi_administrasi`;

-- ------------------------------------------------------------
-- Tabel: admins_jenis (daftar jenis admin)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins_jenis` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`        VARCHAR(50)  NOT NULL,
  `nama`        VARCHAR(100) NOT NULL,
  `deskripsi`   VARCHAR(255) DEFAULT NULL,
  `icon`        VARCHAR(50)  DEFAULT 'fa-solid fa-user-tie',
  `warna`       VARCHAR(20)  DEFAULT '#0d6efd',
  `aktif`       TINYINT(1)   NOT NULL DEFAULT 1,
  `urutan`      INT          NOT NULL DEFAULT 0,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Tabel: admins_users (akun user per-jenis admin)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins_users` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `jenis_admin_id` INT UNSIGNED NOT NULL,
  `username`       VARCHAR(50)  NOT NULL,
  `email`          VARCHAR(100) NOT NULL,
  `password`       VARCHAR(255) NOT NULL,
  `nama_lengkap`   VARCHAR(100) NOT NULL,
  `is_active`      TINYINT(1)   NOT NULL DEFAULT 1,
  `last_login`     DATETIME     DEFAULT NULL,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_jenis_username` (`jenis_admin_id`, `username`),
  UNIQUE KEY `uk_jenis_email`    (`jenis_admin_id`, `email`),
  KEY `fk_jenis` (`jenis_admin_id`),
  CONSTRAINT `fk_users_jenis` FOREIGN KEY (`jenis_admin_id`)
    REFERENCES `admins_jenis` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Tabel: admins_log (audit log per jenis admin)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins_log` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `jenis_admin_id` INT UNSIGNED DEFAULT NULL,
  `user_id`        INT UNSIGNED DEFAULT NULL,
  `username`       VARCHAR(50)  DEFAULT NULL,
  `aksi`           VARCHAR(50)  NOT NULL,
  `keterangan`     VARCHAR(255) DEFAULT NULL,
  `ip_address`     VARCHAR(45)  DEFAULT NULL,
  `user_agent`     VARCHAR(255) DEFAULT NULL,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_jenis`   (`jenis_admin_id`),
  KEY `idx_user`    (`user_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Tabel: admins_sessions (opsional, keamanan ekstra)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins_sessions` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`        INT UNSIGNED NOT NULL,
  `jenis_admin_id` INT UNSIGNED NOT NULL,
  `token`          VARCHAR(64)  NOT NULL,
  `ip_address`     VARCHAR(45)  DEFAULT NULL,
  `expires_at`     DATETIME     NOT NULL,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_token` (`token`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;