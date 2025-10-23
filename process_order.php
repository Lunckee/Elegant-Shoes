<?php
session_start();
require_once 'config/database.php';

// Check if cart is empty
if (empty($_SESSION['cart']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit();
}

try {
    // Start transaction
    $db->beginTransaction();
    
    // Get form data
    $customer_id = $_SESSION['customer_id'] ?? null;
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $province = $_POST['province'] ?? '';
    $postal_code = $_POST['postal_code'] ?? '';
    $country = $_POST['country'] ?? 'Indonesia';
    $notes = $_POST['notes'] ?? null;
    $payment_method = $_POST['payment_method'] ?? 'bank_transfer';
    
    // Calculate totals
    $subtotal = 0;
    $total_items = 0;
    
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
        $total_items += $item['quantity'];
    }
    
    $tax_rate = 0;
    $tax_amount = $subtotal * $tax_rate;
    $shipping_cost = 25000;
    $discount_amount = 0;
    $total_amount = $subtotal + $tax_amount + $shipping_cost - $discount_amount;
    
    // If customer not logged in, create guest customer record
    if (!$customer_id) {
        // Check if customer email exists
        $stmt = $db->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $customer_id = $existing['id'];
        } else {
            // Create new customer
            $stmt = $db->prepare("
                INSERT INTO customers (name, email, phone, address, city, province, postal_code, country, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
            ");
            $stmt->execute([$name, $email, $phone, $address, $city, $province, $postal_code, $country]);
            $customer_id = $db->lastInsertId();
        }
    }
    
    // Generate order number
    $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    // Insert order
    $stmt = $db->prepare("
        INSERT INTO orders (
            order_number, customer_id, status, payment_status, shipping_status,
            subtotal, tax_amount, shipping_cost, discount_amount, total_amount,
            notes
        ) VALUES (?, ?, 'pending', 'pending', 'pending', ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $order_number,
        $customer_id,
        $subtotal,
        $tax_amount,
        $shipping_cost,
        $discount_amount,
        $total_amount,
        $notes
    ]);
    
    $order_id = $db->lastInsertId();
    
    // Insert order items and update stock
    foreach ($_SESSION['cart'] as $item) {
        // Insert order item
        $stmt = $db->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price, total)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $order_id,
            $item['product_id'],
            $item['quantity'],
            $item['price'],
            $item['price'] * $item['quantity']
        ]);
        
        // Update product stock
        $stmt = $db->prepare("
            UPDATE products 
            SET stock = stock - ? 
            WHERE id = ? AND stock >= ?
        ");
        $affected = $stmt->execute([
            $item['quantity'],
            $item['product_id'],
            $item['quantity']
        ]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception("Stok tidak mencukupi untuk produk: " . $item['name']);
        }
    }
    
    // Insert shipping address
    $stmt = $db->prepare("
        INSERT INTO shipping_addresses (
            order_id, name, phone, address, city, province, postal_code, country, notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $order_id,
        $name,
        $phone,
        $address,
        $city,
        $province,
        $postal_code,
        $country,
        $notes
    ]);
    
    // Insert payment record
    $stmt = $db->prepare("
        INSERT INTO payments (
            order_id, payment_method, payment_status, amount
        ) VALUES (?, ?, 'pending', ?)
    ");
    $stmt->execute([
        $order_id,
        $payment_method,
        $total_amount
    ]);
    
    // Commit transaction
    $db->commit();
    
    // Clear cart
    $_SESSION['cart'] = [];
    
    // Set session for order confirmation
    $_SESSION['last_order_id'] = $order_id;
    $_SESSION['last_order_number'] = $order_number;
    
    // Redirect to confirmation page
    header('Location: order_success.php');
    exit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    $db->rollBack();
    
    // Redirect back to checkout with error
    $_SESSION['checkout_error'] = $e->getMessage();
    header('Location: checkout.php?error=1');
    exit();
}
?>



