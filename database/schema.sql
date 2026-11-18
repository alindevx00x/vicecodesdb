-- ============================================================================
-- GTA 6 Cheat Codes (ViceCodes) - MySQL 8.0+ Database Schema
-- Compatible with MAMP & cPanel phpMyAdmin (InnoDB, utf8mb4_unicode_ci)
-- Select your target database in phpMyAdmin before importing.
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Optional for local CLI import only (uncomment if creating DB manually):
-- CREATE DATABASE IF NOT EXISTS `gta6cheatcodes` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `gta6cheatcodes`;

-- ----------------------------------------------------------------------------
-- 1. Users (Admin Accounts)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(191) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `role` ENUM('admin', 'editor') NOT NULL DEFAULT 'admin',
  `bio` TEXT NULL,
  `last_login` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. Categories (Cheats & Blog)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(140) NOT NULL,
  `type` ENUM('cheat', 'blog') NOT NULL DEFAULT 'cheat',
  `icon` VARCHAR(60) NULL DEFAULT 'bolt',
  `description` TEXT NULL,
  `meta_title` VARCHAR(191) NULL,
  `meta_description` VARCHAR(320) NULL,
  `parent_id` INT UNSIGNED NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_slug_type` (`slug`, `type`),
  KEY `idx_categories_type_sort` (`type`, `sort_order`),
  CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. Cheats
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cheats` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(191) NOT NULL,
  `slug` VARCHAR(191) NOT NULL,
  `short_description` VARCHAR(500) NOT NULL,
  `content` LONGTEXT NULL,
  `effects` TEXT NULL,
  `verification_notes` TEXT NULL,
  `category_id` INT UNSIGNED NULL,
  `status` ENUM('verified', 'unconfirmed', 'debunked', 'coming_soon') NOT NULL DEFAULT 'coming_soon',
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `last_verified_at` DATETIME NULL,
  `game_patch` VARCHAR(32) NULL DEFAULT 'v1.00',
  `votes_working` INT UNSIGNED NOT NULL DEFAULT 0,
  `votes_broken` INT UNSIGNED NOT NULL DEFAULT 0,
  `needs_retest` TINYINT(1) NOT NULL DEFAULT 0,
  `meta_title` VARCHAR(191) NULL,
  `meta_description` VARCHAR(320) NULL,
  `focus_keyword` VARCHAR(120) NULL,
  `og_image` VARCHAR(255) NULL,
  `preview_image` VARCHAR(255) NULL,
  `preview_image_alt` VARCHAR(255) NULL,
  `faqs_json` JSON NULL,
  `changelog_json` JSON NULL,
  `views` INT UNSIGNED NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `published_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cheats_slug` (`slug`),
  KEY `idx_cheats_status` (`status`),
  KEY `idx_cheats_category` (`category_id`),
  KEY `idx_cheats_featured_sort` (`is_featured`, `sort_order`),
  KEY `idx_cheats_retest` (`needs_retest`),
  KEY `idx_cheats_published` (`published_at`),
  KEY `idx_cheats_deleted` (`deleted_at`),
  FULLTEXT KEY `ft_cheats` (`title`, `short_description`, `content`, `effects`),
  CONSTRAINT `fk_cheats_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. Cheat Platforms (PS5, Xbox Series X|S, PC)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cheat_platforms` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cheat_id` INT UNSIGNED NOT NULL,
  `platform` ENUM('ps5', 'xbox', 'pc') NOT NULL,
  `code_sequence` VARCHAR(255) NOT NULL DEFAULT '',
  `cell_number` VARCHAR(60) NULL,
  `notes` VARCHAR(500) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cheat_platform` (`cheat_id`, `platform`),
  KEY `idx_cheat_platforms_platform` (`platform`),
  CONSTRAINT `fk_cheat_platforms_cheat` FOREIGN KEY (`cheat_id`) REFERENCES `cheats` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 5. Tags & Pivot Tables
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tags` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(80) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tags_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cheat_tags` (
  `cheat_id` INT UNSIGNED NOT NULL,
  `tag_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`cheat_id`, `tag_id`),
  CONSTRAINT `fk_cheat_tags_cheat` FOREIGN KEY (`cheat_id`) REFERENCES `cheats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cheat_tags_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 6. Blog Posts
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(191) NOT NULL,
  `slug` VARCHAR(191) NOT NULL,
  `excerpt` VARCHAR(500) NULL,
  `quick_answer` TEXT NULL,
  `content` LONGTEXT NOT NULL,
  `category_id` INT UNSIGNED NULL,
  `featured_image` VARCHAR(255) NULL,
  `featured_image_alt` VARCHAR(255) NULL,
  `status` ENUM('published', 'draft', 'scheduled') NOT NULL DEFAULT 'draft',
  `author_id` INT UNSIGNED NULL,
  `meta_title` VARCHAR(191) NULL,
  `meta_description` VARCHAR(320) NULL,
  `focus_keyword` VARCHAR(120) NULL,
  `faqs_json` JSON NULL,
  `reading_time` SMALLINT UNSIGNED NOT NULL DEFAULT 3,
  `views` INT UNSIGNED NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `published_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_blog_posts_slug` (`slug`),
  KEY `idx_blog_status_pub` (`status`, `published_at`),
  KEY `idx_blog_category` (`category_id`),
  KEY `idx_blog_deleted` (`deleted_at`),
  FULLTEXT KEY `ft_blog` (`title`, `excerpt`, `quick_answer`, `content`),
  CONSTRAINT `fk_blog_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_blog_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blog_post_tags` (
  `blog_post_id` INT UNSIGNED NOT NULL,
  `tag_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`blog_post_id`, `tag_id`),
  CONSTRAINT `fk_blog_tags_post` FOREIGN KEY (`blog_post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_blog_tags_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 7. Static Pages
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(191) NOT NULL,
  `slug` VARCHAR(191) NOT NULL,
  `quick_answer` TEXT NULL,
  `content` LONGTEXT NOT NULL,
  `meta_title` VARCHAR(191) NULL,
  `meta_description` VARCHAR(320) NULL,
  `focus_keyword` VARCHAR(120) NULL,
  `status` ENUM('published', 'draft') NOT NULL DEFAULT 'published',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pages_slug` (`slug`),
  KEY `idx_pages_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 8. Newsletter Subscribers (GDPR + Double Opt-In)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(191) NOT NULL,
  `name` VARCHAR(120) NULL,
  `status` ENUM('pending', 'subscribed', 'unsubscribed') NOT NULL DEFAULT 'pending',
  `token` VARCHAR(64) NOT NULL,
  `consent_ip` VARCHAR(45) NULL,
  `confirmed_at` DATETIME NULL,
  `unsubscribed_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_newsletter_email` (`email`),
  KEY `idx_newsletter_token` (`token`),
  KEY `idx_newsletter_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 9. Contact Messages
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `subject` VARCHAR(191) NOT NULL,
  `message` TEXT NOT NULL,
  `ip_address` VARCHAR(45) NULL,
  `status` ENUM('new', 'read', 'replied', 'archived') NOT NULL DEFAULT 'new',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_contact_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 10. Media Library
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `media` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `filename` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `filepath` VARCHAR(255) NOT NULL,
  `url_path` VARCHAR(255) NOT NULL,
  `mime_type` VARCHAR(100) NOT NULL,
  `file_size` INT UNSIGNED NOT NULL DEFAULT 0,
  `alt_text` VARCHAR(255) NULL,
  `uploaded_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_media_created` (`created_at`),
  CONSTRAINT `fk_media_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 11. Settings (Key-Value)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(120) NOT NULL,
  `setting_value` LONGTEXT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 12. Activity Logs
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL,
  `action` VARCHAR(80) NOT NULL,
  `entity_type` VARCHAR(60) NULL,
  `entity_id` INT UNSIGNED NULL,
  `details` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_activity_created` (`created_at`),
  CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 13. Trust Badges (Footer Carousel)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `trust_badges` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` VARCHAR(120) NOT NULL DEFAULT '',
  `badge_html` TEXT NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_trust_badges_active_sort` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
