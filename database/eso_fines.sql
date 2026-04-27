-- =====================================================
-- ESO Fines Management System - Database Schema
-- Database: eso_fines
-- =====================================================

CREATE DATABASE IF NOT EXISTS `eso_fines`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `eso_fines`;

-- -----------------------------------------------------
-- Table: users (admin & student accounts)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(60) NOT NULL UNIQUE,
    `email` VARCHAR(120) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin','student') NOT NULL DEFAULT 'student',
    `student_id` INT(11) UNSIGNED NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_role` (`role`)
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table: students (student profile data)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `students` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `student_no` VARCHAR(30) NOT NULL UNIQUE,
    `full_name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(120) NOT NULL,
    `contact` VARCHAR(20) DEFAULT NULL,
    `course` VARCHAR(80) DEFAULT NULL,
    `year_level` VARCHAR(20) DEFAULT NULL,
    `section` VARCHAR(20) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_full_name` (`full_name`)
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table: fine_categories (predefined fine reasons)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fine_categories` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `default_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `description` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table: fines (issued fines)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fines` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `student_id` INT(11) UNSIGNED NOT NULL,
    `category_id` INT(11) UNSIGNED DEFAULT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `reason` VARCHAR(255) NOT NULL,
    `status` ENUM('unpaid','pending','paid','cancelled') NOT NULL DEFAULT 'unpaid',
    `issued_by` INT(11) UNSIGNED NOT NULL,
    `issued_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `paid_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_student` (`student_id`),
    KEY `idx_status` (`status`),
    CONSTRAINT `fk_fines_student` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fines_category` FOREIGN KEY (`category_id`) REFERENCES `fine_categories`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_fines_admin` FOREIGN KEY (`issued_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table: payments (GCash payment transactions)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `fine_id` INT(11) UNSIGNED NOT NULL,
    `student_id` INT(11) UNSIGNED NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `reference_no` VARCHAR(60) NOT NULL UNIQUE,
    `gcash_ref` VARCHAR(60) DEFAULT NULL,
    `payment_method` VARCHAR(40) NOT NULL DEFAULT 'GCASH',
    `status` ENUM('initiated','pending','success','failed') NOT NULL DEFAULT 'initiated',
    `qr_payload` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `paid_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_fine` (`fine_id`),
    KEY `idx_student` (`student_id`),
    KEY `idx_status` (`status`),
    CONSTRAINT `fk_payments_fine` FOREIGN KEY (`fine_id`) REFERENCES `fines`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_payments_student` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table: activity_logs
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) UNSIGNED DEFAULT NULL,
    `action` VARCHAR(80) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`)
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Seed: default admin (password: Admin@123)
-- -----------------------------------------------------
INSERT IGNORE INTO `users` (`username`,`email`,`password`,`role`,`is_active`) VALUES
('admin','admin@eso.local','$2y$10$cIK5eHUUWchPXVdWkue0QudSOp6OrRhZ9Wbh/jp8/V6yh5yuQ6j9G','admin',1);
-- ^ bcrypt hash of "Admin@123"

-- -----------------------------------------------------
-- Seed: default fine categories
-- -----------------------------------------------------
INSERT IGNORE INTO `fine_categories` (`name`,`default_amount`,`description`) VALUES
('Improper Uniform', 50.00, 'Wearing uniform that does not comply with school dress code'),
('No ID', 30.00, 'Failure to wear/present school ID inside campus'),
('Littering', 100.00, 'Throwing trash outside designated bins'),
('Smoking on Campus', 500.00, 'Smoking inside school premises'),
('Late Attendance', 25.00, 'Tardiness to school activities or classes');

-- -----------------------------------------------------
-- Seed: sample students + matching login accounts
--       (password for both demo students: Student@123)
-- -----------------------------------------------------
INSERT IGNORE INTO `students` (`student_no`,`full_name`,`email`,`contact`,`course`,`year_level`,`section`) VALUES
('2024-0001','Juan Dela Cruz','juan@student.local','09171234567','BSIT','3','A'),
('2024-0002','Maria Santos','maria@student.local','09181234567','BSCS','2','B');

INSERT IGNORE INTO `users` (`username`,`email`,`password`,`role`,`student_id`,`is_active`) VALUES
('juan','juan@student.local','$2y$10$GKodsipa1EpyH/QafsTSgecdt5LSInJ4jlA37t3Cnx4ONULBk0wRG','student',1,1),
('maria','maria@student.local','$2y$10$GKodsipa1EpyH/QafsTSgecdt5LSInJ4jlA37t3Cnx4ONULBk0wRG','student',2,1);
