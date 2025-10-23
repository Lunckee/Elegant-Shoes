-- =====================================================
-- INSERT DEMO CUSTOMER ACCOUNT
-- Elegant Shoes Database
-- =====================================================

USE elegant_shoes_db;

-- Insert demo customer
-- Email: customer@example.com
-- Password: password123

INSERT INTO customers (name, email, phone, password, address, city, province, postal_code, country, status, email_verified) 
VALUES (
    'Customer Demo',
    'customer@example.com',
    '081234567890',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- password: password
    'Jl. Contoh No. 123',
    'Jakarta',
    'DKI Jakarta',
    '12345',
    'Indonesia',
    'active',
    1
)
ON DUPLICATE KEY UPDATE 
    name = VALUES(name),
    phone = VALUES(phone);

-- Cek customer yang berhasil dibuat
SELECT id, name, email, phone, city, status, created_at 
FROM customers 
WHERE email = 'customer@example.com';

-- =====================================================
-- LOGIN CREDENTIALS
-- =====================================================
-- Email: customer@example.com
-- Password: password
-- =====================================================



