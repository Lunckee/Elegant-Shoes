<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
requireLogin();

$order_id = $_GET['id'] ?? 0;

try {
    // Get order data
    $stmt = $db->prepare("
        SELECT o.*, c.name as customer_name, c.email as customer_email, c.phone as customer_phone,
               sa.name as shipping_name, sa.phone as shipping_phone,
               sa.address, sa.city, sa.province, sa.postal_code, sa.notes as shipping_notes,
               p.payment_method
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        LEFT JOIN shipping_addresses sa ON o.id = sa.order_id
        LEFT JOIN payments p ON o.id = p.order_id
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        echo '<p>Pesanan tidak ditemukan</p>';
        exit();
    }
    
    // Get order items
    $stmt = $db->prepare("
        SELECT oi.*, p.name as product_name, p.sku, pi.image_path
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();
    
    ?>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        <!-- Left Column -->
        <div>
            <h4 style="margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0;">Informasi Pesanan</h4>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 10px 0; font-weight: 600;">No. Pesanan:</td>
                    <td style="padding: 10px 0;"><?php echo htmlspecialchars($order['order_number']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; font-weight: 600;">Tanggal:</td>
                    <td style="padding: 10px 0;"><?php echo date('d F Y, H:i', strtotime($order['order_date'])); ?> WIB</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; font-weight: 600;">Status:</td>
                    <td style="padding: 10px 0;">
                        <span class="badge badge-<?php 
                            echo $order['status'] == 'delivered' ? 'success' : 
                                ($order['status'] == 'pending' ? 'warning' : 
                                ($order['status'] == 'cancelled' ? 'danger' : 'info')); 
                        ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; font-weight: 600;">Pembayaran:</td>
                    <td style="padding: 10px 0;">
                        <span class="badge badge-<?php 
                            echo $order['payment_status'] == 'paid' ? 'success' : 
                                ($order['payment_status'] == 'pending' ? 'warning' : 'danger'); 
                        ?>">
                            <?php echo ucfirst($order['payment_status']); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; font-weight: 600;">Metode:</td>
                    <td style="padding: 10px 0;">
                        <?php 
                        $payment_methods = [
                            'bank_transfer' => 'Transfer Bank',
                            'e_wallet' => 'E-Wallet',
                            'credit_card' => 'Kartu Kredit',
                            'cod' => 'COD'
                        ];
                        echo $payment_methods[$order['payment_method']] ?? $order['payment_method'];
                        ?>
                    </td>
                </tr>
            </table>
            
            <h4 style="margin: 20px 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0;">Data Customer</h4>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 10px 0; font-weight: 600;">Nama:</td>
                    <td style="padding: 10px 0;"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; font-weight: 600;">Email:</td>
                    <td style="padding: 10px 0;"><?php echo htmlspecialchars($order['customer_email']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; font-weight: 600;">Telepon:</td>
                    <td style="padding: 10px 0;"><?php echo htmlspecialchars($order['customer_phone'] ?? 'N/A'); ?></td>
                </tr>
            </table>
        </div>
        
        <!-- Right Column -->
        <div>
            <h4 style="margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0;">Alamat Pengiriman</h4>
            <p style="margin: 5px 0;"><strong><?php echo htmlspecialchars($order['shipping_name']); ?></strong></p>
            <p style="margin: 5px 0;"><?php echo htmlspecialchars($order['shipping_phone']); ?></p>
            <p style="margin: 10px 0;"><?php echo htmlspecialchars($order['address']); ?></p>
            <p style="margin: 5px 0;"><?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['province']); ?> <?php echo htmlspecialchars($order['postal_code']); ?></p>
            <?php if ($order['shipping_notes']): ?>
                <p style="margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                    <strong>Catatan:</strong> <?php echo htmlspecialchars($order['shipping_notes']); ?>
                </p>
            <?php endif; ?>
            
            <?php if ($order['notes']): ?>
                <h4 style="margin: 20px 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0;">Catatan Pesanan</h4>
                <p style="margin: 10px 0; padding: 10px; background: #fff3cd; border-radius: 5px;">
                    <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Order Items -->
    <h4 style="margin: 30px 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0;">Produk yang Dipesan</h4>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8f9fa;">
                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Produk</th>
                <th style="padding: 12px; text-align: center; border-bottom: 2px solid #ddd;">SKU</th>
                <th style="padding: 12px; text-align: center; border-bottom: 2px solid #ddd;">Qty</th>
                <th style="padding: 12px; text-align: right; border-bottom: 2px solid #ddd;">Harga</th>
                <th style="padding: 12px; text-align: right; border-bottom: 2px solid #ddd;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td style="padding: 12px; border-bottom: 1px solid #f0f0f0;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <img src="../../<?php echo $item['image_path'] ?? 'assets/img/no-image.svg'; ?>" 
                             alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                        <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                    </div>
                </td>
                <td style="padding: 12px; text-align: center; border-bottom: 1px solid #f0f0f0;">
                    <?php echo htmlspecialchars($item['sku']); ?>
                </td>
                <td style="padding: 12px; text-align: center; border-bottom: 1px solid #f0f0f0;">
                    <strong><?php echo $item['quantity']; ?></strong>
                </td>
                <td style="padding: 12px; text-align: right; border-bottom: 1px solid #f0f0f0;">
                    Rp <?php echo number_format($item['price'], 0, ',', '.'); ?>
                </td>
                <td style="padding: 12px; text-align: right; border-bottom: 1px solid #f0f0f0;">
                    <strong>Rp <?php echo number_format($item['total'], 0, ',', '.'); ?></strong>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <!-- Order Summary -->
    <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
        <table style="width: 100%; max-width: 400px; margin-left: auto;">
            <tr>
                <td style="padding: 8px 0;">Subtotal:</td>
                <td style="padding: 8px 0; text-align: right;">Rp <?php echo number_format($order['subtotal'], 0, ',', '.'); ?></td>
            </tr>
            <?php if ($order['tax_amount'] > 0): ?>
            <tr>
                <td style="padding: 8px 0;">Pajak:</td>
                <td style="padding: 8px 0; text-align: right;">Rp <?php echo number_format($order['tax_amount'], 0, ',', '.'); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td style="padding: 8px 0;">Ongkos Kirim:</td>
                <td style="padding: 8px 0; text-align: right;">Rp <?php echo number_format($order['shipping_cost'], 0, ',', '.'); ?></td>
            </tr>
            <?php if ($order['discount_amount'] > 0): ?>
            <tr>
                <td style="padding: 8px 0;">Diskon:</td>
                <td style="padding: 8px 0; text-align: right; color: #27ae60;">-Rp <?php echo number_format($order['discount_amount'], 0, ',', '.'); ?></td>
            </tr>
            <?php endif; ?>
            <tr style="border-top: 2px solid #ddd;">
                <td style="padding: 15px 0 0 0; font-size: 18px; font-weight: 700;">Total:</td>
                <td style="padding: 15px 0 0 0; text-align: right; font-size: 20px; font-weight: 700; color: #667eea;">
                    Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                </td>
            </tr>
        </table>
    </div>
    
    <?php
    
} catch (Exception $e) {
    echo '<p style="color: #e74c3c;">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>



