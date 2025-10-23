# 🔧 Developer Tools Setup Guide

## Kenapa Developer Tools Tidak Muncul?

Ada beberapa kemungkinan:

### ✅ **Checklist Troubleshooting:**

1. **User 'surya' belum ada di database**
2. **Role 'developer' belum ditambahkan ke ENUM**
3. **Anda login dengan user lain (bukan 'surya')**
4. **Session tidak ter-refresh setelah update database**

---

## 📋 **Langkah-langkah Setup (URUT):**

### **Step 1: Cek User yang Ada di Database**

Buka browser: `http://localhost/project/check_users.php`

Ini akan menampilkan:
- ✅ Semua user yang ada
- ✅ Role masing-masing user
- ✅ Struktur ENUM role
- ✅ SQL yang perlu dijalankan jika ada masalah

---

### **Step 2: Tambahkan Role 'developer' dan User 'surya'**

**Via phpMyAdmin:**

1. Buka `http://localhost/phpmyadmin`
2. Pilih database `elegant_shoes_db`
3. Klik tab **SQL**
4. Copy-paste query berikut:

```sql
-- Step 1: Tambahkan 'developer' ke role ENUM
ALTER TABLE admin_users 
MODIFY COLUMN role ENUM('super_admin', 'admin', 'manager', 'developer') DEFAULT 'admin';

-- Step 2: Buat user developer 'surya'
INSERT INTO admin_users (username, email, password, full_name, role, status)
VALUES ('surya', 'surya@developer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Surya - Developer', 'developer', 'active');

-- Step 3: Verifikasi
SELECT id, username, email, role FROM admin_users WHERE role = 'developer';
```

5. Klik **Go/Kirim**

**ATAU via File SQL:**

1. Buka phpMyAdmin
2. Pilih database `elegant_shoes_db`
3. Klik tab **Import**
4. Pilih file: `admin/add_developer_user.sql`
5. Klik **Go**

---

### **Step 3: Logout dari Semua Session**

**PENTING:** Setelah menambahkan user baru, Anda harus logout!

1. Klik **Logout** di admin panel
2. Atau buka: `http://localhost/project/admin/logout.php`
3. Hapus cookies/cache browser (optional tapi recommended)

---

### **Step 4: Login dengan User Developer**

Login dengan credentials:
- **Username**: `surya`
- **Password**: `password`

---

### **Step 5: Verifikasi Developer Tools Muncul**

Setelah login, buka: `http://localhost/project/admin/dashboard.php`

Anda seharusnya melihat panel **Developer Tools** di atas stats cards:

```
┌─────────────────────────────────────────────────────┐
│ 🔧 Developer Tools                                  │
│ Akses khusus untuk developer - Kelola admin users   │
│                                                     │
│ [Admin User Manager] [Password Generator]           │
└─────────────────────────────────────────────────────┘
```

---

## 🐛 **Debug Tools:**

### **Tool 1: Check Users**
```
http://localhost/project/check_users.php
```
Menampilkan semua user dan memberikan instruksi SQL jika diperlukan.

### **Tool 2: Debug Session**
```
http://localhost/project/admin/debug_session.php
```
Menampilkan:
- Session data saat ini
- Role yang sedang login
- Apakah Developer Tools seharusnya muncul
- Solusi jika tidak muncul

---

## 🔍 **Troubleshooting Spesifik:**

### **Problem 1: "User surya tidak ada"**

**Solusi:**
```sql
INSERT INTO admin_users (username, email, password, full_name, role, status)
VALUES ('surya', 'surya@developer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Surya - Developer', 'developer', 'active');
```

### **Problem 2: "Error: Invalid value 'developer' for column 'role'"**

**Penyebab:** ENUM belum diupdate

**Solusi:**
```sql
ALTER TABLE admin_users 
MODIFY COLUMN role ENUM('super_admin', 'admin', 'manager', 'developer') DEFAULT 'admin';
```

### **Problem 3: "Sudah login dengan surya tapi Developer Tools tidak muncul"**

**Penyebab:** Session lama masih tersimpan

**Solusi:**
1. Logout
2. Clear browser cache/cookies
3. Login ulang dengan `surya`

### **Problem 4: "Developer Tools muncul tapi akses ditolak"**

**Penyebab:** File `admin_user_manager.php` dan `password_hash_generator.php` punya proteksi

**Solusi:** Pastikan session role benar-benar 'developer'
- Cek via: `http://localhost/project/admin/debug_session.php`

### **Problem 5: "Lupa password user surya"**

**Default password:** `password`

**Jika perlu reset:**
```sql
UPDATE admin_users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE username = 'surya';
-- Password akan menjadi: password
```

---

## 📊 **Expected Behavior:**

### **Login sebagai 'surya' (developer):**
✅ Developer Tools muncul di dashboard  
✅ Bisa akses Admin User Manager  
✅ Bisa akses Password Generator  
✅ Bisa mengelola semua admin users  

### **Login sebagai 'admin', 'manager', atau 'super_admin':**
❌ Developer Tools TIDAK muncul  
❌ Tidak bisa akses Admin User Manager (redirect dengan error)  
❌ Tidak bisa akses Password Generator (redirect dengan error)  

---

## 🎯 **Quick Test:**

1. Buka: `http://localhost/project/check_users.php`
   - Lihat apakah user 'surya' ada dengan role 'developer'

2. Logout dari admin panel

3. Login dengan:
   - Username: `surya`
   - Password: `password`

4. Buka dashboard

5. Developer Tools seharusnya muncul!

---

## 📞 **Support:**

Jika masih tidak muncul setelah semua langkah di atas:

1. Buka `http://localhost/project/admin/debug_session.php`
2. Screenshot hasilnya
3. Cek apakah `admin_role` di session = `'developer'`
4. Jika tidak, ada masalah dengan login/session

---

## 🔐 **Default Login Credentials:**

| Username | Password | Role | Developer Tools? |
|----------|----------|------|------------------|
| surya | password | developer | ✅ YES |
| admin | password | super_admin | ❌ NO |
| manager | password | manager | ❌ NO |

---

**File yang Terlibat:**
- `/admin/dashboard.php` - Tombol developer tools (line 79-93)
- `/admin/admin_user_manager.php` - Proteksi akses (line 13-17)
- `/admin/password_hash_generator.php` - Proteksi akses (line 13-17)
- `/includes/auth.php` - Sistem autentikasi
- `/check_users.php` - Debug tool
- `/admin/debug_session.php` - Session debug tool





