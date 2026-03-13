-- Creator Partner Membership Program - Database Schema
-- Run: mysql -u root meesho_ecommerce < backend/database/creator_schema.sql

USE meesho_ecommerce;

-- Creator profiles (linked to users table)
CREATE TABLE IF NOT EXISTS creator_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    creator_code VARCHAR(50) NOT NULL UNIQUE,
    approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    active_status ENUM('active', 'expired', 'inactive') DEFAULT 'inactive',
    commission_rate DECIMAL(5, 2) NOT NULL DEFAULT 10.00,
    total_earned DECIMAL(12, 2) DEFAULT 0,
    total_paid DECIMAL(12, 2) DEFAULT 0,
    wallet_balance DECIMAL(12, 2) DEFAULT 0,
    total_clicks INT UNSIGNED DEFAULT 0,
    total_orders INT UNSIGNED DEFAULT 0,
    total_sales DECIMAL(12, 2) DEFAULT 0,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_code (creator_code),
    INDEX idx_approval (approval_status),
    INDEX idx_active (active_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Creator memberships (₹500/28 days, admin approved)
CREATE TABLE IF NOT EXISTS creator_memberships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    creator_id INT NOT NULL,
    payment_amount DECIMAL(10, 2) NOT NULL DEFAULT 500.00,
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    payment_reference VARCHAR(255) NULL,
    admin_approval ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    starts_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    duration_days INT NOT NULL DEFAULT 28,
    renewal_number INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES creator_profiles(id) ON DELETE CASCADE,
    INDEX idx_creator (creator_id),
    INDEX idx_payment (payment_status),
    INDEX idx_approval (admin_approval),
    INDEX idx_expiry (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Creator referral clicks
CREATE TABLE IF NOT EXISTS creator_clicks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    creator_id INT NOT NULL,
    membership_id INT NULL,
    product_id INT NULL,
    visitor_ip VARCHAR(45) NULL,
    visitor_session VARCHAR(255) NULL,
    user_agent VARCHAR(500) NULL,
    clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES creator_profiles(id) ON DELETE CASCADE,
    INDEX idx_creator (creator_id),
    INDEX idx_membership (membership_id),
    INDEX idx_clicked (clicked_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Creator commissions (per order)
CREATE TABLE IF NOT EXISTS creator_commissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    creator_id INT NOT NULL,
    membership_id INT NULL,
    order_id INT NOT NULL UNIQUE,
    sale_amount DECIMAL(10, 2) NOT NULL,
    commission_rate DECIMAL(5, 2) NOT NULL,
    commission_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'reversed', 'paid') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    FOREIGN KEY (creator_id) REFERENCES creator_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_creator (creator_id),
    INDEX idx_membership (membership_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Creator wallet transactions
CREATE TABLE IF NOT EXISTS creator_wallet_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    creator_id INT NOT NULL,
    type ENUM('credit', 'debit') NOT NULL,
    source ENUM('commission', 'withdrawal', 'adjustment', 'membership_fee') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    reference_id INT NULL,
    balance_after DECIMAL(12, 2) NOT NULL DEFAULT 0,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES creator_profiles(id) ON DELETE CASCADE,
    INDEX idx_creator (creator_id),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Creator withdrawal requests
CREATE TABLE IF NOT EXISTS creator_withdrawals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    creator_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(100) NULL,
    payment_details TEXT NULL,
    status ENUM('pending', 'approved', 'paid', 'rejected') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    notes TEXT NULL,
    FOREIGN KEY (creator_id) REFERENCES creator_profiles(id) ON DELETE CASCADE,
    INDEX idx_creator (creator_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add creator columns to orders (if not exist)
DROP PROCEDURE IF EXISTS add_creator_columns_to_orders;
DELIMITER //
CREATE PROCEDURE add_creator_columns_to_orders()
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='creator_id') THEN
        ALTER TABLE orders ADD COLUMN creator_id INT NULL AFTER promoter_id;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='membership_id') THEN
        ALTER TABLE orders ADD COLUMN membership_id INT NULL AFTER creator_id;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='creator_code') THEN
        ALTER TABLE orders ADD COLUMN creator_code VARCHAR(50) NULL AFTER membership_id;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='commission_percentage') THEN
        ALTER TABLE orders ADD COLUMN commission_percentage DECIMAL(5,2) NULL AFTER creator_code;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='commission_amount') THEN
        ALTER TABLE orders ADD COLUMN commission_amount DECIMAL(10,2) NULL AFTER commission_percentage;
    END IF;
END //
DELIMITER ;
CALL add_creator_columns_to_orders();
DROP PROCEDURE IF EXISTS add_creator_columns_to_orders;
