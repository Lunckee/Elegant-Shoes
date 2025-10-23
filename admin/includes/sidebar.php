<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-shoe-prints"></i> Elegant Shoes</h3>
    </div>
    
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="menu-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        
        <a href="products.php" class="menu-item <?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
            <i class="fas fa-box"></i>
            <span>Management Produk</span>
        </a>
        
        <a href="categories.php" class="menu-item <?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">
            <i class="fas fa-tags"></i>
            <span>Kategori Produk</span>
        </a>
        
        <a href="orders.php" class="menu-item <?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i>
            <span>Management Pesanan</span>
        </a>
        
        <a href="payments.php" class="menu-item <?php echo $current_page == 'payments.php' ? 'active' : ''; ?>">
            <i class="fas fa-credit-card"></i>
            <span>Management Pembayaran</span>
        </a>
        
        <a href="shipping.php" class="menu-item <?php echo $current_page == 'shipping.php' ? 'active' : ''; ?>">
            <i class="fas fa-truck"></i>
            <span>Management Pengiriman</span>
        </a>
        
        <a href="customers.php" class="menu-item <?php echo $current_page == 'customers.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Data Pelanggan</span>
        </a>
        
        <a href="reviews.php" class="menu-item <?php echo $current_page == 'reviews.php' ? 'active' : ''; ?>">
            <i class="fas fa-star"></i>
            <span>Ulasan & Komentar</span>
        </a>
        
        <a href="coupons.php" class="menu-item <?php echo $current_page == 'coupons.php' ? 'active' : ''; ?>">
            <i class="fas fa-ticket-alt"></i>
            <span>Kupon & Diskon</span>
        </a>
        
        <a href="reports.php" class="menu-item <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i>
            <span>Analisis & Laporan</span>
        </a>
        
        <a href="settings.php" class="menu-item <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i>
            <span>Pengaturan Website</span>
        </a>
        
        <?php if (hasRole('super_admin')): ?>
        <a href="admin-users.php" class="menu-item <?php echo $current_page == 'admin-users.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-shield"></i>
            <span>Management Admin</span>
        </a>
        
        <a href="activity-logs.php" class="menu-item <?php echo $current_page == 'activity-logs.php' ? 'active' : ''; ?>">
            <i class="fas fa-history"></i>
            <span>Log Aktivitas</span>
        </a>
        <?php endif; ?>
    </nav>
</div>







