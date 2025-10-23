# 🔐 Panduan Login Customer ke Shop.php

## ✅ Sudah Diupdate!

File `login.php` sudah diupdate agar setelah login akan **langsung redirect ke shop.php** (bukan dashboard.php).

---

## 🚀 Cara Menjalankan Program

### Metode 1: Login dengan Account Baru (Registrasi)

**Step 1:** Buka Browser
```
http://localhost/project/register.php
```

**Step 2:** Isi Form Registrasi
- Nama Lengkap: Masukkan nama Anda
- Email: Gunakan email valid (contoh: test@example.com)
- Nomor Telepon: 08123456789 (opsional)
- Password: Minimal 6 karakter
- Konfirmasi Password: Ketik ulang password

**Step 3:** Klik "Daftar Sekarang"

**Step 4:** Setelah berhasil, klik link "Login di sini"

**Step 5:** Login dengan email & password yang baru dibuat

**Step 6:** Otomatis redirect ke shop.php! ✅

---

### Metode 2: Login dengan Demo Account (Lebih Cepat)

**Step 1:** Insert Demo Customer ke Database

Buka **phpMyAdmin** atau **MySQL Command Line**:
```
http://localhost/phpmyadmin
```

**Step 2:** Pilih database `elegant_shoes_db`

**Step 3:** Klik tab **SQL**

**Step 4:** Copy-paste query berikut, lalu klik **Go**:

```sql
INSERT INTO customers (name, email, phone, password, address, city, province, postal_code, country, status, email_verified) 
VALUES (
    'Customer Demo',
    'customer@example.com',
    '081234567890',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Jl. Contoh No. 123',
    'Jakarta',
    'DKI Jakarta',
    '12345',
    'Indonesia',
    'active',
    1
);
```

**Step 5:** Buka halaman login:
```
http://localhost/project/login.php
```

**Step 6:** Login dengan credentials:
- **Email:** `customer@example.com`
- **Password:** `password`

**Step 7:** Klik "Login" → Otomatis masuk ke shop.php! ✅

---

## 📋 Flow Lengkap: Login → Shop

```
┌─────────────────────┐
│  index.html         │
│  (Homepage)         │
└──────────┬──────────┘
           │
           ▼
   ┌───────────────┐
   │  Klik Login   │
   └───────┬───────┘
           │
           ▼
┌─────────────────────┐
│  login.php          │
│  - Input email      │
│  - Input password   │
│  - Klik Login       │
└──────────┬──────────┘
           │
           ▼
   ┌───────────────┐
   │  Validasi     │
   │  Database     │
   └───────┬───────┘
           │
           ▼ (Login Berhasil)
┌─────────────────────┐
│  Set Session:       │
│  - customer_id      │
│  - customer_name    │
│  - customer_email   │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Redirect to:       │
│  shop.php           │ ← UPDATED!
└──────────┬──────────┘
           │
           ▼
┌─────────────────────────────────┐
│  shop.php                       │
│  - Lihat semua produk           │
│  - Filter & search              │
│  - Tambah ke keranjang          │
│  - Header menampilkan nama user │
└─────────────────────────────────┘
```

---

## 🎯 Cara Test Complete Flow

### Test Scenario: Customer Shopping Journey

**1. Registrasi/Login**
```
http://localhost/project/login.php
```
Login dengan:
- Email: `customer@example.com`
- Password: `password`

**2. Browse Produk** (Otomatis masuk ke sini)
```
http://localhost/project/shop.php
```
✅ Header menampilkan: "Halo, Customer Demo!"
✅ Link "Pesanan Saya" muncul
✅ Link "Akun" muncul

**3. Add to Cart**
- Pilih produk
- Klik "Tambah ke Keranjang"
- Lihat badge keranjang bertambah

**4. View Cart**
- Klik icon keranjang di header
```
http://localhost/project/cart.php
```

**5. Checkout**
- Klik "Lanjut ke Pembayaran"
```
http://localhost/project/checkout.php
```
✅ Form auto-fill dengan data customer yang login

