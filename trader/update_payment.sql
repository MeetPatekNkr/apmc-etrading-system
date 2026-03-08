-- Run this SQL in phpMyAdmin to add payment fields to transactions table
USE apmc_trading;

ALTER TABLE transactions 
ADD COLUMN payment_method VARCHAR(50) DEFAULT NULL AFTER payment_status,
ADD COLUMN utr_number VARCHAR(100) DEFAULT NULL AFTER payment_method,
ADD COLUMN payment_screenshot VARCHAR(255) DEFAULT NULL AFTER utr_number,
ADD COLUMN payment_date DATETIME DEFAULT NULL AFTER payment_screenshot,
ADD COLUMN payment_note TEXT DEFAULT NULL AFTER payment_date;
