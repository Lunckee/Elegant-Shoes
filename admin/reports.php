<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireLogin();

// Get date range
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-t');

// Sales Report
$sales_stmt = $db->prepare("SELECT 
    DATE_FORMAT(order_date, '%Y-%m') as month,
    COUNT(*) as total_orders,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_order_value
    FROM orders 
    WHERE payment_status = 'paid' 
    AND DATE(order_date) BETWEEN ? AND ?
    GROUP BY DATE_FORMAT(order_date, '%Y-%m')
    ORDER BY month");
$sales_stmt->execute([$date_from, $date_to]);
$sales_data = $sales_stmt->fetchAll();

// Top Products
$products_stmt = $db->prepare("SELECT 
    p.name,
    p.sku,
    SUM(oi.quantity) as total_sold,
    SUM(oi.total) as total_revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.payment_status = 'paid'
    AND DATE(o.order_date) BETWEEN ? AND ?
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 10");
$products_stmt->execute([$date_from, $date_to]);
$top_products = $products_stmt->fetchAll();

// Customer Analytics
$customers_stmt = $db->prepare("SELECT 
    COUNT(DISTINCT o.customer_id) as total_customers,
    COUNT(*) as total_orders,
    SUM(o.total_amount) as total_revenue
    FROM orders o
    WHERE o.payment_status = 'paid'
    AND DATE(o.order_date) BETWEEN ? AND ?");
$customers_stmt->execute([$date_from, $date_to]);
$customer_stats = $customers_stmt->fetch();

// Order Status Distribution
$status_stmt = $db->prepare("SELECT 
    status,
    COUNT(*) as count
    FROM orders
    WHERE DATE(order_date) BETWEEN ? AND ?
    GROUP BY status");
$status_stmt->execute([$date_from, $date_to]);
$status_data = $status_stmt->fetchAll();

// Payment Method Distribution
$payment_stmt = $db->prepare("SELECT 
    payment_method,
    COUNT(*) as count,
    SUM(amount) as total
    FROM payments
    WHERE payment_status = 'paid'
    AND DATE(payment_date) BETWEEN ? AND ?
    GROUP BY payment_method");
$payment_stmt->execute([$date_from, $date_to]);
$payment_data = $payment_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analisis & Laporan - Elegant Shoes Admin</title>
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
                <h1><i class="fas fa-chart-bar"></i> Analisis & Laporan</h1>
                <div class="breadcrumb">
                    <a href="dashboard.php">Dashboard</a> / <span>Analisis & Laporan</span>
                </div>
            </div>
            
            <!-- Date Range Filter -->
            <div class="card">
                <div class="card-body">
                    <form method="GET" style="display: flex; gap: 15px; align-items: end;">
                        <div>
                            <label class="form-label">Dari Tanggal:</label>
                            <input type="date" name="date_from" value="<?php echo $date_from; ?>" class="form-control">
                        </div>
                        <div>
                            <label class="form-label">Sampai Tanggal:</label>
                            <input type="date" name="date_to" value="<?php echo $date_to; ?>" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <button type="button" class="btn btn-success" onclick="exportReport()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Summary Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($customer_stats['total_orders']); ?></h3>
                        <p>Total Pesanan</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Rp <?php echo number_format($customer_stats['total_revenue'], 0, ',', '.'); ?></h3>
                        <p>Total Pendapatan</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($customer_stats['total_customers']); ?></h3>
                        <p>Pelanggan Aktif</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Rp <?php echo number_format($customer_stats['total_orders'] > 0 ? $customer_stats['total_revenue'] / $customer_stats['total_orders'] : 0, 0, ',', '.'); ?></h3>
                        <p>Rata-rata Order</p>
                    </div>
                </div>
            </div>
            
            <!-- Charts Row -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-bottom: 30px;">
                <!-- Sales Chart -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-line"></i> Grafik Penjualan Bulanan</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" width="400" height="200"></canvas>
                    </div>
                </div>
                
                <!-- Order Status Chart -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-pie"></i> Distribusi Status Pesanan</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart" width="300" height="300"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Tables Row -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                <!-- Top Products -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-star"></i> Produk Terlaris</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Terjual</th>
                                        <th>Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_products as $product): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($product['name']); ?></strong><br>
                                            <small style="color: #666;"><?php echo htmlspecialchars($product['sku']); ?></small>
                                        </td>
                                        <td><?php echo number_format($product['total_sold']); ?></td>
                                        <td>Rp <?php echo number_format($product['total_revenue'], 0, ',', '.'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Methods -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-credit-card"></i> Metode Pembayaran</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Metode</th>
                                        <th>Jumlah</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payment_data as $payment): ?>
                                    <tr>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                                        <td><?php echo number_format($payment['count']); ?></td>
                                        <td>Rp <?php echo number_format($payment['total'], 0, ',', '.'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Detailed Sales Report -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-table"></i> Laporan Penjualan Detail</h3>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Bulan</th>
                                    <th>Total Pesanan</th>
                                    <th>Total Pendapatan</th>
                                    <th>Rata-rata Order</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sales_data as $month): ?>
                                <tr>
                                    <td><?php echo date('F Y', strtotime($month['month'] . '-01')); ?></td>
                                    <td><?php echo number_format($month['total_orders']); ?></td>
                                    <td>Rp <?php echo number_format($month['total_revenue'], 0, ',', '.'); ?></td>
                                    <td>Rp <?php echo number_format($month['avg_order_value'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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
                labels: salesData.map(item => {
                    const date = new Date(item.month + '-01');
                    return date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: salesData.map(item => item.total_revenue),
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
        
        // Status Chart
        const statusData = <?php echo json_encode($status_data); ?>;
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusData.map(item => item.status),
                datasets: [{
                    data: statusData.map(item => item.count),
                    backgroundColor: [
                        '#3498db',
                        '#27ae60',
                        '#f39c12',
                        '#e74c3c',
                        '#9b59b6',
                        '#1abc9c'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        function exportReport() {
            const dateFrom = '<?php echo $date_from; ?>';
            const dateTo = '<?php echo $date_to; ?>';
            window.open(`ajax/export_report.php?date_from=${dateFrom}&date_to=${dateTo}`, '_blank');
        }
    </script>
</body>
</html>







