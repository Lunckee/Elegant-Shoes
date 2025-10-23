<div class="top-nav">
    <div class="top-nav-left">
        <button class="toggle-sidebar" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div class="breadcrumb">
            <span id="breadcrumb-text">Dashboard</span>
        </div>
    </div>
    
    <div class="top-nav-right">
        <!-- Search -->
        <div style="position: relative;">
            <input type="text" placeholder="Cari..." style="padding: 8px 35px 8px 15px; border: 1px solid #ddd; border-radius: 20px; width: 250px;">
            <i class="fas fa-search" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #666;"></i>
        </div>
        
        <!-- Notifications -->
        <div style="position: relative;">
            <button style="background: none; border: none; font-size: 18px; color: #666; cursor: pointer;">
                <i class="fas fa-bell"></i>
                <span style="position: absolute; top: -5px; right: -5px; background: #e74c3c; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 10px; display: flex; align-items: center; justify-content: center;">3</span>
            </button>
        </div>
        
        <!-- User Menu -->
        <div class="user-menu">
            <div style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <div style="width: 35px; height: 35px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <div style="font-weight: 600; font-size: 14px;"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></div>
                    <div style="font-size: 12px; color: #666;"><?php echo ucfirst($_SESSION['admin_role']); ?></div>
                </div>
                <i class="fas fa-chevron-down" style="font-size: 12px; color: #666;"></i>
            </div>
            
            <div class="user-dropdown">
                <a href="profile.php">
                    <i class="fas fa-user"></i> Profil
                </a>
                <a href="settings.php">
                    <i class="fas fa-cog"></i> Pengaturan
                </a>
                <a href="activity-logs.php">
                    <i class="fas fa-history"></i> Log Aktivitas
                </a>
                <div style="border-top: 1px solid #f0f0f0;"></div>
                <a href="logout.php" style="color: #e74c3c;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</div>







