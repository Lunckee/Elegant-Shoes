# 🚀 Elegant Shoes - Installation Guide

## 📋 Prerequisites

- XAMPP (Apache, MySQL, PHP 7.4+)
- Web Browser
- Text Editor (Optional)

## 🛠️ Installation Steps

### 1. Setup XAMPP
1. **Download XAMPP** dari [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. **Install XAMPP** di komputer Anda
3. **Start Apache dan MySQL** dari XAMPP Control Panel

### 2. Database Setup
1. **Buka phpMyAdmin**: `http://localhost/phpmyadmin`
2. **Import Database**:
   - Klik "Import" tab
   - Pilih file `database/elegant_shoes_database.sql`
   - Klik "Go" untuk import

### 3. File Structure
Pastikan struktur folder seperti ini:
```
project/
├── admin/
│   ├── assets/
│   │   ├── css/
│   │   └── js/
│   ├── includes/
│   ├── login.php
│   ├── dashboard.php
│   ├── products.php
│   ├── orders.php
│   ├── reports.php
│   ├── settings.php
│   └── logout.php
├── config/
│   └── database.php
├── includes/
│   └── auth.php
├── database/
│   └── elegant_shoes_database.sql
├── pictures/
├── index.html
├── formal-shoes.html
├── casual-shoes.html
├── running-shoes.html
├── sport-shoes.html
├── styles.css
└── script.js
```

### 4. Configuration
1. **Edit Database Config** (`config/database.php`):
   ```php
   private $host = 'localhost';
   private $db_name = 'elegant_shoes_db';
   private $username = 'root';
   private $password = ''; // Sesuaikan dengan password MySQL Anda
   ```

### 5. Access the Website
1. **Frontend**: `http://localhost/project/`
2. **Admin Panel**: `http://localhost/project/admin/`

## 🔐 Default Login Credentials

**Admin Login:**
- **Username**: `admin`
- **Password**: `password`

**Manager Login:**
- **Username**: `manager`
- **Password**: `password`

## 📊 Features Overview

### ✅ Admin Dashboard Features
- **Dashboard**: Overview statistics dan charts
- **Product Management**: CRUD untuk produk
- **Order Management**: Kelola pesanan dan status
- **Payment Management**: Kelola pembayaran
- **Shipping Management**: Kelola pengiriman
- **Customer Management**: Data pelanggan
- **Reviews Management**: Ulasan dan komentar
- **Reports & Analytics**: Laporan dan analisis
- **Website Settings**: Pengaturan website
- **Admin Management**: Kelola admin users
- **Activity Logs**: Log aktivitas admin

### ✅ Frontend Features
- **Responsive Design**: Mobile-friendly
- **Product Categories**: 4 kategori sepatu
- **Product Pages**: Halaman khusus per kategori
- **Modern UI**: Clean dan professional design

## 🔧 Troubleshooting

### Database Connection Error
- Pastikan MySQL sudah running di XAMPP
- Cek username/password di `config/database.php`
- Pastikan database `elegant_shoes_db` sudah dibuat

### Admin Login Issues
- Pastikan menggunakan username/password yang benar
- Cek apakah session PHP berfungsi
- Clear browser cache dan cookies

### File Permission Issues
- Pastikan folder `pictures/` memiliki permission write
- Cek permission folder admin dan config

## 📱 Mobile Responsive
Website sudah fully responsive dan dapat diakses dari:
- Desktop
- Tablet
- Mobile Phone

## 🔒 Security Features
- **Password Hashing**: Menggunakan PHP password_hash()
- **Session Management**: Secure session handling
- **SQL Injection Protection**: Prepared statements
- **XSS Protection**: Input sanitization
- **Role-based Access**: Different admin roles

## 📈 Performance Optimization
- **Database Indexing**: Optimized queries
- **Image Optimization**: Responsive images
- **CSS/JS Minification**: Optimized assets
- **Caching**: Browser caching headers

## 🎨 Customization
### Adding New Product Categories
1. Insert ke table `categories`
2. Buat halaman HTML baru
3. Update navigation menu

### Styling Customization
- Edit `admin/assets/css/admin.css` untuk admin panel
- Edit `styles.css` untuk frontend

## 📞 Support
Jika mengalami masalah:
1. Cek error logs di XAMPP
2. Pastikan semua file sudah di-upload dengan benar
3. Verify database connection
4. Check file permissions

## 🚀 Production Deployment
Untuk deployment ke production:
1. **Update database credentials**
2. **Enable HTTPS**
3. **Set proper file permissions**
4. **Configure web server**
5. **Enable caching**
6. **Setup backup system**

---
**Happy Coding! 🎉**
