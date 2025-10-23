# 🛒 Panduan Lengkap Sistem Belanja Elegant Shoes

## 📋 Daftar Isi

1. [Overview Sistem](#overview-sistem)
2. [Fitur untuk Customer](#fitur-untuk-customer)
3. [Fitur untuk Admin](#fitur-untuk-admin)
4. [Alur Pemesanan](#alur-pemesanan)
5. [Struktur File](#struktur-file)
6. [Database Schema](#database-schema)
7. [Cara Penggunaan](#cara-penggunaan)
8. [Troubleshooting](#troubleshooting)

---

## 🎯 Overview Sistem

Sistem belanja Elegant Shoes adalah platform e-commerce lengkap yang terdiri dari:

### Untuk Customer:
- ✅ Katalog produk dengan filter & search
- ✅ Shopping cart (keranjang belanja)
- ✅ Checkout & pemesanan
- ✅ Riwayat pesanan
- ✅ Detail tracking pesanan

### Untuk Admin:
- ✅ Management produk (tambah, edit, hapus)
- ✅ Upload gambar produk
- ✅ Management pesanan
- ✅ Laporan penjualan

---

## 🛍️ Fitur untuk Customer

### 1. **Halaman Belanja (shop.php)**

**URL:** `http://localhost/project/shop.php`

**Fitur:**
- Menampilkan semua produk aktif dengan gambar
- Filter berdasarkan kategori
- Pencarian produk
- Sorting (terbaru, harga rendah-tinggi, nama)
- Informasi stok realtime
- Badge "Featured" untuk produk unggulan
- Tombol "Tambah ke Keranjang"

**Screenshot Fitur:**
```
┌─────────────────────────────────────────────────┐
│  [Search Box] [Category Filter] [Sort] [Filter] │
└─────────────────────────────────────────────────┘

┌───────┐  ┌───────┐  ┌───────┐  ┌───────┐
│ ⭐    │  │       │  │       │  │       │
│ [IMG] │  │ [IMG] │  │ [IMG] │  │ [IMG] │
│ Name  │  │ Name  │  │ Name  │  │ Name  │
│ Price │  │ Price │  │ Price │  │ Price │
│ [🛒]  │  │ [🛒]  │  │ [🛒]  │  │ [🛒]  │
└───────┘  └───────┘  └───────┘  └───────┘
```

### 2. **Keranjang Belanja (cart.php)**

**URL:** `http://localhost/project/cart.php`

**Fitur:**
- Lihat semua item di keranjang
- Update jumlah (+ / -)
- Hapus item
- Kosongkan keranjang
- Ringkasan total pembayaran
- Button "Lanjut ke Pembayaran"

**Data yang Disimpan:**
```php
$_SESSION['cart'] = [
    'product_id' => [
        'product_id' => 1,
        'name' => 'Classic Oxford',
        'price' => 1299000,
        'image' => 'uploads/products/...',
        'quantity' => 2,
        'stock' => 10
    ]
];
```

### 3. **Checkout (checkout.php)**

**URL:** `http://localhost/project/checkout.php`

**Formulir:**
- Informasi pengiriman (nama, email, telpon, alamat)
- Kota, provinsi, kode pos
- Catatan pesanan (opsional)
- Metode pembayaran (Transfer Bank, E-Wallet, Kartu Kredit, COD)
- Ringkasan pesanan
- Total pembayaran

**Validasi:**
- Semua field wajib terisi
- Email format valid
- Keranjang tidak boleh kosong

### 4. **Proses Order (process_order.php)**

**Backend Processing:**
1. Validasi cart & form data
2. Create/check customer record
3. Generate order number (ORD-YYYYMMDD-XXXXXX)
4. Simpan ke tabel `orders`
5. Simpan items ke `order_items`
6. Update stok produk (reduce)
7. Simpan alamat ke `shipping_addresses`
8. Buat record `payments`
9. Clear cart
10. Redirect ke halaman sukses

**Transaction Safety:**
```php
try {
    $db->beginTransaction();
    // ... all operations
    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    // redirect with error
}
```

### 5. **Order Success (order_success.php)**

**URL:** `http://localhost/project/order_success.php`

**Tampilan:**
- ✅ Icon sukses
- Nomor pesanan
- Detail pesanan
- List produk
- Total pembayaran
- Informasi pembayaran (jika transfer bank)
- Link ke riwayat pesanan

### 6. **Riwayat Pesanan (customer/orders.php)**

**URL:** `http://localhost/project/customer/orders.php`

**Fitur:**
- List semua pesanan customer
- Status pesanan (pending, confirmed, processing, shipped, delivered, cancelled)
- Status pembayaran (pending, paid, failed, refunded)
- Status pengiriman (pending, preparing, shipped, delivered, returned)
- Total per pesanan
- Button "Detail" & "Upload Bukti"

### 7. **Detail Pesanan (customer/order_detail.php)**

**URL:** `http://localhost/project/customer/order_detail.php?id=X`

**Informasi:**
- Nomor & tanggal pesanan
- List produk dengan gambar
- Alamat pengiriman lengkap
- Status pesanan, pembayaran, metode
- Ringkasan pembayaran
- Tracking pengiriman (visual timeline)

---

## 👨‍💼 Fitur untuk Admin

### 1. **Management Produk (admin/products.php)**

**URL:** `http://localhost/project/admin/products.php`

**Fitur:**
✅ **Tambah Produk:**
- Upload gambar produk
- Input nama, SKU, kategori
- Harga normal & harga diskon
- Stok & minimum stok
- Warna, ukuran, material, brand
- Deskripsi singkat & lengkap
- Status (active, inactive, draft)
- Featured flag
- Meta title & description untuk SEO

✅ **Edit Produk:**
- Update semua field
- Ganti gambar (gambar lama otomatis terhapus)
- Tampilkan gambar saat ini

✅ **Hapus Produk:**
- Konfirmasi sebelum hapus
- Gambar ikut terhapus dari server
- Cascade delete ke product_images

**Filter & Search:**
- Cari berdasarkan nama/SKU
- Filter kategori
- Filter status
- Pagination (10 item per halaman)

### 2. **Management Pesanan (admin/orders.php)**

**URL:** `http://localhost/project/admin/orders.php`

**Fitur:**
- List semua pesanan
- Update status pesanan
- Update status pembayaran
- Lihat detail lengkap
- Filter berdasarkan status
- Export ke Excel/PDF (potensial)

---

## 🔄 Alur Pemesanan

### Customer Journey:

```
1. Browse Produk (shop.php)
   ↓
2. Klik "Tambah ke Keranjang"
   ↓
3. Lihat Keranjang (cart.php)
   ↓ Update qty / Hapus item (optional)
4. Klik "Lanjut ke Pembayaran"
   ↓
5. Isi Form Checkout (checkout.php)
   ↓ Pilih metode pembayaran
6. Klik "Buat Pesanan"
   ↓
7. Process Order (process_order.php)
   ├─ Simpan ke database
   ├─ Update stok
   └─ Clear cart
   ↓
8. Halaman Sukses (order_success.php)
   ↓
9. Lihat Riwayat (customer/orders.php)
   ↓
10. Upload Bukti Bayar (jika transfer)
```

### Admin Journey:

```
1. Tambah Produk (admin/products.php)
   ↓
2. Upload Gambar
   ↓
3. Set Harga & Stok
   ↓
4. Publish (status: active)
   ↓
   
[Customer Order]
   ↓
5. Terima Order (admin/orders.php)
   ↓
6. Konfirmasi Pembayaran
   ↓
7. Update Status → Processing
   ↓
8. Siapkan Pesanan
   ↓
9. Update Status → Shipped
   ↓
10. Update Status → Delivered
```

---

## 📁 Struktur File

```
project/
│
├── 🛒 CUSTOMER PAGES
│   ├── shop.php                    # Katalog produk
│   ├── cart.php                    # Keranjang belanja
│   ├── checkout.php                # Form checkout
│   ├── add_to_cart.php            # Add item to cart
│   ├── process_order.php          # Process & save order
│   ├── order_success.php          # Order confirmation
│   │
│   └── customer/
│       ├── orders.php              # Riwayat pesanan
│       ├── order_detail.php        # Detail pesanan
│       ├── dashboard.php           # Customer dashboard
│       └── profile.php             # Edit profile
│
├── 👨‍💼 ADMIN PAGES
│   └── admin/
│       ├── products.php            # Management produk
│       ├── orders.php              # Management pesanan
│       ├── dashboard.php           # Admin dashboard
│       └── reports.php             # Laporan
│
├── 🗄️ DATABASE
│   └── database/
│       ├── elegant_shoes_database.sql
│       └── update_product_images.sql
│
├── ⚙️ CONFIG
│   ├── config/
│   │   └── database.php
│   └── includes/
│       └── auth.php
│
├── 🖼️ ASSETS
│   ├── assets/
│   │   └── img/
│   │       └── no-image.svg
│   │
│   └── uploads/
│       └── products/               # Uploaded product images
│
└── 📚 DOCUMENTATION
    ├── PANDUAN_SISTEM_BELANJA.md  # File ini
    ├── PRODUCT_IMAGE_UPLOAD_GUIDE.md
    └── IMPLEMENTASI_FITUR_UPLOAD_GAMBAR.md
```

---

## 🗄️ Database Schema

### Tabel: **orders**

```sql
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,      -- ORD-20251021-ABC123
    customer_id INT NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'),
    payment_status ENUM('pending', 'paid', 'failed', 'refunded'),
    shipping_status ENUM('pending', 'preparing', 'shipped', 'delivered', 'returned'),
    subtotal DECIMAL(10,2) NOT NULL,               -- Total harga produk
    tax_amount DECIMAL(10,2) DEFAULT 0,            -- Pajak (PPN)
    shipping_cost DECIMAL(10,2) DEFAULT 0,         -- Ongkir
    discount_amount DECIMAL(10,2) DEFAULT 0,       -- Diskon
    total_amount DECIMAL(10,2) NOT NULL,           -- Grand total
    notes TEXT,                                     -- Catatan customer
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivered_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);
```

### Tabel: **order_items**

```sql
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,                         -- Jumlah yang dibeli
    price DECIMAL(10,2) NOT NULL,                  -- Harga saat dibeli
    total DECIMAL(10,2) NOT NULL,                  -- price * quantity
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

### Tabel: **shipping_addresses**

```sql
CREATE TABLE shipping_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    postal_code VARCHAR(10) NOT NULL,
    country VARCHAR(100) DEFAULT 'Indonesia',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);
```

### Tabel: **payments**

```sql
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method ENUM('bank_transfer', 'credit_card', 'e_wallet', 'cod') NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    amount DECIMAL(10,2) NOT NULL,
    transaction_id VARCHAR(100),                   -- ID transaksi dari payment gateway
    payment_proof VARCHAR(255),                    -- Upload bukti transfer
    payment_date TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);
```

### Tabel: **products**

```sql
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) NULL,                 -- Harga diskon
    sku VARCHAR(100) UNIQUE NOT NULL,
    stock INT DEFAULT 0,                            -- Updated saat order
    min_stock INT DEFAULT 5,
    color VARCHAR(50),
    size VARCHAR(20),
    material VARCHAR(100),
    brand VARCHAR(100),
    status ENUM('active', 'inactive', 'draft') DEFAULT 'active',
    featured BOOLEAN DEFAULT FALSE,
    meta_title VARCHAR(200),
    meta_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);
```

### Tabel: **product_images**

```sql
CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,              -- uploads/products/xxx.jpg
    alt_text VARCHAR(200),
    sort_order INT DEFAULT 0,
    is_primary BOOLEAN DEFAULT FALSE,              -- Gambar utama
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

---

## 🚀 Cara Penggunaan

### Setup Awal

1. **Import Database:**
   ```sql
   mysql -u root -p elegant_shoes_db < database/elegant_shoes_database.sql
   ```

2. **Konfigurasi Database:**
   Edit `config/database.php`:
   ```php
   private $host = 'localhost';
   private $db_name = 'elegant_shoes_db';
   private $username = 'root';
   private $password = '';
   ```

3. **Set Permission Folder:**
   ```bash
   chmod 755 uploads/products/
   ```

4. **Login Admin:**
   - URL: `http://localhost/project/admin/login.php`
   - Username: `admin`
   - Password: `password` (lihat di database)

### Cara Customer Belanja

1. **Buka Shop:**
   ```
   http://localhost/project/shop.php
   ```

2. **Pilih Produk:**
   - Browse atau cari produk
   - Klik "Tambah ke Keranjang"

3. **Checkout:**
   - Klik icon keranjang di header
   - Review items
   - Klik "Lanjut ke Pembayaran"

4. **Isi Form:**
   - Lengkapi data pengiriman
   - Pilih metode pembayaran
   - Klik "Buat Pesanan"

5. **Konfirmasi:**
   - Catat nomor pesanan
   - Upload bukti bayar (jika transfer)

### Cara Admin Kelola Pesanan

1. **Login Admin:**
   ```
   http://localhost/project/admin/login.php
   ```

2. **Lihat Pesanan:**
   ```
   http://localhost/project/admin/orders.php
   ```

3. **Update Status:**
   - Konfirmasi pembayaran → status: `confirmed`
   - Siapkan barang → status: `processing`
   - Kirim barang → status: `shipped`
   - Sampai tujuan → status: `delivered`

---

## 🐛 Troubleshooting

### 1. Produk Tidak Muncul

**Penyebab:**
- Status produk = 'inactive' atau 'draft'
- Kategori tidak aktif

**Solusi:**
```sql
UPDATE products SET status = 'active' WHERE id = X;
UPDATE categories SET status = 'active' WHERE id = X;
```

### 2. Gambar Tidak Tampil

**Penyebab:**
- Path salah
- File tidak ada
- Permission folder

**Solusi:**
```bash
# Cek file ada
ls uploads/products/

# Set permission
chmod 755 uploads/products/
chmod 644 uploads/products/*
```

### 3. Order Gagal

**Penyebab:**
- Stok habis
- Database error
- Session expired

**Solusi:**
```sql
# Cek stok
SELECT id, name, stock FROM products WHERE id = X;

# Update stok
UPDATE products SET stock = 10 WHERE id = X;
```

### 4. Cart Hilang

**Penyebab:**
- Session timeout
- Browser clear cache
- Server restart

**Solusi:**
```php
// Extend session lifetime di php.ini atau .htaccess
php_value session.gc_maxlifetime 86400
```

### 5. Checkout Error

**Error:** "Stok tidak mencukupi"

**Solusi:**
```php
// Clear cart dan reload
$_SESSION['cart'] = [];
// Atau update stok produk
```

---

## 📊 Flow Diagram

### Order Processing Flow:

```
┌─────────────┐
│ Add to Cart │
└──────┬──────┘
       │
       ▼
┌─────────────┐     Yes    ┌──────────────┐
│ Cart Empty? ├───────────►│ Show Message │
└──────┬──────┘             └──────────────┘
       │ No
       ▼
┌─────────────┐
│  Checkout   │
└──────┬──────┘
       │
       ▼
┌─────────────┐     No     ┌──────────────┐
│ Form Valid? ├───────────►│ Show Errors  │
└──────┬──────┘             └──────────────┘
       │ Yes
       ▼
┌─────────────┐
│ BEGIN TRANS │
└──────┬──────┘
       │
       ▼
┌─────────────┐     No     ┌──────────────┐
│Stock Enough?├───────────►│  ROLLBACK    │
└──────┬──────┘             └──────────────┘
       │ Yes
       ▼
┌─────────────┐
│ Create Order│
└──────┬──────┘
       │
       ▼
┌─────────────┐
│Update Stock │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│Save Shipping│
└──────┬──────┘
       │
       ▼
┌─────────────┐
│Save Payment │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   COMMIT    │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Clear Cart  │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   Success!  │
└─────────────┘
```

---

## 📈 Tips & Best Practices

### Untuk Admin:

1. **Kelola Stok:**
   - Set minimum stok untuk notifikasi
   - Update stok secara berkala
   - Gunakan fitur "Stok Habis" otomatis

2. **Update Status Pesanan:**
   - Konfirmasi pembayaran dalam 1x24 jam
   - Update tracking secara realtime
   - Komunikasi dengan customer

3. **Optimasi Gambar:**
   - Kompres gambar sebelum upload
   - Gunakan format WebP untuk ukuran lebih kecil
   - Resolusi 800x800px optimal

### Untuk Developer:

1. **Security:**
   - Gunakan prepared statements (sudah implemented)
   - Validasi semua input
   - HTTPS untuk production
   - Sanitize file upload

2. **Performance:**
   - Index database columns yang sering di-query
   - Cache product catalog
   - Optimize images
   - CDN untuk static assets

3. **Backup:**
   - Backup database daily
   - Backup uploads folder
   - Version control (Git)

---

## 🎯 Roadmap / Future Features

### Short Term:
- [ ] Upload bukti pembayaran
- [ ] Email notifikasi otomatis
- [ ] Resi pengiriman dari ekspedisi
- [ ] Rating & review produk

### Long Term:
- [ ] Payment gateway integration (Midtrans, dll)
- [ ] Multiple product images (gallery)
- [ ] Wishlist
- [ ] Product recommendations
- [ ] Discount coupons
- [ ] Loyalty points
- [ ] Mobile app (PWA)

---

## 📞 Support & Contact

**Developer:** Elegant Shoes Development Team  
**Email:** developer@elegantshoes.com  
**Dokumentasi:** Lihat folder `/documentation`  
**Database:** `database/elegant_shoes_database.sql`

---

## 📝 Changelog

### Version 1.0 (21 Oktober 2025)
- ✅ Sistem belanja lengkap
- ✅ Shopping cart
- ✅ Checkout & order processing
- ✅ Customer order history
- ✅ Admin product management
- ✅ Image upload
- ✅ Complete documentation

---

**Dibuat dengan ❤️ oleh Tim Elegant Shoes**

**Status:** ✅ **PRODUCTION READY**



