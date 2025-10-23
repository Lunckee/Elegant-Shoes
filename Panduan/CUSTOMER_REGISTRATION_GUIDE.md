# 🛍️ Panduan Sistem Registrasi Customer - Elegant Shoes

## ✅ Fitur yang Sudah Dibuat

### **1. Halaman Registrasi Customer**
📍 **File:** `register.php`
- ✅ Form registrasi lengkap dengan validasi
- ✅ Password strength indicator
- ✅ Email validation
- ✅ Duplicate email check
- ✅ Password encryption (bcrypt)
- ✅ Auto-insert ke tabel `customers` dengan status 'active'

### **2. Halaman Login Customer**
📍 **File:** `login.php`
- ✅ Login dengan email dan password
- ✅ Session management untuk customer
- ✅ Remember me option
- ✅ Link ke halaman registrasi
- ✅ Link ke admin login

### **3. Customer Dashboard**
📍 **File:** `customer/dashboard.php`
- ✅ Welcome banner dengan nama customer
- ✅ Statistik: Total pesanan & total belanja
- ✅ Daftar pesanan terbaru (5 terakhir)
- ✅ Informasi akun customer
- ✅ Navigation menu yang clean

### **4. Customer Profile**
📍 **File:** `customer/profile.php`
- ✅ Edit profil customer
- ✅ Update: nama, telepon, alamat, kota, provinsi, kode pos
- ✅ Email readonly (tidak bisa diubah)
- ✅ Success/error messaging

### **5. Customer Logout**
📍 **File:** `customer/logout.php`
- ✅ Destroy session
- ✅ Redirect ke homepage

### **6. Menu di Homepage**
📍 **File:** `index.html`
- ✅ Tombol "Login" di navbar
- ✅ Tombol "Daftar" di navbar dengan gradient styling
- ✅ Link "Admin" tetap tersedia

---

## 🎯 Alur Penggunaan

### **A. Registrasi Customer Baru**

1. **Buka homepage:**
   ```
   http://localhost/project/index.html
   ```

2. **Klik tombol "Daftar" di navbar**
   - Atau langsung ke: `http://localhost/project/register.php`

3. **Isi form registrasi:**
   - Nama Lengkap *
   - Email *
   - Nomor Telepon (opsional)
   - Password * (minimal 6 karakter)
   - Konfirmasi Password *

4. **Klik "Daftar Sekarang"**

5. **Jika berhasil:**
   - ✅ Data tersimpan di tabel `customers`
   - ✅ Password ter-hash dengan bcrypt
   - ✅ Status otomatis = 'active'
   - ✅ Muncul pesan sukses
   - ✅ User bisa langsung login

---

### **B. Login Customer**

1. **Klik tombol "Login" di navbar**
   - Atau: `http://localhost/project/login.php`

2. **Masukkan:**
   - Email
   - Password

3. **Klik "Login"**

4. **Jika berhasil:**
   - ✅ Session dibuat
   - ✅ Redirect ke customer dashboard
   - ✅ Menu navbar berubah

---

### **C. Menggunakan Dashboard**

Setelah login, customer bisa:

1. **Melihat Dashboard** (`customer/dashboard.php`)
   - Total pesanan
   - Total belanja
   - Pesanan terbaru
   - Info akun

2. **Edit Profil** (`customer/profile.php`)
   - Update nama
   - Update telepon
   - Update alamat lengkap
   - Update kota, provinsi, kode pos

3. **Logout** (`customer/logout.php`)
   - Keluar dan kembali ke homepage

---

## 🗄️ Database

### **Tabel yang Digunakan:**