**6. Complete Order**
- Isi form (sudah auto-fill)
- Pilih metode pembayaran
- Klik "Buat Pesanan"

**7. Order Success**
```
http://localhost/project/order_success.php
```
✅ Pesanan tersimpan di database `orders`
✅ Stok produk berkurang otomatis

**8. View Order History**
```
http://localhost/project/customer/orders.php
```
✅ Lihat semua pesanan yang pernah dibuat

---

## 🔧 Troubleshooting

### ❌ Error: "Email atau password salah!"

**Penyebab:**
- Customer belum terdaftar di database
- Password salah
- Status customer = 'inactive'

**Solusi:**
1. Pastikan sudah insert demo customer (lihat Metode 2)
2. Atau buat account baru via register.php
3. Check database:
```sql
SELECT * FROM customers WHERE email = 'customer@example.com';
```

---

### ❌ Redirect ke halaman lain, bukan shop.php

**Solusi:**
File `login.php` sudah diupdate. Jika masih redirect ke dashboard:
1. Clear browser cache (Ctrl + Shift + Del)
2. Atau buka Incognito/Private mode
3. Login lagi

---

### ❌ Tidak bisa login (blank page)

**Penyebab:**
- PHP error
- Database tidak konek

**Solusi:**
1. Check XAMPP Apache & MySQL sudah running
2. Check error di browser console (F12)
3. Enable PHP error display di php.ini:
```ini
display_errors = On
error_reporting = E_ALL
```

---

## 📊 Verifikasi Session Customer

Setelah login, session yang tersimpan:

```php
$_SESSION = [
    'customer_id' => 1,              // ID customer dari database
    'customer_name' => 'Customer Demo',
    'customer_email' => 'customer@example.com'
];
```

Halaman yang menggunakan session ini:
- ✅ `shop.php` - Tampilkan nama di header
- ✅ `cart.php` - Link pesanan
- ✅ `checkout.php` - Auto-fill form
- ✅ `customer/orders.php` - Lihat pesanan
- ✅ `customer/dashboard.php` - Dashboard customer

---

## 🎨 Tampilan Header shop.php

**Sebelum Login:**
```
[ Home ] [ Belanja ] [ Login ] [ 🛒 Keranjang ]
```

**Setelah Login:**
```
[ Home ] [ Belanja ] [ Pesanan Saya ] [ Akun ] [ 🛒 Keranjang ]
```

---

## 📝 Quick Commands

### Cek Customer di Database:
```sql
SELECT id, name, email, status, created_at 
FROM customers 
ORDER BY id DESC 
LIMIT 5;
```

### Reset Password Customer:
```sql
-- Set password menjadi "password"
UPDATE customers 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE email = 'customer@example.com';
```

### Aktifkan Customer:
```sql
UPDATE customers 
SET status = 'active' 
WHERE email = 'customer@example.com';
```

---

## 🎯 Summary

### File yang Diupdate:
✅ `login.php` 
   - Line 7: Redirect ke shop.php (jika sudah login)
   - Line 32: Redirect ke shop.php (setelah login berhasil)

### File Baru:
✅ `database/insert_demo_customer.sql` - Insert demo customer
✅ `CARA_LOGIN_CUSTOMER.md` - Dokumentasi ini

### Demo Account:
- **Email:** customer@example.com
- **Password:** password

### URL Penting:
- Login: `http://localhost/project/login.php`
- Register: `http://localhost/project/register.php`
- Shop: `http://localhost/project/shop.php`

---

## ✨ Next Steps

Setelah berhasil login dan masuk ke shop.php:

1. **Browse Produk** - Lihat katalog lengkap
2. **Filter** - Cari berdasarkan kategori
3. **Search** - Cari produk spesifik
4. **Add to Cart** - Tambahkan produk ke keranjang
5. **Checkout** - Proses pemesanan
6. **Track Order** - Lihat status pesanan

---

**Status:** ✅ SIAP DIGUNAKAN!

**Dibuat:** 21 Oktober 2025  
**Updated:** Login redirect ke shop.php



