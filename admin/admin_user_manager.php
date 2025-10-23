<?php
/**
 * Admin User Manager
 * Tool untuk membuat dan mengelola admin user dengan mudah
 * 
 * AKSES KHUSUS: Hanya untuk user dengan role 'developer'
 */

require_once '../config/database.php';
require_once '../includes/auth.php';

// Proteksi akses - hanya developer yang bisa mengakses
requireDeveloper();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'create') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $full_name = trim($_POST['full_name'] ?? '');
            $role = $_POST['role'] ?? 'admin';
            
            // Validasi
            if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
                throw new Exception('Semua field harus diisi!');
            }
            
            if (strlen($password) < 6) {
                throw new Exception('Password minimal 6 karakter!');
            }
            
            // Check if username or email already exists
            $check_stmt = $db->prepare("SELECT COUNT(*) FROM admin_users WHERE username = ? OR email = ?");
            $check_stmt->execute([$username, $email]);
            if ($check_stmt->fetchColumn() > 0) {
                throw new Exception('Username atau email sudah digunakan!');
            }
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert new admin
            $stmt = $db->prepare("INSERT INTO admin_users (username, email, password, full_name, role, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$username, $email, $hashed_password, $full_name, $role]);
            
            $message = "Admin user '<strong>{$username}</strong>' berhasil dibuat!";
            
        } elseif ($action === 'update_password') {
            $user_id = $_POST['user_id'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            
            if (empty($new_password)) {
                throw new Exception('Password tidak boleh kosong!');
            }
            
            if (strlen($new_password) < 6) {
                throw new Exception('Password minimal 6 karakter!');
            }
            
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            
            $stmt = $db->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $user_id]);
            
            $message = "Password berhasil diupdate!";
            
        } elseif ($action === 'delete') {
            $user_id = $_POST['user_id'] ?? '';
            
            // Don't allow deleting if only one admin left
            $count_stmt = $db->query("SELECT COUNT(*) FROM admin_users WHERE status = 'active'");
            if ($count_stmt->fetchColumn() <= 1) {
                throw new Exception('Tidak bisa menghapus admin terakhir!');
            }
            
            $stmt = $db->prepare("DELETE FROM admin_users WHERE id = ?");
            $stmt->execute([$user_id]);
            
            $message = "Admin user berhasil dihapus!";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get all admin users
$users = $db->query("SELECT * FROM admin_users ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin User Manager - Elegant Shoes</title>
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
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 12px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 14px;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 20px;
        }
        
        .card h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 22px;
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
            font-size: 14px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 8px 16px;
            font-size: 13px;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #333;
            padding: 8px 16px;
            font-size: 13px;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #f8f9fa;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        th {
            font-weight: 600;
            color: #333;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge-primary {
            background: #cce5ff;
            color: #004085;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .modal-content {
            background: white;
            max-width: 500px;
            margin: 50px auto;
            border-radius: 15px;
            padding: 30px;
        }
        
        .modal h3 {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-users-cog"></i> Admin User Manager</h1>
            <p style="color: #666;">Kelola admin user dengan mudah</p>
            <div class="warning">
                <i class="fas fa-exclamation-triangle"></i> 
                <strong>PERINGATAN:</strong> Hapus file ini setelah selesai digunakan untuk keamanan!
            </div>
        </div>
        
        <?php if ($message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <h2><i class="fas fa-user-plus"></i> Tambah Admin User Baru</h2>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Role *</label>
                        <select name="role" class="form-control" required>
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                            <option value="manager">Manager</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password * (minimal 6 karakter)</label>
                    <input type="text" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Buat Admin User
                </button>
            </form>
        </div>
        
        <div class="card">
            <h2><i class="fas fa-list"></i> Daftar Admin User</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Nama Lengkap</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td>
                            <?php
                            $role_class = $user['role'] === 'super_admin' ? 'primary' : ($user['role'] === 'admin' ? 'success' : 'warning');
                            ?>
                            <span class="badge badge-<?php echo $role_class; ?>">
                                <?php echo ucwords(str_replace('_', ' ', $user['role'])); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $user['status'] === 'active' ? 'success' : 'danger'; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Belum pernah'; ?>
                        </td>
                        <td>
                            <button class="btn btn-warning" onclick="showPasswordModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                <i class="fas fa-key"></i> Reset Password
                            </button>
                            <button class="btn btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Password Modal -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-key"></i> Reset Password</h3>
            <form method="POST">
                <input type="hidden" name="action" value="update_password">
                <input type="hidden" name="user_id" id="userId">
                
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" id="modalUsername" class="form-control" readonly>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password Baru * (minimal 6 karakter)</label>
                    <input type="text" name="new_password" class="form-control" required>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-save"></i> Update Password
                    </button>
                    <button type="button" class="btn btn-danger" onclick="closeModal()" style="flex: 1;">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function showPasswordModal(userId, username) {
            document.getElementById('userId').value = userId;
            document.getElementById('modalUsername').value = username;
            document.getElementById('passwordModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('passwordModal').style.display = 'none';
        }
        
        function deleteUser(userId, username) {
            if (confirm('Apakah Anda yakin ingin menghapus user "' + username + '"?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="user_id" value="' + userId + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('passwordModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>

