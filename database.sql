-- ============================================
-- APMC e-Trading System - Database Schema
-- Agricultural Produce Market Committee
-- ============================================

CREATE DATABASE IF NOT EXISTS apmc_trading CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE apmc_trading;

-- Users table (Farmers, Traders, APMC Officers)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('farmer', 'trader', 'admin') NOT NULL,
    address TEXT,
    village VARCHAR(100),
    district VARCHAR(100),
    state VARCHAR(100) DEFAULT 'Gujarat',
    aadhar_number VARCHAR(12),
    license_number VARCHAR(50),
    is_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Produce Listings by Farmers
CREATE TABLE listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    produce_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit VARCHAR(20) NOT NULL DEFAULT 'Quintal',
    base_price DECIMAL(10,2) NOT NULL,
    description TEXT,
    location VARCHAR(150),
    image VARCHAR(255),
    status ENUM('active', 'bidding', 'sold', 'expired', 'pending') DEFAULT 'pending',
    bid_start_time DATETIME,
    bid_end_time DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bids placed by Traders
CREATE TABLE bids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    trader_id INT NOT NULL,
    bid_amount DECIMAL(10,2) NOT NULL,
    status ENUM('active', 'won', 'lost') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
    FOREIGN KEY (trader_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Completed Transactions
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    farmer_id INT NOT NULL,
    trader_id INT NOT NULL,
    final_amount DECIMAL(10,2) NOT NULL,
    commission DECIMAL(10,2) DEFAULT 0,
    payment_status ENUM('pending', 'paid') DEFAULT 'pending',
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES listings(id),
    FOREIGN KEY (farmer_id) REFERENCES users(id),
    FOREIGN KEY (trader_id) REFERENCES users(id)
);

-- Notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Default Admin (password: Admin@123)
INSERT INTO users (full_name, email, phone, password, role, is_approved) VALUES
('APMC Officer', 'admin@apmc.gov.in', '9000000000',
 '$2y$10$TKh8H1.PFmjZ5f.eAOGJEuuVH8kx2C3jQGqMPCvF5yT2sHw6nkWOm', 'admin', 1);
