<?php
/**
 * Authentication System
 * Elegant Shoes Admin Panel
 */

session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Check user role
function hasRole($required_role) {
    if (!isLoggedIn()) return false;
    
    $user_role = $_SESSION['admin_role'] ?? '';
    $role_hierarchy = ['manager' => 1, 'admin' => 2, 'super_admin' => 3, 'developer' => 4];
    
    // Jika role tidak ada di hierarchy, return false
    if (!isset($role_hierarchy[$user_role]) || !isset($role_hierarchy[$required_role])) {
        return false;
    }
    
    return $role_hierarchy[$user_role] >= $role_hierarchy[$required_role];
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../admin/login.php');
        exit();
    }
}

// Require specific role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: ../admin/dashboard.php?error=access_denied');
        exit();
    }
}

// Check if current user is developer
function isDeveloper() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'developer';
}

// Require developer role
function requireDeveloper() {
    requireLogin();
    if (!isDeveloper()) {
        header('Location: dashboard.php?error=access_denied');
        exit();
    }
}

// Login function
function login($username, $password) {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE (username = ? OR email = ?) AND status = 'active'");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_email'] = $user['email'];
        $_SESSION['admin_name'] = $user['full_name'];
        $_SESSION['admin_role'] = $user['role'];
        
        // Update last login
        $update_stmt = $db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
        $update_stmt->execute([$user['id']]);
        
        return true;
    }
    
    return false;
}

// Logout function
function logout() {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Log activity
function logActivity($action, $table_name = null, $record_id = null, $old_values = null, $new_values = null) {
    global $db;
    
    $stmt = $db->prepare("INSERT INTO activity_logs (admin_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['admin_id'] ?? null,
        $action,
        $table_name,
        $record_id,
        $old_values ? json_encode($old_values) : null,
        $new_values ? json_encode($new_values) : null,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}
?>



