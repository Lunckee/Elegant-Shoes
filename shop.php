<?php
session_start();
require_once 'config/database.php';

// Get products with filters
$category_filter = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

$where_conditions = ["p.status = 'active'"];
$params = [];

if ($category_filter) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

if ($search) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = implode(' AND ', $where_conditions);

// Sorting
$order_clause = match($sort) {
    'price_low' => 'final_price ASC',
    'price_high' => 'final_price DESC',
    'name' => 'p.name ASC',
    default => 'p.created_at DESC'
};

// Get products
$stmt = $db->prepare("
    SELECT p.*, 
           c.name as category_name,
           pi.image_path,
           COALESCE(p.sale_price, p.price) as final_price
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    WHERE $where_clause 
    ORDER BY $order_clause
");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories
$categories = $db->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();

// Get cart count
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Belanja Sepatu - Elegant Shoes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        
        /* Header */
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 28px;
            font-weight: bold;
        }
        
        .header-nav {
            display: flex;
            gap: 30px;
            align-items: center;
        }
        
        .header-nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.3s;
        }
        
        .header-nav a:hover {
            opacity: 0.8;
        }
        
        .cart-icon {
            position: relative;
        }
        
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
        }
        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        /* Search & Filter Bar */
        .filter-bar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .filter-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .search-box {
            flex: 1;
            min-width: 250px;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .filter-box select {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            cursor: pointer;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .product-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background: #f5f5f5;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-category {
            color: #667eea;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        
        .product-name {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #333;
        }
        
        .product-description {
            font-size: 13px;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-price {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .price-current {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
        }
        
        .price-original {
            font-size: 16px;
            color: #999;
            text-decoration: line-through;
        }
        
        .product-stock {
            font-size: 12px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .stock-available {
            color: #27ae60;
            font-weight: 600;
        }
        
        .stock-low {
            color: #f39c12;
            font-weight: 600;
        }
        
        .btn-add-cart {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-add-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-add-cart:active {
            transform: translateY(0);
        }
        
        .no-products {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .no-products i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        /* Alert */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        /* Featured Badge */
        .badge-featured {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #f39c12;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }
        
        .product-card {
            position: relative;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .filter-row {
                flex-direction: column;
            }
            
            .search-box,
            .filter-box {
                width: 100%;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-container">
            <div class="logo">
                <i class="fas fa-shoe-prints"></i> Elegant Shoes
            </div>
            <nav class="header-nav">
                <a href="index.html"><i class="fas fa-home"></i> Home</a>
                <a href="shop.php"><i class="fas fa-shopping-bag"></i> Belanja</a>
                <?php if (isset($_SESSION['customer_id'])): ?>
                    <a href="customer/orders.php"><i class="fas fa-list"></i> Pesanan Saya</a>
                    <a href="customer/dashboard.php"><i class="fas fa-user"></i> Akun</a>
                <?php else: ?>
                    <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                <?php endif; ?>
                <a href="cart.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i> Keranjang
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-badge"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <?php if (isset($_GET['added'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                Produk berhasil ditambahkan ke keranjang!
                <a href="cart.php" style="margin-left: auto; font-weight: 600;">Lihat Keranjang →</a>
            </div>
        <?php endif; ?>
        
        <!-- Filter Bar -->
        <div class="filter-bar">
            <form method="GET" action="shop.php">
                <div class="filter-row">
                    <div class="search-box">
                        <input type="text" name="search" placeholder="Cari sepatu..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="filter-box">
                        <select name="category">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-box">
                        <select name="sort">
                            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                            <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Harga: Rendah ke Tinggi</option>
                            <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Harga: Tinggi ke Rendah</option>
                            <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Nama A-Z</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Products Grid -->
        <?php if (empty($products)): ?>
            <div class="no-products">
                <i class="fas fa-box-open"></i>
                <h3>Tidak ada produk ditemukan</h3>
                <p>Coba gunakan filter atau pencarian yang berbeda.</p>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <?php if ($product['featured']): ?>
                            <span class="badge-featured">★ FEATURED</span>
                        <?php endif; ?>
                        
                        <img src="<?php echo $product['image_path'] ? htmlspecialchars($product['image_path']) : 'assets/img/no-image.svg'; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="product-image">
                        
                        <div class="product-info">
                            <div class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></div>
                            <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="product-description"><?php echo htmlspecialchars($product['short_description'] ?? $product['description'] ?? ''); ?></div>
                            
                            <div class="product-price">
                                <span class="price-current">Rp <?php echo number_format($product['final_price'], 0, ',', '.'); ?></span>
                                <?php if ($product['sale_price']): ?>
                                    <span class="price-original">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-stock">
                                <?php if ($product['stock'] > $product['min_stock']): ?>
                                    <span class="stock-available"><i class="fas fa-check-circle"></i> Stok tersedia (<?php echo $product['stock']; ?>)</span>
                                <?php elseif ($product['stock'] > 0): ?>
                                    <span class="stock-low"><i class="fas fa-exclamation-circle"></i> Stok terbatas (<?php echo $product['stock']; ?>)</span>
                                <?php else: ?>
                                    <span style="color: #e74c3c;"><i class="fas fa-times-circle"></i> Stok habis</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($product['stock'] > 0): ?>
                                <form method="POST" action="add_to_cart.php">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn-add-cart">
                                        <i class="fas fa-shopping-cart"></i> Tambah ke Keranjang
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn-add-cart" disabled style="background: #ccc; cursor: not-allowed;">
                                    Stok Habis
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>



