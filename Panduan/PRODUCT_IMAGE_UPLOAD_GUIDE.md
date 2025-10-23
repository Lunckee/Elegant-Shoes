# Panduan Fitur Upload Gambar Produk

## Fitur yang Ditambahkan

Fitur upload gambar produk telah berhasil ditambahkan ke halaman `admin/products.php`. Fitur ini memungkinkan Anda untuk:

✅ Upload gambar produk saat menambah produk baru
✅ Update gambar produk saat mengedit produk
✅ Melihat preview gambar sebelum upload
✅ Melihat gambar saat ini ketika mengedit produk
✅ Menampilkan gambar produk di daftar produk

## Cara Menggunakan

### 1. Menambah Produk Baru dengan Gambar

1. Login ke admin panel
2. Buka halaman **Products** dari menu sidebar
3. Klik tombol **"Tambah Produk"**
4. Di modal form yang muncul, Anda akan melihat field **"Gambar Produk"** di bagian atas
5. Klik tombol **"Choose File"** atau drag & drop gambar
6. Preview gambar akan muncul secara otomatis
7. Isi data produk lainnya (Nama, SKU, Kategori, Harga, dll)
8. Klik **"Simpan"**

### 2. Mengedit Produk dan Mengubah Gambar

1. Pada daftar produk, klik tombol **Edit** (ikon pensil) pada produk yang ingin diubah
2. Modal edit akan terbuka dan menampilkan **gambar saat ini** (jika ada)
3. Untuk mengubah gambar, klik **"Choose File"** dan pilih gambar baru
4. Preview gambar baru akan muncul
5. Update data lain jika diperlukan
6. Klik **"Simpan"**

**Catatan:** Gambar lama akan otomatis dihapus ketika Anda upload gambar baru.

### 3. Menghapus Produk

Ketika produk dihapus, semua gambar yang terkait dengan produk tersebut juga akan otomatis dihapus dari server.

## Spesifikasi Gambar

### Format yang Didukung
- JPG / JPEG
- PNG
- WEBP

### Batasan
- **Ukuran maksimal:** 5 MB
- **Dimensi rekomendasi:** 800x800 px (rasio 1:1) untuk tampilan optimal
- **Resolusi minimum:** 400x400 px

## Lokasi Penyimpanan

Semua gambar produk disimpan di:
```
/uploads/products/
```

Format nama file:
```
[slug-produk]-[timestamp].[ekstensi]
```

Contoh:
```
classic-oxford-1729512345.jpg
urban-sneaker-1729512456.png
```

## Struktur Database

Gambar disimpan di tabel `product_images` dengan struktur:

| Field | Type | Keterangan |
|-------|------|------------|
| id | INT | Primary key |
| product_id | INT | Foreign key ke tabel products |
| image_path | VARCHAR(255) | Path relatif ke file gambar |
| alt_text | VARCHAR(200) | Text alternatif untuk SEO |
| is_primary | BOOLEAN | Menandakan gambar utama |
| sort_order | INT | Urutan tampilan gambar |

## Fitur Keamanan

✅ Validasi tipe file (hanya gambar yang diizinkan)
✅ Validasi ukuran file (maksimal 5MB)
✅ Nama file di-sanitize untuk keamanan
✅ File lama dihapus otomatis saat update
✅ Semua file gambar dihapus saat produk dihapus

## Troubleshooting

### Gambar tidak muncul setelah upload
1. Pastikan folder `/uploads/products/` memiliki permission yang benar (777 atau 755)
2. Periksa apakah file benar-benar terupload di folder tersebut
3. Periksa console browser untuk error JavaScript

### Upload gagal
1. Pastikan ukuran file tidak melebihi 5MB
2. Pastikan format file adalah JPG, PNG, atau WEBP
3. Periksa PHP upload settings di `php.ini`:
   - `upload_max_filesize = 5M`
   - `post_max_size = 8M`
   - `file_uploads = On`

### Gambar terdistorsi
- Upload gambar dengan rasio 1:1 (kotak) untuk hasil terbaik
- Gunakan resolusi minimal 400x400 px

## Tips

💡 **Optimasi Gambar:** Kompres gambar sebelum upload untuk performa website yang lebih baik
💡 **Konsistensi:** Gunakan background putih atau transparan untuk tampilan yang konsisten
💡 **SEO:** Alt text otomatis diisi dengan nama produk, bagus untuk SEO
💡 **Multiple Views:** Fitur ini support multi-image per produk melalui tabel product_images

## Update Selanjutnya (Potensial)

Fitur yang bisa dikembangkan lebih lanjut:
- [ ] Multiple image upload (galeri produk)
- [ ] Drag & drop untuk reorder gambar
- [ ] Image cropping/editing langsung di browser
- [ ] Kompres otomatis saat upload
- [ ] Watermark otomatis
- [ ] Zoom image pada preview

---

**Dibuat:** 21 Oktober 2025
**Versi:** 1.0
**Developer:** Elegant Shoes Development Team



