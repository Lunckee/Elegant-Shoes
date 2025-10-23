<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireLogin();

// Handle CRUD operations
if ($_POST && isset($_POST['action'])) {
    $action = $_POST['action'];
    $order_id = $_POST['order_id'] ?? null;
    
    try {
        switch ($action) {
            case 'update_order':
                // Update order statuses
                $status = $_POST['status'];
                $payment_status = $_POST['payment_status'];
                $shipping_status = $_POST['shipping_status'];
                $notes = $_POST['notes'] ?? null;
                
                $stmt = $db->prepare("UPDATE orders SET status = ?, payment_status = ?, shipping_status = ?, notes = ? WHERE id = ?");
                $stmt->execute([$status, $payment_status, $shipping_status, $notes, $order_id]);
                
                // Update shipping address if provided
                if (isset($_POST['shipping_name'])) {
                    $stmt = $db->prepare("UPDATE shipping_addresses SET 
                        name = ?, phone = ?, address = ?, city = ?, province = ?, postal_code = ?, notes = ? 
                        WHERE order_id = ?");
                    $stmt->execute([
                        $_POST['shipping_name'],
                        $_POST['shipping_phone'],
                        $_POST['shipping_address'],
                        $_POST['shipping_city'],
                        $_POST['shipping_province'],
                        $_POST['shipping_postal_code'],
                        $_POST['shipping_notes'] ?? null,
                        $order_id
                    ]);
                }
                
                logActivity('update', 'orders', $order_id, null, $_POST);
                header('Location: orders.php?success=updated');
                exit();
                
            case 'delete_order':
                // Begin transaction
                $db->beginTransaction();
                
                // Get order number for logging
                $stmt = $db->prepare("SELECT order_number FROM orders WHERE id = ?");
                $stmt->execute([$order_id]);
                $order = $stmt->fetch();
                
                // Delete order (akan cascade delete ke order_items, shipping_addresses, payments)
                $stmt = $db->prepare("DELETE FROM orders WHERE id = ?");
                $stmt->execute([$order_id]);
                
                // Commit transaction
                $db->commit();
                
                logActivity('delete', 'orders', $order_id, ['order_number' => $order['order_number']], null);
                header('Location: orders.php?success=deleted');
                exit();
                
            case 'update_status':
                $new_status = $_POST['status'];
                $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $order_id]);
                
                logActivity('update', 'orders', $order_id, null, ['status' => $new_status]);
                header('Location: orders.php?success=status_updated');
                exit();
        }
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        header('Location: orders.php?error=' . urlencode($e->getMessage()));
        exit();
    }
}

