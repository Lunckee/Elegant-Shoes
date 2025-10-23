<?php
session_start();
require_once '../config/database.php';

// Check if customer is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: ../login.php');
    exit();
}

$order_id = $_GET['id'] ?? 0;
$customer_id = $_SESSION['customer_id'];

// Get order details
$stmt = $db->prepare("
    SELECT o.*, c.name as customer_name, c.email as customer_email,
           sa.name as shipping_name, sa.phone as shipping_phone,
           sa.address, sa.city, sa.province, sa.postal_code, sa.notes as shipping_notes,
           p.payment_method, p.payment_status, p.payment_date
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    JOIN shipping_addresses sa ON o.id = sa.order_id
    JOIN payments p ON o.id = p.order_id
    WHERE o.id = ? AND o.customer_id = ?
");
$stmt->execute([$order_id, $customer_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Get order items
$stmt = $db->prepare("
    SELECT oi.*, p.name as product_name, p.sku, pi.image_path
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?php echo htmlspecialchars($order['order_number']); ?> - Elegant Shoes</title>
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
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .page-title {
            font-size: 32px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .back-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 30px;
            display: inline-block;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .order-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
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
        
        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-processing { background: #cfe2ff; color: #084298; }
        .status-shipped { background: #e2e3e5; color: #383d41; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-paid { background: #d4edda; color: #155724; }
        .status-failed { background: #f8d7da; color: #721c24; }
        
        .order-item {
            display: flex;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            background: #f5f5f5;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .item-sku {
            font-size: 13px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .item-qty {
            font-size: 14px;
            color: #666;
        }
        
        .item-price {
            text-align: right;
        }
        
        .price-label {
            font-size: 13px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .price-value {
            font-size: 20px;
            font-weight: 700;
            color: #667eea;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 15px;
        }
        
        .summary-row.total {
            border-top: 2px solid #f0f0f0;
            padding-top: 15px;
            margin-top: 15px;
            font-size: 20px;
            font-weight: 700;
            color: #667eea;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 100%;
            text-align: center;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .tracking-status {
            position: relative;
            padding-left: 30px;
        }
        
        .tracking-step {
            padding: 15px 0;
            position: relative;
        }
        
        .tracking-step::before {
            content: '';
            position: absolute;
            left: -21px;
            top: 7px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #ddd;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #ddd;
        }
        
        .tracking-step.active::before {
            background: #667eea;
            box-shadow: 0 0 0 2px #667eea;
        }
        
        .tracking-step::after {
            content: '';
            position: absolute;
            left: -16px;
            top: 25px;
            width: 2px;
            height: calc(100% - 10px);
            background: #ddd;
        }
        
        .tracking-step:last-child::after {
            display: none;
        }
        
        .tracking-step.active::after {
            background: #667eea;
        }
        
        .tracking-title {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .tracking-desc {
            font-size: 13px;
            color: #666;
        }
        
        @media (max-width: 968px) {
            .order-layout {
                grid-template-columns: 1fr;
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
                <a href="../shop.php"><i class="fas fa-shopping-bag"></i> Belanja</a>
                <a href="orders.php"><i class="fas fa-list"></i> Pesanan Saya</a>
                <a href="dashboard.php"><i class="fas fa-user"></i> Akun</a>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <a href="orders.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Pesanan
        </a>
        
        <h1 class="page-title">
            <i class="fas fa-receipt"></i> Pesanan #<?php echo htmlspecialchars($order['order_number']); ?>
        </h1>
        <p style="color: #666; margin-bottom: 30px;">
            Dipesan pada: <?php echo date('d F Y, H:i', strtotime($order['created_at'])); ?> WIB
        </p>
        
        <div class="order-layout">
            <!-- Left Column -->
            <div>
                <!-- Order Items -->
                <div class="card">
                    <div class="section-title"><i class="fas fa-box"></i> Produk yang Dipesan</div>
                    
                    <?php foreach ($items as $item): ?>
                        <div class="order-item">
                            <img src="../<?php echo $item['image_path'] ? htmlspecialchars($item['image_path']) : 'assets/img/no-image.svg'; ?>" 
                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                 class="item-image">
                            <div class="item-info">
                                <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <div class="item-sku">SKU: <?php echo htmlspecialchars($item['sku']); ?></div>
                                <div class="item-qty">
                                    <i class="fas fa-cubes"></i> 
                                    Jumlah: <strong><?php echo $item['quantity']; ?> pcs</strong>
                                </div>
                            </div>
                            <div class="item-price">
                                <div class="price-label">Subtotal:</div>
                                <div class="price-value">Rp <?php echo number_format($item['total'], 0, ',', '.'); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Shipping Address -->
                <div class="card">
                    <div class="section-title"><i class="fas fa-map-marker-alt"></i> Alamat Pengiriman</div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Nama Penerima:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($order['shipping_name']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Telepon:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($order['shipping_phone']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Alamat:</span>
                        <span class="detail-value" style="text-align: right; max-width: 60%;">
                            <?php echo htmlspecialchars($order['address']); ?><br>
                            <?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['province']); ?> 
                            <?php echo htmlspecialchars($order['postal_code']); ?>
                        </span>
                    </div>
                    <?php if ($order['shipping_notes']): ?>
                    <div class="detail-row">
                        <span class="detail-label">Catatan:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($order['shipping_notes']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Right Column -->
            <div>
                <!-- Order Status -->
                <div class="card">
                    <div class="section-title"><i class="fas fa-info-circle"></i> Status Pesanan</div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
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
                    
                    <div class="detail-row">
                        <span class="detail-label">Pembayaran:</span>
                        <span class="status-badge status-<?php echo $order['payment_status']; ?>">
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
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Metode:</span>
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
                </div>
                
                <!-- Order Summary -->
                <div class="card">
                    <div class="section-title"><i class="fas fa-file-invoice-dollar"></i> Ringkasan Pembayaran</div>
                    
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>Rp <?php echo number_format($order['subtotal'], 0, ',', '.'); ?></span>
                    </div>
                    
                    <?php if ($order['tax_amount'] > 0): ?>
                    <div class="summary-row">
                        <span>Pajak</span>
                        <span>Rp <?php echo number_format($order['tax_amount'], 0, ',', '.'); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="summary-row">
                        <span>Ongkos Kirim</span>
                        <span>Rp <?php echo number_format($order['shipping_cost'], 0, ',', '.'); ?></span>
                    </div>
                    
                    <?php if ($order['discount_amount'] > 0): ?>
                    <div class="summary-row">
                        <span>Diskon</span>
                        <span style="color: #27ae60;">-Rp <?php echo number_format($order['discount_amount'], 0, ',', '.'); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></span>
                    </div>
                    
                    <?php if ($order['payment_status'] === 'pending'): ?>
                        <button class="btn btn-primary" onclick="alert('Fitur upload bukti pembayaran segera hadir!')" style="margin-top: 20px;">
                            <i class="fas fa-upload"></i> Upload Bukti Pembayaran
                        </button>
                    <?php endif; ?>
                </div>
                
                <!-- Tracking (if applicable) -->
                <?php if (in_array($order['status'], ['confirmed', 'processing', 'shipped', 'delivered'])): ?>
                <div class="card">
                    <div class="section-title"><i class="fas fa-shipping-fast"></i> Tracking Pengiriman</div>
                    
                    <div class="tracking-status">
                        <div class="tracking-step active">
                            <div class="tracking-title">Pesanan Dikonfirmasi</div>
                            <div class="tracking-desc">Pesanan Anda telah dikonfirmasi</div>
                        </div>
                        
                        <div class="tracking-step <?php echo in_array($order['status'], ['processing', 'shipped', 'delivered']) ? 'active' : ''; ?>">
                            <div class="tracking-title">Pesanan Diproses</div>
                            <div class="tracking-desc">Pesanan sedang disiapkan</div>
                        </div>
                        
                        <div class="tracking-step <?php echo in_array($order['status'], ['shipped', 'delivered']) ? 'active' : ''; ?>">
                            <div class="tracking-title">Pesanan Dikirim</div>
                            <div class="tracking-desc">Pesanan dalam perjalanan</div>
                        </div>
                        
                        <div class="tracking-step <?php echo $order['status'] === 'delivered' ? 'active' : ''; ?>">
                            <div class="tracking-title">Pesanan Diterima</div>
                            <div class="tracking-desc">Pesanan telah sampai</div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>



