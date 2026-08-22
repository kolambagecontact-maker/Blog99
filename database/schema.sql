-- ==========================================================
-- Blog99. — Database Schema
-- Database: inkwell_db
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `inkwell_db`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `inkwell_db`;

-- ----------------------------------------------------------
-- 1. Table: user
-- Stores registered user accounts
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(20) NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 2. Table: blogPost
-- Stores blog posts linked to users via foreign key
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blogPost` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `cover_image` VARCHAR(500) NULL DEFAULT NULL,
  `content` LONGTEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_blogpost_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Indexes for performance
-- ----------------------------------------------------------
CREATE INDEX IF NOT EXISTS `idx_blogpost_user` ON `blogPost` (`user_id`);
CREATE INDEX IF NOT EXISTS `idx_blogpost_created` ON `blogPost` (`created_at`);
