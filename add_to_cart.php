<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: shop.php');
    exit();
}

$product_id = $_POST['product_id'] ?? 0;
$quantity = (int)($_POST['quantity'] ?? 1);

if ($quantity < 1) {
    $quantity = 1;
}

// Get product details
$stmt = $db->prepare("
    SELECT p.*, 
           pi.image_path,
           COALESCE(p.sale_price, p.price) as final_price
    FROM products p
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    WHERE p.id = ? AND p.status = 'active'
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: shop.php?error=product_not_found');
    exit();
}

// Check stock
if ($product['stock'] < $quantity) {
    header('Location: shop.php?error=insufficient_stock');
    exit();
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add or update cart item
if (isset($_SESSION['cart'][$product_id])) {
    // Update quantity
    $new_quantity = $_SESSION['cart'][$product_id]['quantity'] + $quantity;
    
    // Check if new quantity exceeds stock
    if ($new_quantity > $product['stock']) {
        $_SESSION['cart'][$product_id]['quantity'] = $product['stock'];
    } else {
        $_SESSION['cart'][$product_id]['quantity'] = $new_quantity;
    }
} else {
    // Add new item
    $_SESSION['cart'][$product_id] = [
        'product_id' => $product['id'],
        'name' => $product['name'],
        'price' => $product['final_price'],
        'image' => $product['image_path'],
        'quantity' => $quantity,
        'stock' => $product['stock']
    ];
}

// Redirect back
$redirect = $_POST['redirect'] ?? 'shop.php';
header("Location: $redirect?added=1");
exit();
?>



