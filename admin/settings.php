<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireLogin();

// Handle settings update
if ($_POST) {
    foreach ($_POST as $key => $value) {
        if ($key !== 'submit') {
            $stmt = $db->prepare("UPDATE website_settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$value, $key]);
        }
    }
    
    logActivity('update', 'website_settings', null, null, $_POST);
    header('Location: settings.php?success=updated');
    exit();
}

// Get all settings
$settings_stmt = $db->query("SELECT * FROM website_settings ORDER BY setting_key");
$settings = [];
while ($row = $settings_stmt->fetch()) {
    $settings[$row['setting_key']] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Website - Elegant Shoes Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/top-nav.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1><i class="fas fa-cog"></i> Pengaturan Website</h1>
                <div class="breadcrumb">
                    <a href="dashboard.php">Dashboard</a> / <span>Pengaturan Website</span>
                </div>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 
                    Pengaturan berhasil diperbarui!
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <!-- General Settings -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-info-circle"></i> Pengaturan Umum</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nama Website</label>
                                <input type="text" name="site_name" value="<?php echo htmlspecialchars($settings['site_name']['setting_value']); ?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email Utama</label>
                                <input type="email" name="site_email" value="<?php echo htmlspecialchars($settings['site_email']['setting_value']); ?>" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nomor Telepon</label>
                                <input type="text" name="site_phone" value="<?php echo htmlspecialchars($settings['site_phone']['setting_value']); ?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Alamat</label>
                                <textarea name="site_address" class="form-control" rows="3"><?php echo htmlspecialchars($settings['site_address']['setting_value']); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- E-commerce Settings -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-shopping-cart"></i> Pengaturan E-commerce</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Mata Uang</label>
                                <select name="currency" class="form-control">
                                    <option value="IDR" <?php echo $settings['currency']['setting_value'] == 'IDR' ? 'selected' : ''; ?>>Rupiah (IDR)</option>
                                    <option value="USD" <?php echo $settings['currency']['setting_value'] == 'USD' ? 'selected' : ''; ?>>Dollar (USD)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Persentase Pajak (%)</label>
                                <input type="number" name="tax_rate" value="<?php echo htmlspecialchars($settings['tax_rate']['setting_value']); ?>" class="form-control" step="0.01">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Biaya Pengiriman (Rp)</label>
                                <input type="number" name="shipping_cost" value="<?php echo htmlspecialchars($settings['shipping_cost']['setting_value']); ?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Minimum Jumlah Pesanan (Rp)</label>
                                <input type="number" name="min_order_amount" value="<?php echo htmlspecialchars($settings['min_order_amount']['setting_value']); ?>" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- SEO Settings -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-search"></i> Pengaturan SEO</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">Meta Title Default</label>
                            <input type="text" name="meta_title" value="<?php echo htmlspecialchars($settings['meta_title']['setting_value'] ?? ''); ?>" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Meta Description Default</label>
                            <textarea name="meta_description" class="form-control" rows="3"><?php echo htmlspecialchars($settings['meta_description']['setting_value'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Keywords</label>
                            <input type="text" name="keywords" value="<?php echo htmlspecialchars($settings['keywords']['setting_value'] ?? ''); ?>" class="form-control" placeholder="sepatu, shoes, elegant, fashion">
                        </div>
                    </div>
                </div>
                
                <!-- Social Media Settings -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-share-alt"></i> Media Sosial</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Facebook URL</label>
                                <input type="url" name="facebook_url" value="<?php echo htmlspecialchars($settings['facebook_url']['setting_value'] ?? ''); ?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Instagram URL</label>
                                <input type="url" name="instagram_url" value="<?php echo htmlspecialchars($settings['instagram_url']['setting_value'] ?? ''); ?>" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Twitter URL</label>
                                <input type="url" name="twitter_url" value="<?php echo htmlspecialchars($settings['twitter_url']['setting_value'] ?? ''); ?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">YouTube URL</label>
                                <input type="url" name="youtube_url" value="<?php echo htmlspecialchars($settings['youtube_url']['setting_value'] ?? ''); ?>" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Email Settings -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-envelope"></i> Pengaturan Email</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">SMTP Host</label>
                                <input type="text" name="smtp_host" value="<?php echo htmlspecialchars($settings['smtp_host']['setting_value'] ?? ''); ?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">SMTP Port</label>
                                <input type="number" name="smtp_port" value="<?php echo htmlspecialchars($settings['smtp_port']['setting_value'] ?? '587'); ?>" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">SMTP Username</label>
                                <input type="text" name="smtp_username" value="<?php echo htmlspecialchars($settings['smtp_username']['setting_value'] ?? ''); ?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">SMTP Password</label>
                                <input type="password" name="smtp_password" value="<?php echo htmlspecialchars($settings['smtp_password']['setting_value'] ?? ''); ?>" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Security Settings -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-shield-alt"></i> Pengaturan Keamanan</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Maintenance Mode</label>
                                <select name="maintenance_mode" class="form-control">
                                    <option value="0" <?php echo ($settings['maintenance_mode']['setting_value'] ?? '0') == '0' ? 'selected' : ''; ?>>Tidak Aktif</option>
                                    <option value="1" <?php echo ($settings['maintenance_mode']['setting_value'] ?? '0') == '1' ? 'selected' : ''; ?>>Aktif</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Max Login Attempts</label>
                                <input type="number" name="max_login_attempts" value="<?php echo htmlspecialchars($settings['max_login_attempts']['setting_value'] ?? '5'); ?>" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <button type="submit" name="submit" class="btn btn-primary" style="padding: 15px 50px; font-size: 16px;">
                            <i class="fas fa-save"></i> Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>







