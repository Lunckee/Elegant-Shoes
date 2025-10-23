<?php
/**
 * Setup Developer Role & User
 * Script sekali jalan untuk membuat role developer dan user surya
 * 
 * HAPUS FILE INI SETELAH BERHASIL DIJALANKAN!
 */

require_once 'config/database.php';

$results = [];
$hasError = false;

try {
    // Step 1: Alter table untuk menambahkan 'developer' ke ENUM role
    $results[] = ['step' => 'Step 1: Menambahkan role "developer" ke ENUM', 'status' => 'processing'];
    
    try {
        $db->exec("ALTER TABLE admin_users 
                   MODIFY COLUMN role ENUM('super_admin', 'admin', 'manager', 'developer') DEFAULT 'admin'");
        $results[0]['status'] = 'success';
        $results[0]['message'] = '✅ Role "developer" berhasil ditambahkan ke database';
    } catch (PDOException $e) {
        // Jika error, mungkin sudah ada
        if (strpos($e->getMessage(), 'developer') !== false) {
            $results[0]['status'] = 'info';
            $results[0]['message'] = 'ℹ️ Role "developer" sudah ada di database (skip)';
        } else {
            throw $e;
        }
    }
    
    // Step 2: Cek apakah user surya sudah ada
    $results[] = ['step' => 'Step 2: Mengecek user "surya"', 'status' => 'processing'];
    
    $checkStmt = $db->prepare("SELECT id, username, role FROM admin_users WHERE username = ?");
    $checkStmt->execute(['surya']);
    $existingUser = $checkStmt->fetch();
    
    if ($existingUser) {
        // User sudah ada, cek rolenya
        if ($existingUser['role'] === 'developer') {
            $results[1]['status'] = 'info';
            $results[1]['message'] = 'ℹ️ User "surya" sudah ada dengan role "developer" (skip)';
        } else {
            // Update role ke developer
            $updateStmt = $db->prepare("UPDATE admin_users SET role = 'developer' WHERE username = ?");
            $updateStmt->execute(['surya']);
            $results[1]['status'] = 'success';
            $results[1]['message'] = '✅ User "surya" sudah ada, role diupdate menjadi "developer"';
        }
    } else {
        // User belum ada, buat baru
        $results[1]['status'] = 'processing';
        $results[1]['message'] = 'Membuat user baru...';
        
        $results[] = ['step' => 'Step 3: Membuat user "surya"', 'status' => 'processing'];
        
        $password = password_hash('password', PASSWORD_BCRYPT);
        $insertStmt = $db->prepare("INSERT INTO admin_users (username, email, password, full_name, role, status) 
                                    VALUES (?, ?, ?, ?, ?, ?)");
        $insertStmt->execute([
            'surya',
            'surya@developer.com',
            $password,
            'Surya - Developer',
            'developer',
            'active'
        ]);
        
        $results[1]['status'] = 'success';
        $results[1]['message'] = '✅ User "surya" berhasil ditemukan/diverifikasi';
        $results[2]['status'] = 'success';
        $results[2]['message'] = '✅ User "surya" berhasil dibuat dengan role "developer"';
    }
    
    // Step 4: Verifikasi
    $results[] = ['step' => 'Step 4: Verifikasi Setup', 'status' => 'processing'];
    
    $verifyStmt = $db->prepare("SELECT id, username, email, full_name, role, status FROM admin_users WHERE username = ?");
    $verifyStmt->execute(['surya']);
    $verifiedUser = $verifyStmt->fetch();
    
    if ($verifiedUser && $verifiedUser['role'] === 'developer') {
        $results[count($results)-1]['status'] = 'success';
        $results[count($results)-1]['message'] = '✅ Verifikasi berhasil! User "surya" dengan role "developer" sudah siap';
        $results[count($results)-1]['user_data'] = $verifiedUser;
    } else {
        $results[count($results)-1]['status'] = 'error';
        $results[count($results)-1]['message'] = '❌ Verifikasi gagal! Ada masalah dengan setup';
        $hasError = true;
    }
    
} catch (Exception $e) {
    $hasError = true;
    $results[] = [
        'step' => 'ERROR',
        'status' => 'error',
        'message' => '❌ Error: ' . $e->getMessage()
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Developer Role - Elegant Shoes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            max-width: 700px;
            width: 100%;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
        }
        
        .result-item {
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 10px;
            border-left: 5px solid;
        }
        
        .result-item.success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        
        .result-item.error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        
        .result-item.info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }
        
        .result-item.processing {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        
        .result-item h3 {
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .result-item p {
            font-size: 14px;
            line-height: 1.6;
        }
        
        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }
        
        .user-info table {
            width: 100%;
        }
        
        .user-info td {
            padding: 5px;
        }
        
        .user-info td:first-child {
            font-weight: bold;
            width: 120px;
        }
        
        .success-box {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin-top: 20px;
        }
        
        .success-box h2 {
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .login-info {
            background: rgba(255,255,255,0.2);
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .login-info table {
            width: 100%;
            color: white;
        }
        
        .login-info td {
            padding: 8px;
            font-size: 16px;
        }
        
        .login-info td:first-child {
            width: 100px;
            font-weight: normal;
        }
        
        .login-info td:last-child {
            font-weight: bold;
            font-size: 18px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: white;
            color: #28a745;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin: 5px;
            transition: transform 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .warning-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            color: #856404;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
        }
        
        .warning-box h3 {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-cog"></i> Setup Developer Role</h1>
            <p>Instalasi otomatis role developer dan user surya</p>
        </div>
        
        <?php foreach ($results as $result): ?>
        <div class="result-item <?php echo $result['status']; ?>">
            <h3><?php echo $result['step']; ?></h3>
            <p><?php echo $result['message']; ?></p>
            
            <?php if (isset($result['user_data'])): ?>
            <div class="user-info">
                <table>
                    <tr>
                        <td>ID:</td>
                        <td><?php echo $result['user_data']['id']; ?></td>
                    </tr>
                    <tr>
                        <td>Username:</td>
                        <td><strong><?php echo $result['user_data']['username']; ?></strong></td>
                    </tr>
                    <tr>
                        <td>Email:</td>
                        <td><?php echo $result['user_data']['email']; ?></td>
                    </tr>
                    <tr>
                        <td>Full Name:</td>
                        <td><?php echo $result['user_data']['full_name']; ?></td>
                    </tr>
                    <tr>
                        <td>Role:</td>
                        <td><strong style="color: #28a745;"><?php echo strtoupper($result['user_data']['role']); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td><?php echo ucfirst($result['user_data']['status']); ?></td>
                    </tr>
                </table>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        
        <?php if (!$hasError): ?>
        <div class="success-box">
            <h2><i class="fas fa-check-circle"></i> Setup Berhasil!</h2>
            <p style="margin-bottom: 20px;">User developer sudah siap digunakan</p>
            
            <div class="login-info">
                <table>
                    <tr>
                        <td>Username:</td>
                        <td>surya</td>
                    </tr>
                    <tr>
                        <td>Password:</td>
                        <td>password</td>
                    </tr>
                    <tr>
                        <td>Role:</td>
                        <td>developer</td>
                    </tr>
                </table>
            </div>
            
            <div style="margin-top: 20px;">
                <a href="admin/logout.php" class="btn">
                    <i class="fas fa-sign-out-alt"></i> Logout & Login dengan Surya
                </a>
                <a href="admin/dashboard.php" class="btn">
                    <i class="fas fa-tachometer-alt"></i> Ke Dashboard
                </a>
            </div>
        </div>
        
        <div class="warning-box">
            <h3><i class="fas fa-exclamation-triangle"></i> PENTING - Keamanan!</h3>
            <p style="margin-bottom: 10px;">Setelah selesai setup:</p>
            <ol style="margin-left: 20px; line-height: 1.8;">
                <li><strong>HAPUS file ini:</strong> <code>setup_developer.php</code></li>
                <li>Logout dari admin panel saat ini</li>
                <li>Login ulang dengan user <strong>surya</strong></li>
                <li>Developer Tools akan muncul di dashboard</li>
                <li>Ganti password default untuk keamanan</li>
            </ol>
        </div>
        <?php else: ?>
        <div class="warning-box" style="border-color: #dc3545; background: #f8d7da;">
            <h3><i class="fas fa-times-circle"></i> Setup Gagal!</h3>
            <p>Terjadi error saat setup. Silakan cek pesan error di atas atau hubungi developer.</p>
            <p style="margin-top: 10px;">
                <a href="?retry=1" class="btn btn-danger">
                    <i class="fas fa-redo"></i> Coba Lagi
                </a>
            </p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>





