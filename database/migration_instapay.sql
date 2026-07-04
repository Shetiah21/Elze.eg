-- ============================================================
-- Elze.eg InstaPay Payment Verification Migration
-- Run this on an existing elze_db to add InstaPay support fields
-- Generated: 2026-07-04
-- ============================================================

USE elze_db;

-- 1. Extend payment_status ENUM to include 'pending_verification'
ALTER TABLE orders
    MODIFY COLUMN payment_status ENUM('pending', 'pending_verification', 'paid', 'failed') DEFAULT 'pending';

-- 2. Add InstaPay verification metadata columns
ALTER TABLE orders
    ADD COLUMN payment_date TIMESTAMP NULL DEFAULT NULL AFTER payment_reference,
    ADD COLUMN payment_verified_at TIMESTAMP NULL DEFAULT NULL AFTER payment_date,
    ADD COLUMN verified_by INT NULL DEFAULT NULL AFTER payment_verified_at;

-- 3. Add foreign key for verified_by → users(id)
ALTER TABLE orders
    ADD CONSTRAINT fk_orders_verified_by
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL;

-- 4. Add index for payment_status lookups (admin filtering)
CREATE INDEX idx_orders_payment_status ON orders(payment_status);
