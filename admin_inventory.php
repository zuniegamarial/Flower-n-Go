<?php
// admin_inventory.php - AUTO-FIXING VERSION
session_start();
include "db.php";

// Check admin login
if (!isset($_SESSION['user_id'])) {
    header("Location: sign_in.php");
    exit();
}

// Check if user_type is set in session
if (!isset($_SESSION['user_type'])) {
    $_SESSION['user_type'] = 'customer'; // Default
}

if ($_SESSION['user_type'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}

// AUTO-FIX: Check and create stock column if missing
$check = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'stock'");
if(mysqli_num_rows($check) == 0) {
    mysqli_query($conn, "ALTER TABLE products ADD COLUMN stock INT DEFAULT 10");
}

// Handle stock update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['update_stock'])) {
        $product_id = intval($_POST['product_id']);
        $new_stock = intval($_POST['stock']);
        
        $update_sql = "UPDATE products SET stock = $new_stock WHERE id = $product_id";
        if (mysqli_query($conn, $update_sql)) {
            $success = "Stock updated successfully!";
        }
    }
    
    if(isset($_POST['restock'])) {
        $product_id = intval($_POST['product_id']);
        $restock_amount = intval($_POST['restock_amount']);
        
        // Get current stock
        $current_query = mysqli_query($conn, "SELECT stock FROM products WHERE id = $product_id");
        if($current = mysqli_fetch_assoc($current_query)) {
            $new_stock = $current['stock'] + $restock_amount;
            mysqli_query($conn, "UPDATE products SET stock = $new_stock WHERE id = $product_id");
            $success = "Restocked successfully!";
        }
    }
}

// Get all active products
$products_query = mysqli_query($conn, "
    SELECT id, name, price, COALESCE(stock, 10) as stock, image
    FROM products 
    WHERE is_active = 1 
    ORDER BY name ASC
");

// Calculate stats
$stats_query = mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_products,
        COALESCE(SUM(stock), 0) as total_stock,
        SUM(CASE WHEN COALESCE(stock, 0) = 0 THEN 1 ELSE 0 END) as out_of_stock,
        SUM(CASE WHEN COALESCE(stock, 0) <= 5 AND COALESCE(stock, 0) > 0 THEN 1 ELSE 0 END) as low_stock
    FROM products 
    WHERE is_active = 1
");
$stats = mysqli_fetch_assoc($stats_query) ?: ['total_products' => 0, 'total_stock' => 0, 'out_of_stock' => 0, 'low_stock' => 0];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory - Flower 'n Go</title>
    <style>
        body { background:#0f0f0f; color:#fff; font-family:Arial; padding:20px; }
        .container { max-width:1200px; margin:0 auto; }
        .back-btn { background:#333; color:#fff; padding:10px 20px; text-decoration:none; border-radius:5px; display:inline-block; margin-bottom:20px; }
        .stats { display:flex; gap:20px; margin-bottom:30px; }
        .stat-card { background:#222; padding:20px; border-radius:10px; flex:1; text-align:center; }
        .stat-number { font-size:32px; font-weight:bold; margin:10px 0; }
        table { width:100%; background:#222; border-radius:10px; overflow:hidden; }
        th { background:#111; padding:15px; text-align:left; }
        td { padding:15px; border-bottom:1px solid #333; }
        .status { padding:5px 10px; border-radius:20px; font-size:12px; }
        .in-stock { background:#10b98120; color:#10b981; }
        .low-stock { background:#f59e0b20; color:#f59e0b; }
        .out-stock { background:#ef444420; color:#ef4444; }
        .stock-input { width:80px; padding:8px; background:#111; border:1px solid #333; color:#fff; border-radius:5px; }
        .btn { padding:8px 15px; border:none; border-radius:5px; cursor:pointer; }
        .btn-update { background:#D4AF37; color:#000; }
        .btn-add { background:#3b82f6; color:#fff; }
        .alert { background:#ef444420; border-left:4px solid #ef4444; padding:15px; margin:20px 0; border-radius:5px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
        
        <h1>📦 Inventory Management</h1>
        
        <?php if(isset($success)): ?>
            <div style="background:#10b98120; color:#10b981; padding:10px; border-radius:5px; margin-bottom:20px;">
                ✅ <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <!-- Simple Stats -->
        <div class="stats">
            <div class="stat-card">
                <div>Total Products</div>
                <div class="stat-number"><?php echo $stats['total_products']; ?></div>
            </div>
            <div class="stat-card">
                <div>Total Stock</div>
                <div class="stat-number"><?php echo $stats['total_stock']; ?></div>
            </div>
            <div class="stat-card">
                <div>Out of Stock</div>
                <div class="stat-number" style="color:#ef4444;"><?php echo $stats['out_of_stock']; ?></div>
            </div>
        </div>
        
        <!-- Products Table -->
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($products_query) > 0): ?>
                    <?php while($product = mysqli_fetch_assoc($products_query)): 
                        $status = 'in-stock';
                        $status_text = 'In Stock';
                        if($product['stock'] == 0) {
                            $status = 'out-stock';
                            $status_text = 'Out of Stock';
                        } elseif($product['stock'] <= 5) {
                            $status = 'low-stock';
                            $status_text = 'Low Stock';
                        }
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($product['name']); ?></strong><br>
                            <small style="color:#999;">₱<?php echo number_format($product['price'], 2); ?></small>
                        </td>
                        <td style="font-size:20px; font-weight:bold;"><?php echo $product['stock']; ?></td>
                        <td><span class="status <?php echo $status; ?>"><?php echo $status_text; ?></span></td>
                        <td>
                            <form method="POST" style="display:flex; gap:10px;">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="number" name="stock" value="<?php echo $product['stock']; ?>" class="stock-input" min="0">
                                <button type="submit" name="update_stock" class="btn btn-update">Update</button>
                                <button type="submit" name="restock" class="btn btn-add" 
                                        onclick="this.form.querySelector('input[name=stock]').value = parseInt(this.form.querySelector('input[name=stock]').value) + 10;">
                                    +10
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding:40px; color:#999;">
                            No products found. Add products first.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>