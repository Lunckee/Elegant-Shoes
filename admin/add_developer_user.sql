-- =====================================================
-- Script untuk Menambahkan User Developer 'surya'
-- =====================================================

-- 1. Tambahkan role 'developer' ke ENUM jika belum ada
ALTER TABLE admin_users 
MODIFY COLUMN role ENUM('super_admin', 'admin', 'manager', 'developer') DEFAULT 'admin';

-- 2. Insert user developer 'surya' (jika belum ada)
INSERT IGNORE INTO admin_users (username, email, password, full_name, role, status)
VALUES ('surya', 'surya@developer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Surya - Developer', 'developer', 'active');

-- 3. Verifikasi user berhasil ditambahkan
SELECT id, username, email, full_name, role, status, created_at 
FROM admin_users 
WHERE role = 'developer';

-- =====================================================
-- INFORMASI LOGIN:
-- Username: surya
-- Password: password
-- Role: developer
-- =====================================================

-- Catatan:
-- - Password default adalah 'password' (hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi)
-- - Setelah login pertama, disarankan untuk mengganti password
-- - User 'surya' akan memiliki akses ke Developer Tools di dashboard




