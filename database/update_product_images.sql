-- =====================================================
-- UPDATE SCRIPT FOR PRODUCT IMAGES FEATURE
-- Elegant Shoes Database
-- =====================================================

-- Pastikan tabel product_images sudah ada (biasanya sudah dari elegant_shoes_database.sql)
-- Script ini untuk memastikan struktur sudah benar

USE elegant_shoes_db;

-- Cek apakah tabel product_images sudah ada
CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(200),
    sort_order INT DEFAULT 0,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Tambah index untuk performa
CREATE INDEX IF NOT EXISTS idx_product_images_product ON product_images(product_id);
CREATE INDEX IF NOT EXISTS idx_product_images_primary ON product_images(is_primary);

-- =====================================================
-- OPTIONAL: Migrate existing product_images data
-- =====================================================

-- Jika Anda memiliki data gambar di folder pictures/ yang ingin dimigrasikan
-- ke product_images table, uncomment dan sesuaikan query berikut:

/*
-- Contoh: Update produk yang sudah ada dengan gambar dari folder pictures/
INSERT INTO product_images (product_id, image_path, alt_text, is_primary, sort_order) 
VALUES 
(1, 'pictures/formal shoes.jpg', 'Classic Oxford Shoes', 1, 0),
(2, 'pictures/brown-leather-shoes.jpg', 'Executive Derby Shoes', 1, 0),
(3, 'pictures/sneakers-shoe-logo-design-illustration-of-trending-youth-footwear-simple-funky-concept-free-vector.png', 'Urban Sneaker', 1, 0),
(4, 'pictures/running shoes.png', 'Speed Runner Pro', 1, 0),
(5, 'pictures/sport/Screenshot 2025-10-19 105045.png', 'Basketball Pro', 1, 0)
ON DUPLICATE KEY UPDATE 
    image_path = VALUES(image_path),
    alt_text = VALUES(alt_text);
*/

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================

-- Cek produk tanpa gambar
SELECT p.id, p.name, p.sku 
FROM products p 
LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
WHERE pi.id IS NULL;

-- Cek semua gambar produk
SELECT p.id, p.name, pi.image_path, pi.is_primary 
FROM products p 
LEFT JOIN product_images pi ON p.id = pi.product_id
ORDER BY p.id, pi.sort_order;

-- Hitung total produk vs produk dengan gambar
SELECT 
    (SELECT COUNT(*) FROM products) as total_products,
    (SELECT COUNT(DISTINCT product_id) FROM product_images WHERE is_primary = 1) as products_with_images;

-- =====================================================
-- CLEANUP QUERIES (Gunakan dengan hati-hati!)
-- =====================================================

-- Hapus semua gambar dari produk tertentu
-- DELETE FROM product_images WHERE product_id = ?;

-- Hapus semua gambar yang bukan primary
-- DELETE FROM product_images WHERE is_primary = 0;

-- Reset semua data product_images (HATI-HATI!)
-- TRUNCATE TABLE product_images;

-- =====================================================
-- MAINTENANCE QUERIES
-- =====================================================

-- Set gambar pertama sebagai primary jika tidak ada primary image
UPDATE product_images pi1
JOIN (
    SELECT product_id, MIN(id) as first_image_id
    FROM product_images
    GROUP BY product_id
    HAVING SUM(is_primary) = 0
) pi2 ON pi1.id = pi2.first_image_id
SET pi1.is_primary = 1;

-- Pastikan hanya ada satu primary image per produk
UPDATE product_images pi
SET is_primary = 0
WHERE is_primary = 1
AND id NOT IN (
    SELECT * FROM (
        SELECT MIN(id) 
        FROM product_images 
        WHERE is_primary = 1 
        GROUP BY product_id
    ) as temp
);



