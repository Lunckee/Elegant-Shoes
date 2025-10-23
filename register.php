<?php
require_once 'config/database.php';

$success = '';
$error = '';

if ($_POST) {
    try {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validasi
        if (empty($name) || empty($email) || empty($password)) {
            throw new Exception('Nama, email, dan password harus diisi!');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Format email tidak valid!');
        }
        
        if (strlen($password) < 6) {
            throw new Exception('Password minimal 6 karakter!');
        }
        
        if ($password !== $confirm_password) {
            throw new Exception('Password dan konfirmasi password tidak cocok!');
        }
        
        // Cek email sudah terdaftar
        $checkStmt = $db->prepare("SELECT id FROM customers WHERE email = ?");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetch()) {
            throw new Exception('Email sudah terdaftar! Silakan gunakan email lain atau login.');
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        // Insert customer baru
        $stmt = $db->prepare("INSERT INTO customers (name, email, phone, password, status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->execute([$name, $email, $phone, $hashedPassword]);
        
        $success = 'Registrasi berhasil! Silakan login untuk melanjutkan.';
        
        // Clear form
        $_POST = [];
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Elegant Shoes</title>
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
        
        .register-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .register-header p {
            color: #666;
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
            padding: 12px 15px;
            border: 2px solid #f0f0f0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            outline: none;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }
        
        .input-group .form-control {
            padding-left: 45px;
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: #e0e0e0;
        }
        
        .divider span {
            background: white;
            padding: 0 15px;
            color: #666;
            position: relative;
            z-index: 1;
        }
        
        .links {
            text-align: center;
            margin-top: 20px;
        }
        
        .links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
        
        .back-home {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-home:hover {
            text-decoration: underline;
        }
        
        .password-strength {
            font-size: 12px;
            margin-top: 5px;
        }
        
        .strength-weak {
            color: #dc3545;
        }
        
        .strength-medium {
            color: #ffc107;
        }
        
        .strength-strong {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <a href="index.html" class="back-home">
            <i class="fas fa-arrow-left"></i> Kembali ke Beranda
        </a>
        
        <div class="register-header">
            <h1><i class="fas fa-user-plus"></i> Daftar Akun</h1>
            <p>Buat akun untuk mulai berbelanja</p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Nama Lengkap *</label>
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="name" class="form-control" placeholder="Masukkan nama lengkap" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Email *</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" class="form-control" placeholder="nama@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Nomor Telepon</label>
                <div class="input-group">
                    <i class="fas fa-phone"></i>
                    <input type="tel" name="phone" class="form-control" placeholder="08xxxxxxxxxx" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password * (minimal 6 karakter)</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required>
                </div>
                <div id="passwordStrength" class="password-strength"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Konfirmasi Password *</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Ketik ulang password" required>
                </div>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-user-plus"></i> Daftar Sekarang
            </button>
        </form>
        
        <div class="divider">
            <span>atau</span>
        </div>
        
        <div class="links">
            <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </div>
    </div>
    
    <script>
        // Password strength indicator
        const password = document.getElementById('password');
        const strengthDiv = document.getElementById('passwordStrength');
        
        password.addEventListener('input', function() {
            const value = this.value;
            const length = value.length;
            
            if (length === 0) {
                strengthDiv.textContent = '';
                return;
            }
            
            let strength = 0;
            if (length >= 6) strength++;
            if (length >= 8) strength++;
            if (/[a-z]/.test(value) && /[A-Z]/.test(value)) strength++;
            if (/[0-9]/.test(value)) strength++;
            if (/[^a-zA-Z0-9]/.test(value)) strength++;
            
            if (strength <= 2) {
                strengthDiv.textContent = '⚠️ Password lemah';
                strengthDiv.className = 'password-strength strength-weak';
            } else if (strength <= 3) {
                strengthDiv.textContent = '✓ Password sedang';
                strengthDiv.className = 'password-strength strength-medium';
            } else {
                strengthDiv.textContent = '✓✓ Password kuat';
                strengthDiv.className = 'password-strength strength-strong';
            }
        });
    </script>
</body>
</html>