#### **`customers` Table**
```sql
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    address TEXT,
    city VARCHAR(100),
    province VARCHAR(100),
    postal_code VARCHAR(10),
    country VARCHAR(100) DEFAULT 'Indonesia',
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### **Field yang Diisi saat Registrasi:**
- `name` - Nama lengkap
- `email` - Email (unique)
- `phone` - Nomor telepon (opsional)
- `password` - Password ter-hash (bcrypt)
- `status` - 'active' (otomatis)
- `created_at` - Timestamp otomatis

### **Field yang Bisa Diupdate di Profil:**
- `name`
- `phone`
- `address`
- `city`
- `province`
- `postal_code`

---

## 🔐 Keamanan

### **1. Password Security**
- ✅ Minimum 6 karakter
- ✅ Hash menggunakan `password_hash()` dengan bcrypt
- ✅ Verify menggunakan `password_verify()`
- ✅ Password strength indicator real-time

### **2. Input Validation**
- ✅ Email format validation
- ✅ Duplicate email check
- ✅ Password match confirmation
- ✅ SQL injection protection (prepared statements)
- ✅ XSS protection (`htmlspecialchars()`)

### **3. Session Management**
- ✅ Session untuk customer terpisah dari admin
- ✅ Session variables:
  - `customer_id`
  - `customer_name`
  - `customer_email`
- ✅ Login check di setiap halaman customer
- ✅ Redirect ke login jika belum login

---

## 📱 User Interface

### **1. Design Consistency**
- ✅ Gradient purple theme matching admin panel
- ✅ Modern, clean, responsive design
- ✅ Icon-based navigation (Font Awesome)
- ✅ Smooth transitions dan hover effects

### **2. User Experience**
- ✅ Clear error/success messaging
- ✅ Password strength indicator
- ✅ Form validation feedback
- ✅ Breadcrumb navigation
- ✅ Easy navigation between pages

---

## 🧪 Testing

### **Test Case 1: Registrasi Sukses**
1. Buka `register.php`
2. Isi semua field required
3. Password minimal 6 karakter
4. Password dan konfirmasi sama
5. Email belum terdaftar
6. ✅ **Expected:** Registrasi berhasil, muncul success message

### **Test Case 2: Email Duplicate**
1. Registrasi dengan email yang sudah ada
2. ✅ **Expected:** Error "Email sudah terdaftar!"

### **Test Case 3: Password Mismatch**
1. Password dan konfirmasi berbeda
2. ✅ **Expected:** Error "Password tidak cocok!"

### **Test Case 4: Login Sukses**
1. Login dengan email & password yang benar
2. ✅ **Expected:** Redirect ke dashboard, session aktif

### **Test Case 5: Login Gagal**
1. Login dengan password salah
2. ✅ **Expected:** Error "Email atau password salah!"

### **Test Case 6: Access Control**
1. Akses `customer/dashboard.php` tanpa login
2. ✅ **Expected:** Redirect ke `login.php`

### **Test Case 7: Profile Update**
1. Login sebagai customer
2. Update profil
3. ✅ **Expected:** Data tersimpan, success message muncul

---

## 📂 Struktur File

```
project/
├── index.html              # Homepage dengan menu Login & Daftar
├── register.php            # Halaman registrasi customer
├── login.php               # Halaman login customer
├── customer/               # Folder khusus customer
│   ├── dashboard.php       # Dashboard customer
│   ├── profile.php         # Edit profil customer
│   └── logout.php          # Logout customer
├── config/
│   └── database.php        # Database connection
└── CUSTOMER_REGISTRATION_GUIDE.md
```

---

## 🎨 Navbar Menu Structure

### **Before Login (Guest):**
```
[Beranda] [Produk ▼] [Tentang] [Kontak] [Login] [Daftar] [Admin]
```

### **After Login (Customer):**
Di dalam customer dashboard:
```
[Beranda] [Dashboard] [Profil] [Logout]
```

---

## 🔄 Session Variables

### **Customer Session:**
```php
$_SESSION['customer_id']     // ID customer dari database
$_SESSION['customer_name']   // Nama customer
$_SESSION['customer_email']  // Email customer
```

### **Admin Session (Terpisah):**
```php
$_SESSION['admin_id']        // ID admin dari database
$_SESSION['admin_role']      // Role: developer/super_admin/admin/manager
// ... dll
```

---

## 🚀 Quick Start

### **1. Setup Database**
Database sudah include tabel `customers`. Jika belum:
```sql
-- Jalankan file:
SOURCE /path/to/elegant_shoes_database.sql
```

### **2. Test Registrasi**
```
1. Buka: http://localhost/project/register.php
2. Isi form:
   - Nama: Test Customer
   - Email: test@customer.com
   - Phone: 081234567890
   - Password: test123
   - Confirm: test123
3. Klik "Daftar Sekarang"
4. Success!
```

### **3. Test Login**
```
1. Buka: http://localhost/project/login.php
2. Login:
   - Email: test@customer.com
   - Password: test123
3. Klik "Login"
4. Redirect ke dashboard!
```

---

## 🎯 Perbedaan Customer vs Admin

| Feature | Customer | Admin |
|---------|----------|-------|
| **Login Page** | `login.php` | `admin/login.php` |
| **Dashboard** | `customer/dashboard.php` | `admin/dashboard.php` |
| **Database Table** | `customers` | `admin_users` |
| **Session Key** | `customer_id` | `admin_id` |
| **Registration** | ✅ Public | ❌ Only via admin/developer tools |
| **Role System** | No roles (all customers equal) | ✅ Yes (developer/super_admin/admin/manager) |
| **Features** | - View orders<br>- Edit profile<br>- Track orders | - Manage products<br>- Manage orders<br>- Manage customers<br>- View reports |

---

## ✨ Fitur Tambahan yang Bisa Dikembangkan

### **Future Enhancements:**
1. ✨ Email verification
2. ✨ Forgot password functionality
3. ✨ Social login (Google, Facebook)
4. ✨ Order history dengan detail lengkap
5. ✨ Wishlist/favorite products
6. ✨ Shopping cart
7. ✨ Customer reviews & ratings
8. ✨ Address book (multiple addresses)
9. ✨ Loyalty points system
10. ✨ Order tracking real-time

---

## 📞 Support

Jika ada pertanyaan atau issue:
1. Cek apakah database sudah diimport
2. Cek koneksi database di `config/database.php`
3. Cek session di browser (clear cookies jika perlu)
4. Cek error log PHP

---

**Sistem registrasi customer sudah siap digunakan!** 🎉





