<?php
require_once 'config/database.php';

echo "<h2>Checking Admin Users</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th></tr>";

$stmt = $db->query("SELECT id, username, email, role, status FROM admin_users ORDER BY id");
while ($user = $stmt->fetch()) {
    echo "<tr>";
    echo "<td>" . $user['id'] . "</td>";
    echo "<td><strong>" . $user['username'] . "</strong></td>";
    echo "<td>" . $user['email'] . "</td>";
    echo "<td style='color: " . ($user['role'] === 'developer' ? 'green' : 'blue') . ";'><strong>" . $user['role'] . "</strong></td>";
    echo "<td>" . $user['status'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check if 'developer' role exists in ENUM
echo "<h3>Checking Table Structure</h3>";
$result = $db->query("SHOW COLUMNS FROM admin_users LIKE 'role'");
$roleInfo = $result->fetch();
echo "<p><strong>Role ENUM values:</strong> " . $roleInfo['Type'] . "</p>";

echo "<h3>Action Needed:</h3>";
$devExists = $db->query("SELECT COUNT(*) as count FROM admin_users WHERE role = 'developer'")->fetch();
if ($devExists['count'] == 0) {
    echo "<p style='color: red;'>❌ No developer user found!</p>";
    echo "<p>Run this SQL in phpMyAdmin:</p>";
    echo "<pre style='background: #f0f0f0; padding: 10px;'>";
    echo "-- Step 1: Add developer to role enum\n";
    echo "ALTER TABLE admin_users \n";
    echo "MODIFY COLUMN role ENUM('super_admin', 'admin', 'manager', 'developer') DEFAULT 'admin';\n\n";
    echo "-- Step 2: Create developer user\n";
    echo "INSERT INTO admin_users (username, email, password, full_name, role, status)\n";
    echo "VALUES ('surya', 'surya@developer.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Surya - Developer', 'developer', 'active');";
    echo "</pre>";
} else {
    echo "<p style='color: green;'>✅ Developer user exists!</p>";
    echo "<p><strong>Login credentials:</strong></p>";
    echo "<ul>";
    echo "<li>Username: <strong>surya</strong></li>";
    echo "<li>Password: <strong>password</strong></li>";
    echo "</ul>";
}
?>





