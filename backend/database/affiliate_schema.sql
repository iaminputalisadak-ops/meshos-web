-- Affiliate (Promoter) System - Add to existing meesho_ecommerce database
-- Run after main schema. Usage: mysql -u root meesho_ecommerce < affiliate_schema.sql

USE meesho_ecommerce;

-- 1) Promoter profiles (linked to users; one user can become a promoter)
CREATE TABLE IF NOT EXISTS promoter_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    code VARCHAR(50) NOT NULL UNIQUE,
    commission_rate DECIMAL(5, 2) NOT NULL DEFAULT 10.00,
    status ENUM('pending', 'approved', 'rejected', 'suspended') DEFAULT 'pending',
    total_clicks INT UNSIGNED DEFAULT 0,
    total_orders INT UNSIGNED DEFAULT 0,
    total_sales DECIMAL(12, 2) DEFAULT 0,
    pending_commission DECIMAL(12, 2) DEFAULT 0,
    approved_commission DECIMAL(12, 2) DEFAULT 0,
    paid_commission DECIMAL(12, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_code (code),
    INDEX idx_status (status),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Referral clicks
CREATE TABLE IF NOT EXISTS referral_clicks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    promoter_id INT NOT NULL,
    product_id INT NULL,
    visitor_ip VARCHAR(45) NULL,
    visitor_session VARCHAR(255) NULL,
    user_agent VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (promoter_id) REFERENCES promoter_profiles(id) ON DELETE CASCADE,
    INDEX idx_promoter (promoter_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Commissions (one per order attributed to promoter)
CREATE TABLE IF NOT EXISTS commissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    promoter_id INT NOT NULL,
    order_id INT NOT NULL UNIQUE,
    order_amount DECIMAL(10, 2) NOT NULL,
    commission_rate DECIMAL(5, 2) NOT NULL DEFAULT 10.00,
    commission_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'reversed', 'paid') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    FOREIGN KEY (promoter_id) REFERENCES promoter_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_promoter (promoter_id),
    INDEX idx_status (status),
    INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Withdrawal requests
CREATE TABLE IF NOT EXISTS withdrawal_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    promoter_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'approved', 'paid', 'rejected') DEFAULT 'pending',
    payment_method VARCHAR(100) NULL,
    payment_details TEXT NULL,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    notes TEXT NULL,
    FOREIGN KEY (promoter_id) REFERENCES promoter_profiles(id) ON DELETE CASCADE,
    INDEX idx_promoter (promoter_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5) Add promoter_id and referral_code to orders
DROP PROCEDURE IF EXISTS add_promoter_columns_to_orders;
DELIMITER //
CREATE PROCEDURE add_promoter_columns_to_orders()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'promoter_id'
    ) THEN
        ALTER TABLE orders ADD COLUMN promoter_id INT NULL AFTER user_id;
    END IF;
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'referral_code'
    ) THEN
        ALTER TABLE orders ADD COLUMN referral_code VARCHAR(50) NULL AFTER promoter_id;
    END IF;
END //
DELIMITER ;
CALL add_promoter_columns_to_orders();
DROP PROCEDURE IF EXISTS add_promoter_columns_to_orders;

-- 6) Optional: add FK from orders.promoter_id to promoter_profiles.id
-- Run manually if needed: ALTER TABLE orders ADD CONSTRAINT fk_orders_promoter FOREIGN KEY (promoter_id) REFERENCES promoter_profiles(id) ON DELETE SET NULL;
