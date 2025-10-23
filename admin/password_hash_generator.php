<?php
/**
 * Password Hash Generator
 * Tool untuk generate hash password untuk admin user
 * 
 * AKSES KHUSUS: Hanya untuk user dengan role 'developer'
 */

require_once '../config/database.php';
require_once '../includes/auth.php';

// Proteksi akses - hanya developer yang bisa mengakses
requireDeveloper();

$generated_hash = '';
$password_input = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_input = $_POST['password'] ?? '';
    if (!empty($password_input)) {
        $generated_hash = password_hash($password_input, PASSWORD_BCRYPT);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Hash Generator - Elegant Shoes</title>
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
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .header .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 12px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            font-family: monospace;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .result {
            background: #f8f9fa;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .result h3 {
            color: #28a745;
            margin-bottom: 15px;
        }
        
        .hash-output {
            background: white;
            padding: 15px;
            border-radius: 5px;
            word-break: break-all;
            font-family: monospace;
            font-size: 14px;
            color: #333;
            border: 1px solid #dee2e6;
            position: relative;
        }
        
        .copy-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
            width: 100%;
        }
        
        .copy-btn:hover {
            background: #218838;
        }
        
        .sql-example {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .sql-example h4 {
            color: #004085;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .sql-code {
            background: #fff;
            padding: 12px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 13px;
            color: #333;
            border: 1px solid #dee2e6;
            overflow-x: auto;
        }
        
        .instructions {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .instructions h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .instructions ol {
            margin-left: 20px;
        }
        
        .instructions li {
            margin-bottom: 10px;
            line-height: 1.6;
        }
        
        .instructions code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-key"></i> Password Hash Generator</h1>
            <p style="color: #666; margin-top: 5px;">Tool untuk generate hash password admin</p>
            <div class="warning">
                <i class="fas fa-exclamation-triangle"></i> 
                <strong>PERINGATAN:</strong> Hapus file ini setelah selesai digunakan untuk keamanan!
            </div>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-lock"></i> Masukkan Password Baru
                </label>
                <input type="text" name="password" class="form-control" placeholder="Contoh: MySecurePassword123!" value="<?php echo htmlspecialchars($password_input); ?>" required>
                <small style="color: #666; display: block; margin-top: 5px;">
                    <i class="fas fa-info-circle"></i> Password akan di-hash menggunakan bcrypt
                </small>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-cog"></i> Generate Hash
            </button>
        </form>
        
        <?php if ($generated_hash): ?>
        <div class="result">
            <h3><i class="fas fa-check-circle"></i> Hash Berhasil Dibuat</h3>
            <div class="hash-output" id="hashOutput"><?php echo htmlspecialchars($generated_hash); ?></div>
            <button class="copy-btn" onclick="copyHash()">
                <i class="fas fa-copy"></i> Copy Hash ke Clipboard
            </button>
            
            <div class="sql-example">
                <h4><i class="fas fa-database"></i> Contoh Query SQL untuk Update Password:</h4>
                <div class="sql-code">UPDATE admin_users SET password = '<?php echo htmlspecialchars($generated_hash); ?>' WHERE username = 'admin';</div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="instructions">
            <h3><i class="fas fa-book"></i> Cara Menggunakan</h3>
            <ol>
                <li>Masukkan password baru yang diinginkan di form di atas</li>
                <li>Klik tombol <strong>"Generate Hash"</strong></li>
                <li>Copy hash yang dihasilkan</li>
                <li>Buka <strong>phpMyAdmin</strong> (http://localhost/phpmyadmin)</li>
                <li>Pilih database <code>elegant_shoes_db</code></li>
                <li>Klik tab <strong>SQL</strong></li>
                <li>Jalankan query berikut (ganti <code>HASH_DISINI</code> dengan hash yang di-copy):
                    <div class="sql-code" style="margin-top: 10px;">
                        -- Update password user admin:<br>
                        UPDATE admin_users SET password = 'HASH_DISINI' WHERE username = 'admin';
                    </div>
                </li>
                <li><strong>PENTING:</strong> Hapus file <code>password_hash_generator.php</code> setelah selesai!</li>
            </ol>
        </div>
    </div>
    
    <script>
        function copyHash() {
            const hashText = document.getElementById('hashOutput').textContent;
            navigator.clipboard.writeText(hashText).then(function() {
                const btn = event.target.closest('button');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Hash Berhasil Di-copy!';
                btn.style.background = '#218838';
                
                setTimeout(function() {
                    btn.innerHTML = originalText;
                    btn.style.background = '#28a745';
                }, 2000);
            }, function() {
                alert('Gagal copy. Silakan copy manual.');
            });
        }
    </script>
</body>
</html>

