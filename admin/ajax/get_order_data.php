<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

$order_id = $_GET['id'] ?? 0;

try {
    // Get order data
    $stmt = $db->prepare("
        SELECT o.*, c.name as customer_name, c.email as customer_email,
               sa.name as shipping_name, sa.phone as shipping_phone,
               sa.address as shipping_address, sa.city as shipping_city,
               sa.province as shipping_province, sa.postal_code as shipping_postal_code,
               sa.notes as shipping_notes
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        LEFT JOIN shipping_addresses sa ON o.id = sa.order_id
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        echo json_encode(['error' => 'Pesanan tidak ditemukan']);
        exit();
    }
    
    // Format response
    $response = [
        'order_number' => $order['order_number'],
        'status' => $order['status'],
        'payment_status' => $order['payment_status'],
        'shipping_status' => $order['shipping_status'],
        'notes' => $order['notes'],
        'shipping' => [
            'name' => $order['shipping_name'],
            'phone' => $order['shipping_phone'],
            'address' => $order['shipping_address'],
            'city' => $order['shipping_city'],
            'province' => $order['shipping_province'],
            'postal_code' => $order['shipping_postal_code'],
            'notes' => $order['shipping_notes']
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>



