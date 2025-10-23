<?php
session_start();
require_once 'config/database.php';

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $product_id = $_POST['product_id'] ?? 0;
        
        switch ($action) {
            case 'update':
                $quantity = max(1, (int)($_POST['quantity'] ?? 1));
                if (isset($_SESSION['cart'][$product_id])) {
                    // Check stock
                    if ($quantity <= $_SESSION['cart'][$product_id]['stock']) {
                        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
                    }
                }
                break;
                
            case 'remove':
                if (isset($_SESSION['cart'][$product_id])) {
                    unset($_SESSION['cart'][$product_id]);
                }
                break;
                
            case 'clear':
                $_SESSION['cart'] = [];
                break;
        }
    }
    header('Location: cart.php');
    exit();
}

// Calculate totals
$subtotal = 0;
$total_items = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
        $total_items += $item['quantity'];
    }
}

$tax_rate = 0; // 0% PPN (bisa diubah jadi 11% jika perlu)
$tax = $subtotal * $tax_rate;
$shipping = $subtotal > 0 ? 25000 : 0; // Gratis ongkir jika tidak ada item
$total = $subtotal + $tax + $shipping;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Elegant Shoes</title>
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
        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .page-title {
            font-size: 32px;
            margin-bottom: 30px;
            color: #333;
        }
        
        /* Cart Layout */
        .cart-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }
        
        /* Cart Items */
        .cart-items {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        }
        
        .cart-item {
            display: grid;
            grid-template-columns: 120px 1fr auto;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
            background: #f5f5f5;
        }
        
        .item-details {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .item-name {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }
        
        .item-price {
            font-size: 20px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-btn {
            width: 32px;
            height: 32px;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .quantity-btn:hover {
            background: #667eea;
            color: white;
        }
        
        .quantity-input {
            width: 60px;
            text-align: center;
            padding: 8px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .item-actions {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-end;
        }
        
        .item-total {
            font-size: 22px;
            font-weight: 700;
            color: #333;
        }
        
        .btn-remove {
            color: #e74c3c;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            padding: 8px 12px;
            transition: all 0.3s;
        }
        
        .btn-remove:hover {
            background: #fee;
            border-radius: 5px;
        }
        
        /* Cart Summary */
        .cart-summary {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .summary-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #333;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 15px;
        }
        
        .summary-row.total {
            border-top: 2px solid #f0f0f0;
            padding-top: 15px;
            margin-top: 20px;
            font-size: 20px;
            font-weight: 700;
            color: #667eea;
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #f5f5f5;
            color: #333;
            margin-top: 10px;
            width: 100%;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        
        .btn-clear {
            color: #e74c3c;
            background: #fee;
            padding: 10px 20px;
            font-size: 14px;
            margin-top: 20px;
        }
        
        .empty-cart {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        }
        
        .empty-cart i {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-cart h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #666;
        }
        
        .empty-cart p {
            color: #999;
            margin-bottom: 30px;
        }
        
        /* Responsive */
        @media (max-width: 968px) {
            .cart-layout {
                grid-template-columns: 1fr;
            }
            
            .cart-summary {
                position: static;
            }
        }
        
        @media (max-width: 600px) {
            .cart-item {
                grid-template-columns: 80px 1fr;
                gap: 15px;
            }
            
            .item-image {
                width: 80px;
                height: 80px;
            }
            
            .item-actions {
                grid-column: 2;
                flex-direction: row;
                align-items: center;
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
                <a href="cart.php"><i class="fas fa-shopping-cart"></i> Keranjang</a>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <h1 class="page-title"><i class="fas fa-shopping-cart"></i> Keranjang Belanja</h1>
        
        <?php if (empty($_SESSION['cart'])): ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Keranjang Anda Kosong</h3>
                <p>Belum ada produk yang ditambahkan ke keranjang</p>
                <a href="shop.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Mulai Belanja
                </a>
            </div>
        <?php else: ?>
            <!-- Cart with Items -->
            <div class="cart-layout">
                <!-- Cart Items -->
                <div class="cart-items">
                    <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                        <div class="cart-item">
                            <img src="<?php echo $item['image'] ? htmlspecialchars($item['image']) : 'assets/img/no-image.svg'; ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 class="item-image">
                            
                            <div class="item-details">
                                <div>
                                    <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="item-price">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></div>
                                </div>
                                
                                <div class="quantity-control">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                        <input type="hidden" name="quantity" value="<?php echo max(1, $item['quantity'] - 1); ?>">
                                        <button type="submit" class="quantity-btn" <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>>-</button>
                                    </form>
                                    
                                    <input type="text" class="quantity-input" value="<?php echo $item['quantity']; ?>" readonly>
                                    
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                        <input type="hidden" name="quantity" value="<?php echo $item['quantity'] + 1; ?>">
                                        <button type="submit" class="quantity-btn" <?php echo $item['quantity'] >= $item['stock'] ? 'disabled' : ''; ?>>+</button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="item-actions">
                                <div class="item-total">Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></div>
                                
                                <form method="POST">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                    <button type="submit" class="btn-remove">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="btn btn-clear">
                            <i class="fas fa-trash-alt"></i> Kosongkan Keranjang
                        </button>
                    </form>
                </div>
                
                <!-- Cart Summary -->
                <div class="cart-summary">
                    <div class="summary-title">Ringkasan Belanja</div>
                    
                    <div class="summary-row">
                        <span>Subtotal (<?php echo $total_items; ?> item)</span>
                        <span>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></span>
                    </div>
                    
                    <?php if ($tax > 0): ?>
                    <div class="summary-row">
                        <span>Pajak (<?php echo ($tax_rate * 100); ?>%)</span>
                        <span>Rp <?php echo number_format($tax, 0, ',', '.'); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="summary-row">
                        <span>Ongkos Kirim</span>
                        <span>Rp <?php echo number_format($shipping, 0, ',', '.'); ?></span>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                    </div>
                    
                    <a href="checkout.php" class="btn btn-primary">
                        <i class="fas fa-credit-card"></i> Lanjut ke Pembayaran
                    </a>
                    
                    <a href="shop.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Lanjut Belanja
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>



