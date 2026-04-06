-- Base MySQL pour stocker les demandes de devis Jim Tech
-- Crée une base et une table prêtes à accueillir les formulaires du site.

CREATE DATABASE IF NOT EXISTS `jimtech_quotes`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `jimtech_quotes`;

CREATE TABLE IF NOT EXISTS `quote_requests` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `project_type` ENUM('web','design','video','global','other') NOT NULL DEFAULT 'web',
  `budget` ENUM('small','medium','large','unknown') NOT NULL DEFAULT 'unknown',
  `message` TEXT NOT NULL,
  `status` ENUM('new','reviewed','contacted','archived') NOT NULL DEFAULT 'new',
  `source` VARCHAR(80) NOT NULL DEFAULT 'site-form',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_project_type` (`project_type`),
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