// Get orders with filters
$page = $_GET['page'] ?? 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$payment_filter = $_GET['payment'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(o.order_number LIKE ? OR c.name LIKE ? OR c.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter) {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
}

if ($payment_filter) {
    $where_conditions[] = "o.payment_status = ?";
    $params[] = $payment_filter;
}

if ($date_from) {
    $where_conditions[] = "DATE(o.order_date) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $where_conditions[] = "DATE(o.order_date) <= ?";
    $params[] = $date_to;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get orders
$stmt = $db->prepare("SELECT o.*, c.name as customer_name, c.email as customer_email, c.phone as customer_phone 
                     FROM orders o 
                     LEFT JOIN customers c ON o.customer_id = c.id 
                     $where_clause 
                     ORDER BY o.order_date DESC LIMIT ? OFFSET ?");
$params[] = $limit;
$params[] = $offset;
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Get total count
$count_stmt = $db->prepare("SELECT COUNT(*) FROM orders o 
                           LEFT JOIN customers c ON o.customer_id = c.id 
                           $where_clause");
$count_stmt->execute(array_slice($params, 0, -2));
$total_orders = $count_stmt->fetchColumn();
$total_pages = ceil($total_orders / $limit);

// Get order statistics
$stats_stmt = $db->query("SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as total_revenue,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders
    FROM orders");
$stats = $stats_stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Pesanan - Elegant Shoes Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/top-nav.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1><i class="fas fa-shopping-cart"></i> Management Pesanan</h1>
                <div class="breadcrumb">
                    <a href="dashboard.php">Dashboard</a> / <span>Management Pesanan</span>
                </div>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 
                    <?php 
                    $success_messages = [
                        'updated' => 'Pesanan berhasil diperbarui!',
                        'deleted' => 'Pesanan berhasil dihapus!',
                        'status_updated' => 'Status pesanan berhasil diperbarui!'
                    ];
                    echo $success_messages[$_GET['success']] ?? 'Operasi berhasil!';
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> 
                    Error: <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Order Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_orders']); ?></h3>
                        <p>Total Pesanan</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Rp <?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?></h3>
                        <p>Total Pendapatan</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['pending_orders']); ?></h3>
                        <p>Pesanan Pending</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['delivered_orders']); ?></h3>
                        <p>Pesanan Selesai</p>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="card">
                <div class="card-body">
                    <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: end;">
                        <div>
                            <label>Pencarian:</label>
                            <input type="text" name="search" placeholder="No. pesanan, nama, email..." value="<?php echo htmlspecialchars($search); ?>" style="width: 250px;">
                        </div>
                        
                        <div>
                            <label>Status:</label>
                            <select name="status" style="width: 120px;">
                                <option value="">Semua</option>
                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $status_filter == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $status_filter == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <div>
                            <label>Pembayaran:</label>
                            <select name="payment" style="width: 120px;">
                                <option value="">Semua</option>
                                <option value="pending" <?php echo $payment_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="paid" <?php echo $payment_filter == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                <option value="failed" <?php echo $payment_filter == 'failed' ? 'selected' : ''; ?>>Failed</option>
                                <option value="refunded" <?php echo $payment_filter == 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                            </select>
                        </div>
                        
                        <div>
                            <label>Dari:</label>
                            <input type="date" name="date_from" value="<?php echo $date_from; ?>">
                        </div>
                        
                        <div>
                            <label>Sampai:</label>
                            <input type="date" name="date_to" value="<?php echo $date_to; ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        
                        <a href="orders.php" class="btn btn-secondary">
                            <i class="fas fa-refresh"></i> Reset
                        </a>
                    </form>
                </div>
            </div>
            
            <!-- Orders Table -->
            <div class="card">
                <div class="card-header">
                    <h3>Daftar Pesanan (<?php echo $total_orders; ?> total)</h3>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No. Pesanan</th>
                                    <th>Pelanggan</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Pembayaran</th>
                                    <th>Pengiriman</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></strong><br>
                                            <small style="color: #666;"><?php echo htmlspecialchars($order['customer_email'] ?? ''); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $order['status'] == 'delivered' ? 'success' : 
                                                ($order['status'] == 'pending' ? 'warning' : 
                                                ($order['status'] == 'cancelled' ? 'danger' : 'info')); 
                                        ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $order['payment_status'] == 'paid' ? 'success' : 
                                                ($order['payment_status'] == 'pending' ? 'warning' : 'danger'); 
                                        ?>">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $order['shipping_status'] == 'delivered' ? 'success' : 
                                                ($order['shipping_status'] == 'pending' ? 'warning' : 'info'); 
                                        ?>">
                                            <?php echo ucfirst($order['shipping_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" onclick="viewOrder(<?php echo $order['id']; ?>)" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-warning btn-sm" onclick="editOrder(<?php echo $order['id']; ?>)" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteOrder(<?php echo $order['id']; ?>, '<?php echo htmlspecialchars($order['order_number']); ?>')" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div style="display: flex; justify-content: center; margin-top: 20px;">
                        <div style="display: flex; gap: 5px;">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&payment=<?php echo urlencode($payment_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" 
                                   class="btn <?php echo $i == $page ? 'btn-primary' : 'btn-secondary'; ?>" 
                                   style="padding: 8px 12px;">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Order Detail Modal -->
    <div id="orderModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 900px;">
            <div class="modal-header">
                <h3 id="modalTitle">Detail Pesanan</h3>
                <button onclick="closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
            </div>
            <div class="modal-body" id="orderDetails">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>
    
    <!-- Edit Order Modal -->
    <div id="editOrderModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3>Edit Pesanan <span id="editOrderNumber"></span></h3>
                <button onclick="closeEditModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
            </div>
            <form method="POST" id="editOrderForm">
                <input type="hidden" name="action" value="update_order">
                <input type="hidden" name="order_id" id="editOrderId">
                
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <!-- Status Section -->
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                        <h4 style="margin-bottom: 15px;">Status Pesanan</h4>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Status Pesanan</label>
                                <select name="status" id="editOrderStatus" class="form-control" required>
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="processing">Processing</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="refunded">Refunded</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Status Pembayaran</label>
                                <select name="payment_status" id="editPaymentStatus" class="form-control" required>
                                    <option value="pending">Pending</option>
                                    <option value="paid">Paid</option>
                                    <option value="failed">Failed</option>
                                    <option value="refunded">Refunded</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Status Pengiriman</label>
                                <select name="shipping_status" id="editShippingStatus" class="form-control" required>
                                    <option value="pending">Pending</option>
                                    <option value="preparing">Preparing</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="returned">Returned</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Shipping Address Section -->
                    <div style="background: #fff; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 20px;">
                        <h4 style="margin-bottom: 15px;">Alamat Pengiriman</h4>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nama Penerima</label>
                                <input type="text" name="shipping_name" id="editShippingName" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Telepon</label>
                                <input type="tel" name="shipping_phone" id="editShippingPhone" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Alamat Lengkap</label>
                            <textarea name="shipping_address" id="editShippingAddress" class="form-control" rows="2" required></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Kota</label>
                                <input type="text" name="shipping_city" id="editShippingCity" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Provinsi</label>
                                <input type="text" name="shipping_province" id="editShippingProvince" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Kode Pos</label>
                                <input type="text" name="shipping_postal_code" id="editShippingPostalCode" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Catatan Pengiriman</label>
                            <textarea name="shipping_notes" id="editShippingNotes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    
                    <!-- Order Notes -->
                    <div class="form-group">
                        <label class="form-label">Catatan Pesanan</label>
                        <textarea name="notes" id="editOrderNotes" class="form-control" rows="3" placeholder="Catatan tambahan untuk pesanan ini..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer" style="padding: 20px; border-top: 1px solid #f0f0f0; text-align: right;">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function viewOrder(orderId) {
            // Load order details via AJAX
            fetch(`ajax/get_order_details.php?id=${orderId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('orderDetails').innerHTML = html;
                    document.getElementById('orderModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memuat detail pesanan');
                });
        }
        
        function editOrder(orderId) {
            // Load order data for editing
            fetch(`ajax/get_order_data.php?id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    
                    // Set order ID and number
                    document.getElementById('editOrderId').value = orderId;
                    document.getElementById('editOrderNumber').textContent = '#' + data.order_number;
                    
                    // Set order status
                    document.getElementById('editOrderStatus').value = data.status;
                    document.getElementById('editPaymentStatus').value = data.payment_status;
                    document.getElementById('editShippingStatus').value = data.shipping_status;
                    document.getElementById('editOrderNotes').value = data.notes || '';
                    
                    // Set shipping address
                    document.getElementById('editShippingName').value = data.shipping.name || '';
                    document.getElementById('editShippingPhone').value = data.shipping.phone || '';
                    document.getElementById('editShippingAddress').value = data.shipping.address || '';
                    document.getElementById('editShippingCity').value = data.shipping.city || '';
                    document.getElementById('editShippingProvince').value = data.shipping.province || '';
                    document.getElementById('editShippingPostalCode').value = data.shipping.postal_code || '';
                    document.getElementById('editShippingNotes').value = data.shipping.notes || '';
                    
                    // Show modal
                    document.getElementById('editOrderModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memuat data pesanan');
                });
        }
        
        function deleteOrder(orderId, orderNumber) {
            if (confirm(`Apakah Anda yakin ingin menghapus pesanan ${orderNumber}?\n\nPeringatan: Data pesanan, items, dan pembayaran akan dihapus permanen!`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_order">
                    <input type="hidden" name="order_id" value="${orderId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
        }
        
        function closeEditModal() {
            document.getElementById('editOrderModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const orderModal = document.getElementById('orderModal');
            const editModal = document.getElementById('editOrderModal');
            if (event.target == orderModal) {
                closeModal();
            }
            if (event.target == editModal) {
                closeEditModal();
            }
        }
    </script>
    
    <style>
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            padding: 20px 30px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-body {
            padding: 30px;
        }
    </style>
</body>
</html>





