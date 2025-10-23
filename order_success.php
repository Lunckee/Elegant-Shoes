<?php
session_start();
require_once 'config/database.php';

// Check if there's a recent order
if (!isset($_SESSION['last_order_id'])) {
    header('Location: shop.php');
    exit();
}

$order_id = $_SESSION['last_order_id'];
$order_number = $_SESSION['last_order_number'];

// Get order details
$stmt = $db->prepare("
    SELECT o.*, c.name as customer_name, c.email as customer_email,
           sa.address, sa.city, sa.province, sa.postal_code,
           p.payment_method
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    JOIN shipping_addresses sa ON o.id = sa.order_id
    JOIN payments p ON o.id = p.order_id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

// Get order items
$stmt = $db->prepare("
    SELECT oi.*, p.name as product_name, pi.image_path
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

// Clear session order data
unset($_SESSION['last_order_id']);
unset($_SESSION['last_order_number']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - Elegant Shoes</title>
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
            text-align: center;
        }
        
        .logo {
            font-size: 28px;
            font-weight: bold;
        }
        
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .success-card {
            background: white;
            border-radius: 15px;
            padding: 50px;
            text-align: center;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .success-icon {
            font-size: 80px;
            color: #27ae60;
            margin-bottom: 20px;
            animation: scaleIn 0.5s ease;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }
        
        .success-title {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }
        
        .success-subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }
        
        .order-number {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .order-number-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .order-number-value {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
        }
        
        .order-details {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            color: #666;
            font-weight: 500;
        }
        
        .detail-value {
            font-weight: 600;
            color: #333;
        }
        
        .order-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            background: #f5f5f5;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .item-qty {
            font-size: 14px;
            color: #666;
        }
        
        .item-price {
            font-weight: 700;
            color: #667eea;
        }
        
        .total-amount {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .total-label {
            font-size: 18px;
            font-weight: 600;
        }
        
        .total-value {
            font-size: 28px;
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
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            margin: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #f5f5f5;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        
        .action-buttons {
            text-align: center;
            margin-top: 30px;
        }
        
        .payment-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .payment-info-title {
            font-weight: 700;
            margin-bottom: 10px;
            color: #856404;
        }
        
        .payment-info-text {
            font-size: 14px;
            color: #856404;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        @media (max-width: 600px) {
            .success-card {
                padding: 30px 20px;
            }
            
            .success-title {
                font-size: 24px;
            }
            
            .order-details {
                padding: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <i class="fas fa-shoe-prints"></i> Elegant Shoes
            </div>
        </div>
    </header>
    
    <div class="container">
        <!-- Success Message -->
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1 class="success-title">Pesanan Berhasil Dibuat!</h1>
            <p class="success-subtitle">Terima kasih atas pesanan Anda. Kami akan segera memprosesnya.</p>
            
            <div class="order-number">
                <div class="order-number-label">Nomor Pesanan Anda:</div>
                <div class="order-number-value"><?php echo htmlspecialchars($order_number); ?></div>
            </div>
            
            <p style="color: #666; font-size: 14px;">
                Kami telah mengirimkan konfirmasi pesanan ke email: <strong><?php echo htmlspecialchars($order['customer_email']); ?></strong>
            </p>
        </div>
        
        <!-- Order Details -->
        <div class="order-details">
            <div class="section-title"><i class="fas fa-info-circle"></i> Detail Pesanan</div>
            
            <div class="detail-row">
                <span class="detail-label">Status Pesanan:</span>
                <span class="detail-value">
                    <span class="status-badge status-pending">Menunggu Pembayaran</span>
                </span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Metode Pembayaran:</span>
                <span class="detail-value">
                    <?php 
                    $payment_methods = [
                        'bank_transfer' => 'Transfer Bank',
                        'e_wallet' => 'E-Wallet',
                        'credit_card' => 'Kartu Kredit',
                        'cod' => 'Cash on Delivery'
                    ];
                    echo $payment_methods[$order['payment_method']] ?? $order['payment_method'];
                    ?>
                </span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Tanggal Pesanan:</span>
                <span class="detail-value"><?php echo date('d F Y, H:i', strtotime($order['created_at'])); ?> WIB</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Alamat Pengiriman:</span>
                <span class="detail-value" style="text-align: right; max-width: 60%;">
                    <?php echo htmlspecialchars($order['address']); ?><br>
                    <?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['province']); ?> <?php echo htmlspecialchars($order['postal_code']); ?>
                </span>
            </div>
        </div>
        
        <!-- Order Items -->
        <div class="order-details">
            <div class="section-title"><i class="fas fa-box"></i> Produk yang Dipesan</div>
            
            <?php foreach ($items as $item): ?>
                <div class="order-item">
                    <img src="<?php echo $item['image_path'] ? htmlspecialchars($item['image_path']) : 'assets/img/no-image.svg'; ?>" 
                         alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                         class="item-image">
                    <div class="item-info">
                        <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                        <div class="item-qty">Jumlah: <?php echo $item['quantity']; ?> pcs</div>
                        <div class="item-price">@ Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></div>
                    </div>
                    <div class="item-price">
                        Rp <?php echo number_format($item['total'], 0, ',', '.'); ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="total-amount">
                <span class="total-label">Total Pembayaran:</span>
                <span class="total-value">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></span>
            </div>
            
            <?php if ($order['payment_method'] === 'bank_transfer'): ?>
            <div class="payment-info">
                <div class="payment-info-title"><i class="fas fa-university"></i> Informasi Pembayaran</div>
                <div class="payment-info-text">
                    <p style="margin-bottom: 10px;">Silakan transfer ke rekening berikut:</p>
                    <strong>Bank BCA: 1234567890</strong><br>
                    <strong>a.n. Elegant Shoes</strong><br>
                    <p style="margin-top: 10px; font-size: 13px;">
                        Setelah transfer, mohon upload bukti pembayaran melalui halaman pesanan Anda.
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Action Buttons -->
        <div class="action-buttons">
            <?php if (isset($_SESSION['customer_id'])): ?>
                <a href="customer/orders.php" class="btn btn-primary">
                    <i class="fas fa-list"></i> Lihat Semua Pesanan
                </a>
            <?php endif; ?>
            <a href="shop.php" class="btn btn-secondary">
                <i class="fas fa-shopping-bag"></i> Lanjut Belanja
            </a>
            <a href="index.html" class="btn btn-secondary">
                <i class="fas fa-home"></i> Kembali ke Home
            </a>
        </div>
    </div>
</body>
</html>



