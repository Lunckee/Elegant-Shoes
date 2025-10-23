# Testing Checklist - Fitur Upload Gambar Produk

## Pre-Test Setup

- [ ] Database `elegant_shoes_db` sudah running
- [ ] Apache/XAMPP sudah berjalan
- [ ] Login sebagai admin berhasil
- [ ] Folder `uploads/products/` ada dan memiliki permission yang benar

## Test Case 1: Upload Gambar Baru (Create Product)

**Steps:**
1. Buka `http://localhost/project/admin/products.php`
2. Klik tombol "Tambah Produk"
3. Upload gambar (pastikan < 5MB, format JPG/PNG/WEBP)
4. Isi semua field wajib:
   - Nama Produk: "Test Product"
   - SKU: "TEST-001"
   - Kategori: Pilih salah satu
   - Harga: 100000
   - Stok: 10
5. Klik "Simpan"

**Expected Result:**
- [ ] Preview gambar muncul setelah memilih file
- [ ] Produk berhasil tersimpan
- [ ] Redirect ke products.php dengan pesan sukses
- [ ] Gambar produk muncul di tabel daftar produk
- [ ] File gambar tersimpan di folder `uploads/products/`
- [ ] Record tersimpan di tabel `product_images` dengan `is_primary = 1`

## Test Case 2: Update Gambar Produk (Edit Product)

**Steps:**
1. Dari daftar produk, klik tombol "Edit" pada produk yang baru dibuat
2. Modal edit terbuka dan menampilkan gambar saat ini
3. Pilih gambar baru
4. Klik "Simpan"

**Expected Result:**
- [ ] Gambar lama muncul di "Gambar saat ini"
- [ ] Preview gambar baru muncul setelah memilih file
- [ ] Update berhasil dengan pesan sukses
- [ ] Gambar lama terhapus dari folder `uploads/products/`
- [ ] Gambar baru muncul di daftar produk
- [ ] Record di `product_images` ter-update dengan path baru

## Test Case 3: Product Tanpa Gambar

**Steps:**
1. Klik "Tambah Produk"
2. **JANGAN** upload gambar
3. Isi field wajib lainnya
4. Klik "Simpan"

**Expected Result:**
- [ ] Produk berhasil tersimpan
- [ ] Di daftar produk, muncul placeholder "No Image" (SVG)
- [ ] Tidak ada error

## Test Case 4: Validasi File

**Test 4A: File Terlalu Besar**
- Upload file > 5MB
- **Expected:** Error atau upload gagal

**Test 4B: Format Tidak Didukung**
- Upload file .pdf, .doc, atau format lain
- **Expected:** File tidak ter-upload atau ada pesan error

**Test 4C: Format Didukung**
- Upload JPG: ✅
- Upload PNG: ✅
- Upload WEBP: ✅

## Test Case 5: Hapus Produk dengan Gambar

**Steps:**
1. Pilih produk yang memiliki gambar
2. Klik tombol "Delete"
3. Konfirmasi penghapusan

**Expected Result:**
- [ ] Produk terhapus dari database
- [ ] File gambar terhapus dari folder `uploads/products/`
- [ ] Record di `product_images` terhapus (CASCADE)

## Test Case 6: Multiple Products

**Steps:**
1. Tambah 5 produk dengan gambar berbeda
2. Lihat daftar produk

**Expected Result:**
- [ ] Semua 5 produk muncul dengan gambar masing-masing
- [ ] Tidak ada gambar yang tercampur
- [ ] Performa loading tetap baik

## Test Case 7: Edit Tanpa Mengubah Gambar

**Steps:**
1. Edit produk yang sudah ada
2. **JANGAN** pilih gambar baru
3. Ubah data lain (misalnya harga atau stok)
4. Simpan

**Expected Result:**
- [ ] Data ter-update
- [ ] Gambar lama tetap ada (tidak terhapus)

## Database Verification

Jalankan query berikut di phpMyAdmin/MySQL:

```sql
-- Cek semua product images
SELECT p.id, p.name, pi.image_path, pi.is_primary 
FROM products p 
LEFT JOIN product_images pi ON p.id = pi.product_id
ORDER BY p.id;

-- Cek file yang tidak memiliki record
-- (manual check di folder uploads/products/)
```

## Security Tests

- [ ] Test XSS: Upload file dengan nama `<script>alert('xss')</script>.jpg`
  - **Expected:** Nama file di-sanitize
- [ ] Test Path Traversal: Upload dengan nama `../../etc/passwd.jpg`
  - **Expected:** Path di-sanitize
- [ ] Test permissions: Akses `uploads/products/` langsung dari browser
  - **Expected:** File bisa diakses (karena perlu ditampilkan)

## Performance Tests

- [ ] Upload 10 produk berturut-turut
  - **Expected:** Tidak ada memory leak atau slowdown
- [ ] Load halaman products.php dengan 50+ produk
  - **Expected:** Load time < 2 detik

## Browser Compatibility

Test di:
- [ ] Google Chrome
- [ ] Mozilla Firefox
- [ ] Microsoft Edge
- [ ] Safari (jika available)

## Responsive Design

- [ ] Mobile view (< 768px)
- [ ] Tablet view (768px - 1024px)
- [ ] Desktop view (> 1024px)

## Regression Tests

Pastikan fitur lama masih berfungsi:
- [ ] Filter by category
- [ ] Filter by status
- [ ] Search products
- [ ] Pagination
- [ ] Delete product (tanpa gambar)
- [ ] Edit product fields lainnya

---

## Bug Tracking

Jika menemukan bug, catat di sini:

| # | Deskripsi | Severity | Status |
|---|-----------|----------|--------|
| 1 | | | |
| 2 | | | |

---

**Tester:** ___________________
**Tanggal:** ___________________
**Status:** [ ] PASS / [ ] FAIL



