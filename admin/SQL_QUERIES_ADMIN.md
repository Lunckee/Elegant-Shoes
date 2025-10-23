# SQL Queries untuk Manajemen Admin User

Dokumentasi lengkap query SQL untuk mengelola admin user di phpMyAdmin.

---

## 📋 Daftar Isi

1. [Melihat Data Admin](#1-melihat-data-admin)
2. [Membuat Admin Baru](#2-membuat-admin-baru)
3. [Update Password](#3-update-password)
4. [Update Data Admin](#4-update-data-admin)
5. [Hapus Admin](#5-hapus-admin)
6. [Generate Password Hash](#6-generate-password-hash)

---

## 1. Melihat Data Admin

### Lihat Semua Admin
```sql
SELECT * FROM admin_users ORDER BY created_at DESC;
```

### Lihat Admin Aktif Saja
```sql
SELECT * FROM admin_users WHERE status = 'active' ORDER BY created_at DESC;
```

### Lihat Detail Admin Tertentu
```sql
SELECT * FROM admin_users WHERE username = 'admin';
```

### Lihat Berdasarkan Email
```sql
SELECT * FROM admin_users WHERE email = 'admin@elegantshoes.com';
```

---

## 2. Membuat Admin Baru

### Langkah-langkah:

#### A. Generate Password Hash Dulu
Gunakan salah satu tool berikut:
- **Tool 1**: Akses `http://localhost/project/admin/password_hash_generator.php`
- **Tool 2**: Akses `http://localhost/project/admin/admin_user_manager.php`
- **Manual PHP**: Buat file `hash.php` dengan isi:
  ```php
  <?php
  echo password_hash('PasswordAnda123', PASSWORD_BCRYPT);
  ```

#### B. Insert Admin Baru
```sql
-- Template:
INSERT INTO admin_users (username, email, password, full_name, role, status)
VALUES ('username_baru', 'email@example.com', 'HASH_PASSWORD_DISINI', 'Nama Lengkap', 'admin', 'active');

-- Contoh konkret:
INSERT INTO admin_users (username, email, password, full_name, role, status)
VALUES ('johndoe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', 'admin', 'active');
```

### Role yang Tersedia:
- `super_admin` - Akses penuh
- `admin` - Akses standar
- `manager` - Akses terbatas

### Status yang Tersedia:
- `active` - Bisa login
- `inactive` - Tidak bisa login

---

## 3. Update Password

### Update Password Admin Tertentu (by Username)
```sql
-- Template:
UPDATE admin_users 
SET password = 'HASH_PASSWORD_BARU' 
WHERE username = 'admin';

-- Contoh dengan hash:
UPDATE admin_users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE username = 'admin';
```

### Update Password by Email
```sql
UPDATE admin_users 
SET password = 'HASH_PASSWORD_BARU' 
WHERE email = 'admin@elegantshoes.com';
```

### Update Password by ID
```sql
UPDATE admin_users 
SET password = 'HASH_PASSWORD_BARU' 
WHERE id = 1;
```

### Reset Password untuk Semua Admin (HATI-HATI!)
```sql
-- Ini akan set semua password menjadi "password"
UPDATE admin_users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
```

---

## 4. Update Data Admin

### Update Email
```sql
UPDATE admin_users 
SET email = 'email_baru@example.com' 
WHERE username = 'admin';
```

### Update Nama Lengkap
```sql
UPDATE admin_users 
SET full_name = 'Nama Baru' 
WHERE username = 'admin';
```

### Update Role
```sql
UPDATE admin_users 
SET role = 'super_admin' 
WHERE username = 'admin';
```

### Update Status (Aktif/Nonaktif)
```sql
-- Nonaktifkan admin
UPDATE admin_users 
SET status = 'inactive' 
WHERE username = 'admin';

-- Aktifkan kembali
UPDATE admin_users 
SET status = 'active' 
WHERE username = 'admin';
```

### Update Multiple Fields Sekaligus
```sql
UPDATE admin_users 
SET 
    email = 'newemail@example.com',
    full_name = 'New Name',
    role = 'super_admin',
    status = 'active'
WHERE username = 'admin';
```

---

## 5. Hapus Admin

### Hapus Admin Tertentu
```sql
-- By username
DELETE FROM admin_users WHERE username = 'admin_to_delete';

-- By email
DELETE FROM admin_users WHERE email = 'delete@example.com';

-- By ID
DELETE FROM admin_users WHERE id = 5;
```

### ⚠️ PERINGATAN:
- Jangan hapus semua admin! Minimal sisakan 1 admin aktif
- Hapus dengan hati-hati, tidak bisa di-undo

---

## 6. Generate Password Hash

### Cara 1: Menggunakan Tool Web (TERMUDAH)
```
Akses: http://localhost/project/admin/password_hash_generator.php
```

### Cara 2: Menggunakan Admin Manager (PALING MUDAH)
```
Akses: http://localhost/project/admin/admin_user_manager.php
- Bisa buat admin baru
- Bisa reset password
- Bisa hapus admin
- Semua via interface web
```

### Cara 3: Manual dengan PHP
Buat file `generate_hash.php`:
```php
<?php
// Ganti 'YourPasswordHere' dengan password yang diinginkan
$password = 'YourPasswordHere';
$hash = password_hash($password, PASSWORD_BCRYPT);
echo "Password: " . $password . "<br>";
echo "Hash: " . $hash;
```

Akses via browser: `http://localhost/project/generate_hash.php`

### Cara 4: Via phpMyAdmin SQL (Advanced)
⚠️ Tidak disarankan karena phpMyAdmin tidak support `password_hash()` langsung.

---

## 🔒 Password Default yang Ada di Database

Database sudah memiliki 2 user default:

### User 1:
- **Username**: `admin`
- **Email**: `admin@elegantshoes.com`
- **Password**: `password`
- **Role**: `super_admin`

### User 2:
- **Username**: `manager`
- **Email**: `manager@elegantshoes.com`
- **Password**: `password`
- **Role**: `manager`

**Hash untuk password "password":**
```
$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
```

---

## 📝 Tips & Best Practices

### 1. Selalu Generate Hash Baru
Jangan pakai hash yang sama untuk semua user. Generate hash baru untuk setiap password.

### 2. Password Kuat
Gunakan kombinasi:
- Minimal 8 karakter
- Huruf besar dan kecil
- Angka
- Simbol khusus

### 3. Backup Sebelum Update
Sebelum menjalankan query UPDATE atau DELETE, backup dulu:
```sql
SELECT * FROM admin_users INTO OUTFILE '/tmp/admin_backup.sql';
```

### 4. Verifikasi Setelah Update
Setelah update password, coba login untuk memastikan berhasil.

### 5. Hapus Tool Keamanan
Setelah selesai menggunakan tool generator, **hapus file-file berikut**:
- `admin/password_hash_generator.php`
- `admin/admin_user_manager.php`
- `generate_hash.php` (jika dibuat)

---

## ❓ Troubleshooting

### Problem: Login Gagal Setelah Update Password
**Solusi:**
1. Pastikan hash password benar (dimulai dengan `$2y$10$`)
2. Pastikan tidak ada spasi di awal/akhir hash
3. Pastikan status user = 'active'
4. Clear session/cookies browser

### Problem: Tidak Bisa Hapus Admin
**Solusi:**
```sql
-- Cek apakah ada foreign key constraint
SELECT * FROM activity_logs WHERE admin_id = ID_YANG_MAU_DIHAPUS;

-- Jika ada, hapus dulu activity logs atau set NULL:
UPDATE activity_logs SET admin_id = NULL WHERE admin_id = ID_YANG_MAU_DIHAPUS;

-- Baru hapus admin
DELETE FROM admin_users WHERE id = ID_YANG_MAU_DIHAPUS;
```

### Problem: Lupa Username dan Email Semua Admin
**Solusi:**
```sql
-- Lihat semua admin
SELECT id, username, email, full_name, role, status FROM admin_users;

-- Atau buat admin baru langsung
INSERT INTO admin_users (username, email, password, full_name, role, status)
VALUES ('recovery', 'recovery@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Recovery Admin', 'super_admin', 'active');

-- Login dengan: username = recovery, password = password
```

---

## 🚀 Quick Reference

### Reset Password Admin Menjadi "password"
```sql
UPDATE admin_users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE username = 'admin';
```

### Buat Admin Baru dengan Password "password"
```sql
INSERT INTO admin_users (username, email, password, full_name, role, status)
VALUES ('newadmin', 'new@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'New Admin', 'admin', 'active');
```

### Lihat Semua Admin Aktif
```sql
SELECT id, username, email, full_name, role FROM admin_users WHERE status = 'active';
```

---

**Dibuat untuk:** Elegant Shoes Admin Panel  
**Database:** elegant_shoes_db  
**Tabel:** admin_users

