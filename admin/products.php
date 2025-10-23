<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireLogin();

function generateSlug($string) {
	$slug = strtolower(trim($string));
	$slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
	$slug = preg_replace('/[\s-]+/', '-', $slug);
	$slug = trim($slug, '-');
	return $slug ?: uniqid('prod-');
}

// Convert empty string to NULL for numeric/decimal fields
function sanitizeNumeric($value) {
	return ($value === '' || $value === null) ? null : $value;
}

// Convert empty string to NULL for text fields that allow NULL
function sanitizeText($value) {
	return ($value === '' || $value === null) ? null : $value;
}

// Function to upload product image
function uploadProductImage($file, $productName) {
    $uploadDir = '../uploads/products/';
    
    // Create directory if not exists
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Validate file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'error' => 'Format file tidak didukung. Gunakan JPG, PNG, atau WEBP.'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'Ukuran file terlalu besar. Maksimal 5MB.'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = generateSlug($productName) . '-' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'path' => 'uploads/products/' . $filename];
    }
    
    return ['success' => false, 'error' => 'Gagal mengupload file.'];
}

// Handle CRUD operations
if ($_POST) {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? '';
    
    switch ($action) {
        case 'create':
            $name = $_POST['name'] ?? '';
            $slug = $_POST['slug'] ?? '';
            if ($slug === '' || $slug === null) {
            	$slug = generateSlug($name);
            }
            $stmt = $db->prepare("INSERT INTO products (category_id, name, slug, description, short_description, price, sale_price, sku, stock, min_stock, color, size, material, brand, status, featured, meta_title, meta_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['category_id'], 
                $name, 
                $slug, 
                sanitizeText($_POST['description'] ?? null),
                sanitizeText($_POST['short_description'] ?? null), 
                sanitizeNumeric($_POST['price'] ?? null), 
                sanitizeNumeric($_POST['sale_price'] ?? null), 
                $_POST['sku'],
                sanitizeNumeric($_POST['stock'] ?? null), 
                sanitizeNumeric($_POST['min_stock'] ?? null), 
                sanitizeText($_POST['color'] ?? null), 
                sanitizeText($_POST['size'] ?? null),
                sanitizeText($_POST['material'] ?? null), 
                sanitizeText($_POST['brand'] ?? null), 
                $_POST['status'], 
                $_POST['featured'] ?? 0,
                sanitizeText($_POST['meta_title'] ?? null), 
                sanitizeText($_POST['meta_description'] ?? null)
            ]);
            
            $productId = $db->lastInsertId();
            
            // Handle image upload
            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
                $uploadResult = uploadProductImage($_FILES['product_image'], $name);
                if ($uploadResult['success']) {
                    $imgStmt = $db->prepare("INSERT INTO product_images (product_id, image_path, alt_text, is_primary, sort_order) VALUES (?, ?, ?, 1, 0)");
                    $imgStmt->execute([$productId, $uploadResult['path'], $name]);
                }
            }
            
            logActivity('create', 'products', $productId, null, $_POST);
            header('Location: products.php?success=created');
            exit();
            
        case 'update':
            $name = $_POST['name'] ?? '';
            $slug = $_POST['slug'] ?? '';
            if ($slug === '' || $slug === null) {
            	$slug = generateSlug($name);
            }
            $stmt = $db->prepare("UPDATE products SET category_id=?, name=?, slug=?, description=?, short_description=?, price=?, sale_price=?, sku=?, stock=?, min_stock=?, color=?, size=?, material=?, brand=?, status=?, featured=?, meta_title=?, meta_description=? WHERE id=?");
            $stmt->execute([
                $_POST['category_id'], 
                $name, 
                $slug, 
                sanitizeText($_POST['description'] ?? null),
                sanitizeText($_POST['short_description'] ?? null), 
                sanitizeNumeric($_POST['price'] ?? null), 
                sanitizeNumeric($_POST['sale_price'] ?? null), 
                $_POST['sku'],
                sanitizeNumeric($_POST['stock'] ?? null), 
                sanitizeNumeric($_POST['min_stock'] ?? null), 
                sanitizeText($_POST['color'] ?? null), 
                sanitizeText($_POST['size'] ?? null),
                sanitizeText($_POST['material'] ?? null), 
                sanitizeText($_POST['brand'] ?? null), 
                $_POST['status'], 
                $_POST['featured'] ?? 0,
                sanitizeText($_POST['meta_title'] ?? null), 
                sanitizeText($_POST['meta_description'] ?? null), 
                $id
            ]);
            
            // Handle image upload
            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
                $uploadResult = uploadProductImage($_FILES['product_image'], $name);
                if ($uploadResult['success']) {
                    // Delete old primary image
                    $oldImgStmt = $db->prepare("SELECT image_path FROM product_images WHERE product_id = ? AND is_primary = 1");
                    $oldImgStmt->execute([$id]);
                    $oldImage = $oldImgStmt->fetch();
                    
                    if ($oldImage && file_exists('../' . $oldImage['image_path'])) {
                        unlink('../' . $oldImage['image_path']);
                    }
                    
                    // Remove old primary image record or update it
                    $db->prepare("DELETE FROM product_images WHERE product_id = ? AND is_primary = 1")->execute([$id]);
                    
                    // Insert new image
                    $imgStmt = $db->prepare("INSERT INTO product_images (product_id, image_path, alt_text, is_primary, sort_order) VALUES (?, ?, ?, 1, 0)");
                    $imgStmt->execute([$id, $uploadResult['path'], $name]);
                }
            }
            
            logActivity('update', 'products', $id, null, $_POST);
            header('Location: products.php?success=updated');
            exit();
            
        case 'delete':
            // Delete product images first
            $imgStmt = $db->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
            $imgStmt->execute([$id]);
            $images = $imgStmt->fetchAll();
            
            foreach ($images as $image) {
                if (file_exists('../' . $image['image_path'])) {
                    unlink('../' . $image['image_path']);
                }
            }
            
            $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            
            logActivity('delete', 'products', $id, null, null);
            header('Location: products.php?success=deleted');
            exit();
    }
}

