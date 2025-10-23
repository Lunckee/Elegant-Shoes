<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Session - Developer Tools</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>🔍 Session Debug Information</h2>
        <table>
            <tr>
                <th>Key</th>
                <th>Value</th>
            </tr>
            <?php foreach ($_SESSION as $key => $value): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($key); ?></strong></td>
                <td><?php echo htmlspecialchars($value); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="card">
        <h2>🎯 Developer Tools Access Check</h2>
        <?php
        $role = $_SESSION['admin_role'] ?? 'NOT SET';
        $isDeveloper = (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'developer');
        ?>
        <table>
            <tr>
                <th>Check</th>
                <th>Result</th>
            </tr>
            <tr>
                <td>Session Role</td>
                <td><strong><?php echo htmlspecialchars($role); ?></strong></td>
            </tr>
            <tr>
                <td>Is Developer?</td>
                <td class="<?php echo $isDeveloper ? 'success' : 'error'; ?>">
                    <?php echo $isDeveloper ? '✅ YES - Developer Tools SHOULD be visible' : '❌ NO - Developer Tools will NOT be visible'; ?>
                </td>
            </tr>
            <tr>
                <td>Condition Check</td>
                <td>
                    <code>isset($_SESSION['admin_role']) = <?php echo isset($_SESSION['admin_role']) ? 'true' : 'false'; ?></code><br>
                    <code>$_SESSION['admin_role'] === 'developer' = <?php echo ($_SESSION['admin_role'] ?? '') === 'developer' ? 'true' : 'false'; ?></code>
                </td>
            </tr>
        </table>
    </div>

    <div class="card">
        <h2>📊 Database User Info</h2>
        <?php
        $userId = $_SESSION['admin_id'] ?? null;
        if ($userId) {
            $stmt = $db->prepare("SELECT * FROM admin_users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            if ($user) {
                echo "<table>";
                foreach ($user as $key => $value) {
                    if (!is_numeric($key)) {
                        echo "<tr>";
                        echo "<th>" . htmlspecialchars($key) . "</th>";
                        echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                        echo "</tr>";
                    }
                }
                echo "</table>";
            }
        }
        ?>
    </div>

    <div class="card">
        <h2>🔧 Solution</h2>
        <?php if (!$isDeveloper): ?>
        <div style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;">
            <p><strong>⚠️ Developer Tools tidak akan muncul karena role Anda bukan 'developer'</strong></p>
            <p>Untuk menampilkan Developer Tools, Anda perlu:</p>
            <ol>
                <li><strong>Logout</strong> dari akun saat ini</li>
                <li><strong>Login</strong> dengan user <code>surya</code> dan password <code>password</code></li>
            </ol>
            <p>Jika user 'surya' belum ada, jalankan SQL ini di phpMyAdmin:</p>
            <pre>-- Add developer role to enum
ALTER TABLE admin_users 
MODIFY COLUMN role ENUM('super_admin', 'admin', 'manager', 'developer') DEFAULT 'admin';

-- Create developer user
INSERT INTO admin_users (username, email, password, full_name, role, status)
VALUES ('surya', 'surya@developer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Surya - Developer', 'developer', 'active');</pre>
        </div>
        <?php else: ?>
        <div style="background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;">
            <p class="success">✅ Perfect! Developer Tools should be visible on your dashboard.</p>
            <p><a href="dashboard.php" style="color: #667eea; font-weight: bold;">→ Go to Dashboard</a></p>
        </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <a href="dashboard.php" style="display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;">← Back to Dashboard</a>
        <a href="logout.php" style="display: inline-block; padding: 10px 20px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;">Logout</a>
    </div>
</body>
</html>





