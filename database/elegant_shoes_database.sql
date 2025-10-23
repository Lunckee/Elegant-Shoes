-- =====================================================
-- ELEGANT SHOES DATABASE STRUCTURE
-- =====================================================

CREATE DATABASE IF NOT EXISTS elegant_shoes_db;
USE elegant_shoes_db;

-- =====================================================
-- TABLES CREATION
-- =====================================================

-- 1. Admin Users Table
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'admin', 'manager') DEFAULT 'admin',
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Categories Table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 3. Products Table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) NULL,
    sku VARCHAR(100) UNIQUE NOT NULL,
    stock INT DEFAULT 0,
    min_stock INT DEFAULT 5,
    weight DECIMAL(8,2) DEFAULT 0,
    dimensions VARCHAR(100),
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

-- 4. Product Images Table
CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(200),
    sort_order INT DEFAULT 0,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- 5. Customers Table
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

-- 6. Orders Table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    shipping_status ENUM('pending', 'preparing', 'shipped', 'delivered', 'returned') DEFAULT 'pending',
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    shipping_cost DECIMAL(10,2) DEFAULT 0,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    notes TEXT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivered_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- 7. Order Items Table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- 8. Shipping Addresses Table
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

-- 9. Payments Table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method ENUM('bank_transfer', 'credit_card', 'e_wallet', 'cod') NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    amount DECIMAL(10,2) NOT NULL,
    transaction_id VARCHAR(100),
    payment_proof VARCHAR(255),
    payment_date TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- 10. Reviews Table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    customer_id INT NOT NULL,
    order_id INT,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(200),
    comment TEXT,
    images JSON,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
);

-- 11. Coupons Table
CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('percentage', 'fixed') NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    minimum_amount DECIMAL(10,2) DEFAULT 0,
    maximum_discount DECIMAL(10,2),
    usage_limit INT,
    used_count INT DEFAULT 0,
    start_date DATETIME,
    end_date DATETIME,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 12. Website Settings Table
CREATE TABLE website_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 13. Activity Logs Table
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE SET NULL
);

-- =====================================================
-- INDEXES
-- =====================================================

CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_products_featured ON products(featured);
CREATE INDEX idx_orders_customer ON orders(customer_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_date ON orders(order_date);
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_reviews_product ON reviews(product_id);
CREATE INDEX idx_reviews_status ON reviews(status);

-- =====================================================
-- SAMPLE DATA
-- =====================================================

-- Insert Admin User
INSERT INTO admin_users (username, email, password, full_name, role) VALUES
('admin', 'admin@elegantshoes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Administrator', 'super_admin'),
('manager', 'manager@elegantshoes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Store Manager', 'manager');

-- Insert Categories
INSERT INTO categories (name, slug, description, status) VALUES
('Formal Shoes', 'formal-shoes', 'Sepatu formal untuk acara penting dan kantor', 'active'),
('Casual Shoes', 'casual-shoes', 'Sepatu kasual untuk aktivitas sehari-hari', 'active'),
('Running Shoes', 'running-shoes', 'Sepatu lari dengan teknologi terbaru', 'active'),
('Sport Shoes', 'sport-shoes', 'Sepatu olahraga untuk berbagai aktivitas', 'active');

-- Insert Website Settings
INSERT INTO website_settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'Elegant Shoes', 'text', 'Nama website'),
('site_email', 'info@elegantshoes.com', 'text', 'Email utama website'),
('site_phone', '+62 859 0427 7936', 'text', 'Nomor telepon utama'),
('site_address', 'Jl. Fashion Street No. 123, Jakarta Pusat, 10110', 'text', 'Alamat utama'),
('currency', 'IDR', 'text', 'Mata uang default'),
('tax_rate', '11', 'number', 'Persentase pajak'),
('shipping_cost', '25000', 'number', 'Biaya pengiriman default'),
('min_order_amount', '100000', 'number', 'Minimum jumlah pesanan');

-- Insert Sample Products
INSERT INTO products (category_id, name, slug, description, short_description, price, sku, stock, color, size, material, brand, status, featured) VALUES
(1, 'Classic Oxford', 'classic-oxford', 'Sepatu Oxford klasik dengan desain timeless. Cocok untuk acara formal dan kantor.', 'Sepatu Oxford klasik dengan desain timeless', 1299000, 'FO-001', 10, 'Black', '40-45', 'Genuine Leather', 'Elegant', 'active', 1),
(1, 'Executive Derby', 'executive-derby', 'Sepatu Derby elegan dengan finishing premium. Ideal untuk eksekutif dan profesional.', 'Sepatu Derby elegan dengan finishing premium', 1499000, 'FO-002', 8, 'Brown', '40-45', 'Premium Leather', 'Elegant', 'active', 1),
(2, 'Urban Sneaker', 'urban-sneaker', 'Sneaker urban dengan desain minimalis. Perfect untuk jalan-jalan santai dan hangout.', 'Sneaker urban dengan desain minimalis', 699000, 'CA-001', 15, 'White', '38-44', 'Canvas', 'Elegant', 'active', 1),
(3, 'Speed Runner Pro', 'speed-runner-pro', 'Sepatu lari untuk kecepatan dengan teknologi responsive cushioning. Ideal untuk 5K dan 10K.', 'Sepatu lari dengan teknologi responsive cushioning', 899000, 'RU-001', 25, 'Blue', '38-45', 'Mesh', 'Elegant', 'active', 1),
(4, 'Basketball Pro', 'basketball-pro', 'Sepatu basket dengan teknologi ankle support dan cushioning optimal untuk performa maksimal di lapangan.', 'Sepatu basket dengan teknologi ankle support', 1199000, 'SP-001', 5, 'Red', '40-46', 'Synthetic', 'Elegant', 'active', 1);

-- Insert Product Images
INSERT INTO product_images (product_id, image_path, alt_text, is_primary) VALUES
(1, 'pictures/formal-shoes-1.jpg', 'Classic Oxford Shoes', 1),
(2, 'pictures/formal-shoes-2.jpg', 'Executive Derby Shoes', 1),
(3, 'pictures/casual-shoes-1.jpg', 'Urban Sneaker', 1),
(4, 'pictures/running-shoes-1.jpg', 'Speed Runner Pro', 1),
(5, 'pictures/sport/Screenshot 2025-10-19 105045.png', 'Basketball Pro', 1);

-- Insert Sample Coupons
INSERT INTO coupons (code, name, type, value, minimum_amount, usage_limit, start_date, end_date) VALUES
('WELCOME10', 'Welcome Discount', 'percentage', 10, 500000, 100, NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH)),
('SAVE50K', 'Fixed Discount', 'fixed', 50000, 1000000, 50, NOW(), DATE_ADD(NOW(), INTERVAL 2 MONTH));

