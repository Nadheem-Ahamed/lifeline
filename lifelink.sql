CREATE DATABASE IF NOT EXISTS lifelink
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE lifelink;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS donors (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    age TINYINT UNSIGNED NOT NULL,
    gender ENUM('Woman', 'Man', 'Non-binary', 'Prefer not to say') NOT NULL,
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    phone VARCHAR(20) NOT NULL,
    city VARCHAR(100) NOT NULL,
    last_donation_date DATE NULL,
    is_available TINYINT(1) NOT NULL DEFAULT 0,
    contact_consent_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_donors_user_id (user_id),
    KEY idx_donors_search (blood_group, city, is_available),
    CONSTRAINT fk_donors_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS blood_requests (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    patient_name VARCHAR(100) NOT NULL,
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    hospital_name VARCHAR(150) NOT NULL,
    city VARCHAR(100) NOT NULL,
    required_units TINYINT UNSIGNED NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    emergency_level ENUM('Routine', 'Urgent', 'Emergency') NOT NULL,
    status ENUM('Pending', 'Completed', 'Rejected') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_requests_status_created (status, created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admins (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admins_email (email)
) ENGINE=InnoDB;
