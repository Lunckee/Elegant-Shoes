<?php
session_start();
require_once 'config/database.php';

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

// Calculate totals
$subtotal = 0;
$total_items = 0;

foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $total_items += $item['quantity'];
}

$tax_rate = 0;
$tax = $subtotal * $tax_rate;
$shipping = 25000;
$total = $subtotal + $tax + $shipping;

// Get customer data if logged in
$customer = null;
if (isset($_SESSION['customer_id'])) {
    $stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->execute([$_SESSION['customer_id']]);
    $customer = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Elegant Shoes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 28px;
            font-weight: bold;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .page-title {
            font-size: 32px;
            margin-bottom: 30px;
            color: #333;
        }
        
        .checkout-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }
        
        .checkout-form {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #333;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        
        .form-label.required::after {
            content: ' *';
            color: #e74c3c;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        select.form-control {
            cursor: pointer;
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }
        
        .order-summary {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .summary-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #333;
        }
        
        .order-item {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            background: #f5f5f5;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .item-qty {
            font-size: 13px;
            color: #666;
        }
        
        .item-price {
            font-weight: 700;
            color: #667eea;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 15px;
        }
        
        .summary-row.total {
            border-top: 2px solid #f0f0f0;
            padding-top: 15px;
            margin-top: 15px;
            font-size: 20px;
            font-weight: 700;
            color: #667eea;
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            transition: all 0.3s;
            width: 100%;
            margin-top: 20px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 10px;
        }
        
        .payment-option {
            position: relative;
        }
        
        .payment-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }
        
        .payment-label {
            display: block;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .payment-option input[type="radio"]:checked + .payment-label {
            border-color: #667eea;
            background: #f0f4ff;
        }
        
        .payment-label i {
            font-size: 24px;
            display: block;
            margin-bottom: 8px;
            color: #667eea;
        }
        
        .payment-label span {
            font-size: 13px;
            font-weight: 600;
        }
        
        @media (max-width: 968px) {
            .checkout-layout {
                grid-template-columns: 1fr;
            }
            
            .order-summary {
                position: static;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <i class="fas fa-shoe-prints"></i> Elegant Shoes
            </div>
        </div>
    </header>
    
    <div class="container">
        <h1 class="page-title"><i class="fas fa-credit-card"></i> Checkout</h1>
        
        <?php if (!isset($_SESSION['customer_id'])): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Perhatian:</strong> Anda checkout sebagai tamu. <a href="login.php" style="color: #0c5460; font-weight: 700;">Login</a> untuk pengalaman lebih baik.
            </div>
        <?php endif; ?>
        
        <form method="POST" action="process_order.php" id="checkoutForm">
            <div class="checkout-layout">
                <!-- Checkout Form -->
                <div class="checkout-form">
                    <!-- Shipping Information -->
                    <div class="section-title"><i class="fas fa-shipping-fast"></i> Informasi Pengiriman</div>
                    
                    <div class="form-group">
                        <label class="form-label required">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" 
                               value="<?php echo $customer['name'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required">Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo $customer['email'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Telepon</label>
                            <input type="tel" name="phone" class="form-control" 
                                   value="<?php echo $customer['phone'] ?? ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Alamat Lengkap</label>
                        <textarea name="address" class="form-control" required><?php echo $customer['address'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required">Kota</label>
                            <input type="text" name="city" class="form-control" 
                                   value="<?php echo $customer['city'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Provinsi</label>
                            <input type="text" name="province" class="form-control" 
                                   value="<?php echo $customer['province'] ?? ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required">Kode Pos</label>
                            <input type="text" name="postal_code" class="form-control" 
                                   value="<?php echo $customer['postal_code'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Negara</label>
                            <input type="text" name="country" class="form-control" 
                                   value="<?php echo $customer['country'] ?? 'Indonesia'; ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Catatan Pesanan (Opsional)</label>
                        <textarea name="notes" class="form-control" placeholder="Contoh: Kirim pagi hari, dsb."></textarea>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="section-title" style="margin-top: 30px;">
                        <i class="fas fa-credit-card"></i> Metode Pembayaran
                    </div>
                    
                    <div class="payment-methods">
                        <div class="payment-option">
                            <input type="radio" name="payment_method" id="bank_transfer" value="bank_transfer" required>
                            <label for="bank_transfer" class="payment-label">
                                <i class="fas fa-university"></i>
                                <span>Transfer Bank</span>
                            </label>
                        </div>
                        
                        <div class="payment-option">
                            <input type="radio" name="payment_method" id="e_wallet" value="e_wallet">
                            <label for="e_wallet" class="payment-label">
                                <i class="fas fa-wallet"></i>
                                <span>E-Wallet</span>
                            </label>
                        </div>
                        
                        <div class="payment-option">
                            <input type="radio" name="payment_method" id="credit_card" value="credit_card">
                            <label for="credit_card" class="payment-label">
                                <i class="fas fa-credit-card"></i>
                                <span>Kartu Kredit</span>
                            </label>
                        </div>
                        
                        <div class="payment-option">
                            <input type="radio" name="payment_method" id="cod" value="cod">
                            <label for="cod" class="payment-label">
                                <i class="fas fa-money-bill-wave"></i>
                                <span>COD</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="order-summary">
                    <div class="summary-title">Ringkasan Pesanan</div>
                    
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div class="order-item">
                            <img src="<?php echo $item['image'] ? htmlspecialchars($item['image']) : 'assets/img/no-image.svg'; ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 class="item-image">
                            <div class="item-info">
                                <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="item-qty">Qty: <?php echo $item['quantity']; ?></div>
                            </div>
                            <div class="item-price">
                                Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div style="margin-top: 20px;">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></span>
                        </div>
                        
                        <?php if ($tax > 0): ?>
                        <div class="summary-row">
                            <span>Pajak</span>
                            <span>Rp <?php echo number_format($tax, 0, ',', '.'); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="summary-row">
                            <span>Ongkos Kirim</span>
                            <span>Rp <?php echo number_format($shipping, 0, ',', '.'); ?></span>
                        </div>
                        
                        <div class="summary-row total">
                            <span>Total Pembayaran</span>
                            <span>Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" id="btnSubmit">
                        <i class="fas fa-check-circle"></i> Buat Pesanan
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <script>
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('btnSubmit');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        });
    </script>
</body>
</html>



