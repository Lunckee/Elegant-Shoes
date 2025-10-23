<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireLogin();

// Get dashboard statistics
$stats = [];

// Total Products
$stmt = $db->query("SELECT COUNT(*) as count FROM products");
$stats['products'] = $stmt->fetch()['count'];

// Total Orders
$stmt = $db->query("SELECT COUNT(*) as count FROM orders");
$stats['orders'] = $stmt->fetch()['count'];

// Total Customers
$stmt = $db->query("SELECT COUNT(*) as count FROM customers");
$stats['customers'] = $stmt->fetch()['count'];

// Total Revenue
$stmt = $db->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'");
$stats['revenue'] = $stmt->fetch()['total'] ?? 0;

// Recent Orders
$stmt = $db->query("SELECT o.*, c.name as customer_name FROM orders o 
                   LEFT JOIN customers c ON o.customer_id = c.id 
                   ORDER BY o.created_at DESC LIMIT 10");
$recent_orders = $stmt->fetchAll();

// Low Stock Products
$stmt = $db->query("SELECT * FROM products WHERE stock <= min_stock ORDER BY stock ASC LIMIT 5");
$low_stock_products = $stmt->fetchAll();

// Monthly Sales Chart Data
$stmt = $db->query("SELECT DATE_FORMAT(order_date, '%Y-%m') as month, 
                   SUM(total_amount) as total, COUNT(*) as orders 
                   FROM orders 
                   WHERE payment_status = 'paid' 
                   AND order_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                   GROUP BY DATE_FORMAT(order_date, '%Y-%m')
                   ORDER BY month");
$sales_data = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Elegant Shoes Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/top-nav.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                <div class="breadcrumb">
                    <a href="dashboard.php">Dashboard</a>
                </div>
            </div>
            
            <!-- Access Denied Alert -->
            <?php if (isset($_GET['error']) && $_GET['error'] === 'access_denied'): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px;">
                <i class="fas fa-exclamation-triangle"></i> 
                <strong>Akses Ditolak!</strong> Anda tidak memiliki izin untuk mengakses halaman tersebut. 
                Fitur developer tools hanya dapat diakses oleh user dengan role 'developer'.
            </div>
            <?php endif; ?>
            
            <!-- Developer Tools (Only for Developer Role) -->
            <?php if (isDeveloper()): ?>
            <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; margin-bottom: 30px;">
                <div class="card-body">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <h3 style="color: white; margin-bottom: 10px;">
                                <i class="fas fa-code"></i> Developer Tools
                            </h3>
                            <p style="color: rgba(255,255,255,0.9); margin: 0;">
                                Akses khusus untuk developer - Kelola admin users dan konfigurasi sistem
                            </p>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <a href="admin_user_manager.php" class="btn" style="background: white; color: #667eea; font-weight: 600;">
                                <i class="fas fa-users-cog"></i> Admin User Manager
                            </a>
                            <a href="password_hash_generator.php" class="btn" style="background: rgba(255,255,255,0.2); color: white; font-weight: 600;">
                                <i class="fas fa-key"></i> Password Generator
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['products']); ?></h3>
                        <p>Total Produk</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['orders']); ?></h3>
                        <p>Total Pesanan</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['customers']); ?></h3>
                        <p>Total Pelanggan</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Rp <?php echo number_format($stats['revenue'], 0, ',', '.'); ?></h3>
                        <p>Total Pendapatan</p>
                    </div>
                </div>
            </div>
            
            <!-- Charts and Tables Row -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                <!-- Sales Chart -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-line"></i> Grafik Penjualan 12 Bulan Terakhir</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" width="400" height="200"></canvas>
                    </div>
                </div>
                
                <!-- Low Stock Alert -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-exclamation-triangle"></i> Stok Rendah</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($low_stock_products)): ?>
                            <p style="color: #27ae60; text-align: center;">
                                <i class="fas fa-check-circle"></i> Semua produk memiliki stok yang cukup
                            </p>
                        <?php else: ?>
                            <?php foreach ($low_stock_products as $product): ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                                    <div>
                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                        <br>
                                        <small style="color: #666;">SKU: <?php echo htmlspecialchars($product['sku']); ?></small>
                                    </div>
                                    <span class="badge badge-danger"><?php echo $product['stock']; ?> tersisa</span>
                                </div>
                            <?php endforeach; ?>
                            <div style="text-align: center; margin-top: 15px;">
                                <a href="products.php?filter=low_stock" class="btn btn-warning btn-sm">
                                    <i class="fas fa-eye"></i> Lihat Semua
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-clock"></i> Pesanan Terbaru</h3>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No. Pesanan</th>
                                    <th>Pelanggan</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></td>
                                    <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $order['status'] == 'delivered' ? 'success' : 
                                                ($order['status'] == 'pending' ? 'warning' : 'info'); 
                                        ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                                    <td>
                                        <a href="orders.php?action=view&id=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="text-align: center; margin-top: 15px;">
                        <a href="orders.php" class="btn btn-primary">
                            <i class="fas fa-list"></i> Lihat Semua Pesanan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Sales Chart
        const salesData = <?php echo json_encode($sales_data); ?>;
        const ctx = document.getElementById('salesChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: salesData.map(item => item.month),
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: salesData.map(item => item.total),
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
    
    <script src="assets/js/admin.js"></script>
</body>
</html>



