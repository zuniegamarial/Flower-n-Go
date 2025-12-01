<?php
// admin_dashboard.php
// Admin dashboard for Flower Shop e-commerce
// Place this file in your admin folder and require login to set $_SESSION['user_id'] and $_SESSION['is_admin'] = 1

session_start();

// -------------------------
// Configuration - set your DB values here
// -------------------------
$db_host = 'localhost';
$db_name = 'flower_shop';
$db_user = 'db_user';
$db_pass = 'db_pass';

// Adjust this path if you store config elsewhere
// -------------------------
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo "Database connection failed: " . htmlspecialchars($e->getMessage());
    exit;
}

// -------------------------
// Authentication check
// -------------------------
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    // Not logged in as admin — redirect to login page
    header('Location: /login.php');
    exit;
}

// Simple CSRF token helper
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function check_csrf($token) {
    return hash_equals($_SESSION['csrf_token'], $token ?? '');
}

// Helper: sanitize output
function e($s) { return htmlspecialchars($s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }

// -------------------------
// API endpoints (AJAX) — keep in this same file for simplicity
// Access with ?action=...
// -------------------------
$action = $_GET['action'] ?? null;
if ($action) {
    header('Content-Type: application/json');
    try {
        if ($action === 'get_orders') {
            $stmt = $pdo->prepare('SELECT o.id, o.created_at, o.total_amount, o.status, u.name AS customer_name
                                   FROM orders o
                                   LEFT JOIN users u ON u.id = o.user_id
                                   ORDER BY o.created_at DESC
                                   LIMIT 100');
            $stmt->execute();
            $rows = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $rows]);
            exit;
        }

        if ($action === 'get_products') {
            $stmt = $pdo->prepare('SELECT id, name, sku, price, stock, image_url FROM products ORDER BY name');
            $stmt->execute();
            $rows = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $rows]);
            exit;
        }

        if ($action === 'get_low_stock') {
            $threshold = (int)($_GET['threshold'] ?? 5);
            $stmt = $pdo->prepare('SELECT id, name, stock FROM products WHERE stock <= ? ORDER BY stock ASC');
            $stmt->execute([$threshold]);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            exit;
        }

        if ($action === 'get_sales_chart') {
            // return simple sales by day for the last 30 days
            $stmt = $pdo->prepare('SELECT DATE(created_at) AS day, SUM(total_amount) AS total
                                   FROM orders
                                   WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
                                   GROUP BY DATE(created_at)
                                   ORDER BY day');
            $stmt->execute();
            $rows = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $rows]);
            exit;
        }

        if ($action === 'update_stock' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!check_csrf($input['csrf'] ?? '')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
                exit;
            }
            $product_id = (int)($input['id'] ?? 0);
            $new_stock = (int)($input['stock'] ?? 0);
            if ($product_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid product id']);
                exit;
            }
            $stmt = $pdo->prepare('UPDATE products SET stock = ? WHERE id = ?');
            $stmt->execute([$new_stock, $product_id]);
            echo json_encode(['success' => true]);
            exit;
        }

        // unknown action
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// -------------------------
// If not an AJAX action: render the HTML dashboard
// -------------------------
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Dashboard — Flower Shop</title>

    <!-- CSS libraries: Bootstrap 5 & DataTables -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">

    <style>
        body { padding-top: 56px; }
        .sidebar { min-height: calc(100vh - 56px); }
        .card-quick { cursor: pointer; }
        .product-img { width: 48px; height: 48px; object-fit: cover; border-radius: 6px; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Flower Shop Admin</a>
    <div class="d-flex align-items-center">
      <span class="text-white me-3">Hi, <?php echo e($_SESSION['user_name'] ?? 'Admin'); ?></span>
      <a href="/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container-fluid">
  <div class="row">
    <nav class="col-md-2 d-none d-md-block bg-light sidebar py-4">
      <div class="position-sticky">
        <ul class="nav flex-column">
          <li class="nav-item"><a class="nav-link active" href="#overview">Overview</a></li>
          <li class="nav-item"><a class="nav-link" href="#orders">Orders</a></li>
          <li class="nav-item"><a class="nav-link" href="#products">Products</a></li>
          <li class="nav-item"><a class="nav-link" href="#customers">Customers</a></li>
          <li class="nav-item"><a class="nav-link" href="#analytics">Analytics</a></li>
        </ul>
      </div>
    </nav>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
      <h1 class="h2" id="overview">Dashboard Overview</h1>

      <div class="row g-3 my-3">
        <div class="col-6 col-md-3">
          <div class="card p-3 card-quick" id="card-orders">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-0">Recent Orders</h6>
                <small id="orders-count">—</small>
              </div>
              <div class="fs-3">📦</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-0">Products</h6>
                <small id="products-count">—</small>
              </div>
              <div class="fs-3">🌸</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-0">Low Stock</h6>
                <small id="lowstock-count">—</small>
              </div>
              <div class="fs-3">⚠️</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-0">Today Sales</h6>
                <small id="today-sales">—</small>
              </div>
              <div class="fs-3">💵</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Orders table -->
      <section id="orders" class="my-4">
        <h3>Orders</h3>
        <table id="orders-table" class="display table table-striped" style="width:100%">
          <thead><tr><th>ID</th><th>Date</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
          <tbody></tbody>
        </table>
      </section>

      <!-- Products table -->
      <section id="products" class="my-4">
        <h3>Products</h3>
        <table id="products-table" class="display table table-hover" style="width:100%">
          <thead><tr><th>Image</th><th>Name</th><th>SKU</th><th>Price</th><th>Stock</th><th>Action</th></tr></thead>
          <tbody></tbody>
        </table>
      </section>

      <!-- Analytics -->
      <section id="analytics" class="my-4">
        <h3>Analytics</h3>
        <canvas id="salesChart" height="100"></canvas>
      </section>

    </main>
  </div>
</div>

<!-- JS libraries -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
const CSRF_TOKEN = '<?php echo e($_SESSION['csrf_token']); ?>';

async function fetchJSON(q) {
    const res = await fetch(q);
    return res.json();
}

$(document).ready(async function(){
    // Orders
    const ordersResp = await fetchJSON('?action=get_orders');
    if (ordersResp.success) {
        $('#orders-count').text(ordersResp.data.length + ' recent');
        const table = $('#orders-table').DataTable({ data: ordersResp.data, columns: [
            { data: 'id' },
            { data: 'created_at' },
            { data: 'customer_name' },
            { data: 'total_amount', render: function(d){ return '₱' + parseFloat(d).toFixed(2); } },
            { data: 'status' }
        ]});
    }

    // Products
    const prodResp = await fetchJSON('?action=get_products');
    if (prodResp.success) {
        $('#products-count').text(prodResp.data.length);
        const table = $('#products-table').DataTable({ data: prodResp.data, columns: [
            { data: 'image_url', render: function(d){ return '<img src="'+(d||'/images/noimage.png')+'" class="product-img"/>'; } },
            { data: 'name' },
            { data: 'sku' },
            { data: 'price', render: function(d){ return '₱' + parseFloat(d).toFixed(2); } },
            { data: 'stock' },
            { data: null, render: function(row){ return '<button class="btn btn-sm btn-outline-primary btn-edit-stock" data-id="'+row.id+'">Edit</button>'; } }
        ]});

        // bind edit stock
        $('#products-table tbody').on('click', 'button.btn-edit-stock', function(){
            const data = table.row($(this).parents('tr')).data();
            const newStock = prompt('Update stock for: '+data.name, data.stock);
            if (newStock !== null) {
                updateStock(data.id, parseInt(newStock,10));
            }
        });
    }

    // Low stock
    const lowResp = await fetchJSON('?action=get_low_stock&threshold=5');
    if (lowResp.success) {
        $('#lowstock-count').text(lowResp.data.length + ' items');
    }

    // Sales chart
    const salesResp = await fetchJSON('?action=get_sales_chart');
    if (salesResp.success) {
        const labels = salesResp.data.map(r => r.day);
        const totals = salesResp.data.map(r => parseFloat(r.total));
        const ctx = document.getElementById('salesChart');
        new Chart(ctx, { type: 'line', data: { labels, datasets: [{ label: 'Sales (PHP)', data: totals, fill: true }]}, options: { responsive: true } });
    }

    // Today sales quick stat (simple calculation)
    const today = new Date().toISOString().slice(0,10);
    const todaySales = salesResp.success ? (salesResp.data.find(r=>r.day===today)?.total || 0) : 0;
    $('#today-sales').text('₱' + parseFloat(todaySales).toFixed(2));
});

async function updateStock(id, stock) {
    const res = await fetch('?action=update_stock', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, stock, csrf: CSRF_TOKEN })
    });
    const j = await res.json();
    if (j.success) { alert('Stock updated'); location.reload(); }
    else alert('Error: ' + (j.message || 'unknown'));
}
</script>

</body>
</