// Get products with pagination
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(p.name LIKE ? OR p.sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_filter) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

if ($status_filter) {
    $where_conditions[] = "p.status = ?";
    $params[] = $status_filter;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get products
$stmt = $db->prepare("SELECT p.*, c.name as category_name, pi.image_path 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                     $where_clause 
                     ORDER BY p.created_at DESC LIMIT ? OFFSET ?");
$params[] = $limit;
$params[] = $offset;
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get total count
$count_stmt = $db->prepare("SELECT COUNT(*) FROM products p $where_clause");
$count_stmt->execute(array_slice($params, 0, -2));
$total_products = $count_stmt->fetchColumn();
$total_pages = ceil($total_products / $limit);

// Get categories for filter
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Produk - Elegant Shoes Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/top-nav.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1><i class="fas fa-box"></i> Management Produk</h1>
                <div class="breadcrumb">
                    <a href="dashboard.php">Dashboard</a> / <span>Management Produk</span>
                </div>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 
                    Produk berhasil <?php echo $_GET['success'] == 'created' ? 'ditambahkan' : ($_GET['success'] == 'updated' ? 'diperbarui' : 'dihapus'); ?>!
                </div>
            <?php endif; ?>
            
            <!-- Filters and Actions -->
            <div class="card">
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                            <div>
                                <input type="text" id="search" placeholder="Cari produk..." value="<?php echo htmlspecialchars($search); ?>" style="width: 250px;">
                            </div>
                            <div>
                                <select id="category-filter" style="width: 150px;">
                                    <option value="">Semua Kategori</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <select id="status-filter" style="width: 120px;">
                                    <option value="">Semua Status</option>
                                    <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Tidak Aktif</option>
                                    <option value="draft" <?php echo $status_filter == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                </select>
                            </div>
                            <button class="btn btn-primary" onclick="applyFilters()">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                        
                        <div>
                            <button class="btn btn-success" onclick="showProductModal()">
                                <i class="fas fa-plus"></i> Tambah Produk
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Products Table -->
            <div class="card">
                <div class="card-header">
                    <h3>Daftar Produk (<?php echo $total_products; ?> total)</h3>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table" id="productsTable">
                            <thead>
                                <tr>
                                    <th>Gambar</th>
                                    <th>Nama Produk</th>
                                    <th>SKU</th>
                                    <th>Kategori</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <img src="../<?php echo $product['image_path'] ?? 'assets/img/no-image.svg'; ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                        <?php if ($product['featured']): ?>
                                            <span class="badge badge-warning" style="margin-left: 5px;">Featured</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['sku']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if ($product['sale_price']): ?>
                                            <span style="text-decoration: line-through; color: #999;">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></span><br>
                                            <strong style="color: #e74c3c;">Rp <?php echo number_format($product['sale_price'], 0, ',', '.'); ?></strong>
                                        <?php else: ?>
                                            Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $product['stock'] <= $product['min_stock'] ? 'danger' : ($product['stock'] <= ($product['min_stock'] * 2) ? 'warning' : 'success'); ?>">
                                            <?php echo $product['stock']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $product['status'] == 'active' ? 'success' : ($product['status'] == 'inactive' ? 'danger' : 'warning'); ?>">
                                            <?php echo ucfirst($product['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteProduct(<?php echo $product['id']; ?>)">
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
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&status=<?php echo urlencode($status_filter); ?>" 
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
    
    <!-- Product Modal -->
    <div id="productModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3 id="modalTitle">Tambah Produk</h3>
                <button onclick="closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
            </div>
            <form id="productForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="productId">
                
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <!-- Image Upload Section -->
                    <div class="form-group">
                        <label class="form-label">Gambar Produk</label>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <input type="file" name="product_image" id="productImage" accept="image/*" class="form-control" onchange="previewImage(event)">
                            <div id="imagePreview" style="display: none; margin-top: 10px;">
                                <img id="preview" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #ddd; padding: 5px;">
                            </div>
                            <div id="currentImagePreview" style="display: none; margin-top: 10px;">
                                <p style="margin: 0 0 5px 0; font-size: 12px; color: #666;">Gambar saat ini:</p>
                                <img id="currentImage" src="" alt="Current Image" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #ddd; padding: 5px;">
                            </div>
                            <small style="color: #666;">Format: JPG, PNG, WEBP. Maksimal 5MB.</small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Nama Produk *</label>
                            <input type="text" name="name" id="productName" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="productSlug" class="form-control" placeholder="otomatis dari nama jika kosong">
                        </div>
                        <div class="form-group">
                            <label class="form-label">SKU *</label>
                            <input type="text" name="sku" id="productSku" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Kategori *</label>
                            <select name="category_id" id="productCategory" class="form-control" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" id="productStatus" class="form-control">
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Harga Normal *</label>
                            <input type="number" name="price" id="productPrice" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Harga Diskon</label>
                            <input type="number" name="sale_price" id="productSalePrice" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Stok *</label>
                            <input type="number" name="stock" id="productStock" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Stok Minimum</label>
                            <input type="number" name="min_stock" id="productMinStock" class="form-control" value="5">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Warna</label>
                            <input type="text" name="color" id="productColor" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ukuran</label>
                            <input type="text" name="size" id="productSize" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Material</label>
                            <input type="text" name="material" id="productMaterial" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Brand</label>
                            <input type="text" name="brand" id="productBrand" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Deskripsi Singkat</label>
                        <textarea name="short_description" id="productShortDesc" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Deskripsi Lengkap</label>
                        <textarea name="description" id="productDescription" class="form-control" rows="4"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Meta Title</label>
                            <input type="text" name="meta_title" id="productMetaTitle" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Featured</label>
                            <select name="featured" id="productFeatured" class="form-control">
                                <option value="0">Tidak</option>
                                <option value="1">Ya</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" id="productMetaDesc" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer" style="padding: 20px; border-top: 1px solid #f0f0f0; text-align: right;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function applyFilters() {
            const search = document.getElementById('search').value;
            const category = document.getElementById('category-filter').value;
            const status = document.getElementById('status-filter').value;
            
            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (category) params.append('category', category);
            if (status) params.append('status', status);
            
            window.location.href = 'products.php?' + params.toString();
        }
        
        function previewImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('preview');
            const imagePreview = document.getElementById('imagePreview');
            const currentImagePreview = document.getElementById('currentImagePreview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    imagePreview.style.display = 'block';
                    currentImagePreview.style.display = 'none';
                }
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
            }
        }
        
        function showProductModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Produk';
            document.getElementById('formAction').value = 'create';
            document.getElementById('productForm').reset();
            document.getElementById('productSlug').value = '';
            document.getElementById('imagePreview').style.display = 'none';
            document.getElementById('currentImagePreview').style.display = 'none';
            document.getElementById('productModal').style.display = 'block';
        }
        
        function editProduct(product) {
            document.getElementById('modalTitle').textContent = 'Edit Produk';
            document.getElementById('formAction').value = 'update';
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productSku').value = product.sku;
            document.getElementById('productCategory').value = product.category_id;
            document.getElementById('productStatus').value = product.status;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productSalePrice').value = product.sale_price || '';
            document.getElementById('productStock').value = product.stock;
            document.getElementById('productMinStock').value = product.min_stock;
            document.getElementById('productColor').value = product.color || '';
            document.getElementById('productSize').value = product.size || '';
            document.getElementById('productMaterial').value = product.material || '';
            document.getElementById('productBrand').value = product.brand || '';
            document.getElementById('productShortDesc').value = product.short_description || '';
            document.getElementById('productDescription').value = product.description || '';
            document.getElementById('productMetaTitle').value = product.meta_title || '';
            document.getElementById('productMetaDesc').value = product.meta_description || '';
            document.getElementById('productFeatured').value = product.featured;
            document.getElementById('productSlug').value = product.slug || '';
            
            // Show current image if exists
            const currentImage = document.getElementById('currentImage');
            const currentImagePreview = document.getElementById('currentImagePreview');
            const imagePreview = document.getElementById('imagePreview');
            
            imagePreview.style.display = 'none';
            
            if (product.image_path) {
                currentImage.src = '../' + product.image_path;
                currentImagePreview.style.display = 'block';
            } else {
                currentImagePreview.style.display = 'none';
            }
            
            document.getElementById('productModal').style.display = 'block';
        }
        
        function deleteProduct(id) {
            if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function closeModal() {
            document.getElementById('productModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        // Auto-generate slug from product name unless user overrides
        (function () {
            const nameInput = document.getElementById('productName');
            const slugInput = document.getElementById('productSlug');
            let userOverrode = false;

            function slugify(text) {
                return text
                    .toLowerCase()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9\s-]/g, '')
                    .trim()
                    .replace(/[\s-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }

            slugInput.addEventListener('input', function () {
                userOverrode = slugInput.value.trim().length > 0;
            });

            nameInput.addEventListener('input', function () {
                if (!userOverrode) {
                    slugInput.value = slugify(nameInput.value);
                }
            });
        })();
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
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            color: white;
        }
    </style>
</body>
</html>



