<?php
session_start();
require_once '../config/database.php';

// Check if customer is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: ../login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Get customer orders
$stmt = $db->prepare("
    SELECT o.*, COUNT(oi.id) as items_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.customer_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$customer_id]);
$orders = $stmt->fetchAll();

// Get customer info
$stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Elegant Shoes</title>
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
        
        .welcome-card {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        }
        
        .welcome-text {
            font-size: 18px;
            font-weight: 600;
            color: #667eea;
        }
        
        .order-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        
        .order-card:hover {
            box-shadow: 0 5px 25px rgba(0,0,0,0.12);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .order-number {
            font-size: 18px;
            font-weight: 700;
            color: #333;
        }
        
        .order-date {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        
        .order-status {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-confirmed {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-processing {
            background: #cfe2ff;
            color: #084298;
        }
        
        .status-shipped {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .status-delivered {
            background: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .order-body {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 20px;
            align-items: center;
        }
        
        .order-info p {
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .order-info strong {
            color: #333;
        }
        
        .order-total {
            text-align: center;
        }
        
        .total-label {
            font-size: 13px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .total-amount {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
        }
        
        .order-actions {
            text-align: right;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            margin: 5px 0;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-outline {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .btn-outline:hover {
            background: #667eea;
            color: white;
        }
        
        .empty-orders {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        }
        
        .empty-orders i {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-orders h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #666;
        }
        
        .empty-orders p {
            color: #999;
            margin-bottom: 30px;
        }
        
        @media (max-width: 768px) {
            .order-body {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .order-total, .order-actions {
                text-align: left;
            }
            
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
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
            <nav class="header-nav">
                <a href="../index.html"><i class="fas fa-home"></i> Home</a>
                <a href="../shop.php"><i class="fas fa-shopping-bag"></i> Belanja</a>
                <a href="orders.php"><i class="fas fa-list"></i> Pesanan Saya</a>
                <a href="dashboard.php"><i class="fas fa-user"></i> Akun</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <div class="welcome-card">
            <div class="welcome-text">
                <i class="fas fa-user-circle"></i> 
                Halo, <?php echo htmlspecialchars($customer['name']); ?>!
            </div>
        </div>
        
        <h1 class="page-title"><i class="fas fa-list-alt"></i> Pesanan Saya</h1>
        
        <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <i class="fas fa-shopping-bag"></i>
                <h3>Belum Ada Pesanan</h3>
                <p>Anda belum pernah melakukan pemesanan</p>
                <a href="../shop.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Mulai Belanja
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-number">
                                <i class="fas fa-receipt"></i> 
                                <?php echo htmlspecialchars($order['order_number']); ?>
                            </div>
                            <div class="order-date">
                                <i class="far fa-calendar"></i> 
                                <?php echo date('d F Y, H:i', strtotime($order['created_at'])); ?> WIB
                            </div>
                        </div>
                        <div class="order-status">
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php 
                                $statuses = [
                                    'pending' => 'Menunggu',
                                    'confirmed' => 'Dikonfirmasi',
                                    'processing' => 'Diproses',
                                    'shipped' => 'Dikirim',
                                    'delivered' => 'Selesai',
                                    'cancelled' => 'Dibatalkan'
                                ];
                                echo $statuses[$order['status']] ?? ucfirst($order['status']);
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="order-body">
                        <div class="order-info">
                            <p><strong><i class="fas fa-box"></i> Produk:</strong> <?php echo $order['items_count']; ?> item</p>
                            <p><strong><i class="fas fa-credit-card"></i> Pembayaran:</strong> 
                                <span class="status-badge status-<?php echo $order['payment_status']; ?>" style="padding: 3px 8px; font-size: 11px;">
                                    <?php 
                                    $payment_statuses = [
                                        'pending' => 'Belum Bayar',
                                        'paid' => 'Lunas',
                                        'failed' => 'Gagal',
                                        'refunded' => 'Refund'
                                    ];
                                    echo $payment_statuses[$order['payment_status']] ?? ucfirst($order['payment_status']);
                                    ?>
                                </span>
                            </p>
                            <p><strong><i class="fas fa-truck"></i> Pengiriman:</strong> 
                                <span class="status-badge status-<?php echo $order['shipping_status']; ?>" style="padding: 3px 8px; font-size: 11px;">
                                    <?php 
                                    $shipping_statuses = [
                                        'pending' => 'Menunggu',
                                        'preparing' => 'Disiapkan',
                                        'shipped' => 'Dikirim',
                                        'delivered' => 'Terkirim',
                                        'returned' => 'Dikembalikan'
                                    ];
                                    echo $shipping_statuses[$order['shipping_status']] ?? ucfirst($order['shipping_status']);
                                    ?>
                                </span>
                            </p>
                        </div>
                        
                        <div class="order-total">
                            <div class="total-label">Total Pembayaran</div>
                            <div class="total-amount">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></div>
                        </div>
                        
                        <div class="order-actions">
                            <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                            <?php if ($order['payment_status'] === 'pending' && $order['status'] === 'pending'): ?>
                                <button class="btn btn-outline" onclick="alert('Fitur upload bukti pembayaran segera hadir!')">
                                    <i class="fas fa-upload"></i> Upload Bukti
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>



