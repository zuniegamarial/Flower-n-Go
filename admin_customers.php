<?php
// admin_customers.php - FIXED VERSION
session_start();
include "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: sign_in.php");
    exit();
}

// Search functionality - FIXED
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where_clause = "";

if ($search) {
    $where_clause = "WHERE (name LIKE '%$search%' OR email LIKE '%$search%') AND user_type = 'customer'";
} else {
    $where_clause = "WHERE user_type = 'customer'";
}

// Get all customers - FIXED SQL
$sql = "SELECT id, name, email, phone, address, created_at 
        FROM users 
        $where_clause 
        ORDER BY created_at DESC";

$customers_query = mysqli_query($conn, $sql);

// Get customer count - FIXED
$count_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE user_type = 'customer'");
$count_row = mysqli_fetch_assoc($count_result);
$customer_count = $count_row['count'] ?? 0;

// Check for query error
if (!$customers_query) {
    die("Query failed: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Customers - Flower 'n Go</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg: #0A0A0A;
            --card: #1A1A1A;
            --accent: #D4AF37;
            --text: #FFF;
            --text2: #AAA;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background: var(--bg); color: var(--text); font-family: Arial; padding:20px; }
        .container { max-width:1200px; margin:0 auto; }
        .header { display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; }
        h1 { color:var(--accent); }
        .search-box { background:#111; padding:12px; border-radius:8px; border:1px solid #333; width:300px; color:#FFF; }
        .stats { display:flex; gap:20px; margin-bottom:20px; }
        .stat-card { background:#1A1A1A; padding:15px; border-radius:10px; flex:1; border-top:4px solid var(--accent); }
        .stat-number { font-size:28px; font-weight:bold; color:var(--accent); }
        table { width:100%; background:#1A1A1A; border-radius:10px; overflow:hidden; }
        th { background:#111; padding:15px; text-align:left; }
        td { padding:15px; border-bottom:1px solid #222; }
        .action-btn { background:var(--accent); color:#000; border:none; padding:5px 10px; border-radius:5px; cursor:pointer; margin:2px; }
        .action-btn.delete { background:#EF4444; color:#FFF; }
        .back-btn { display:inline-block; background:#333; color:#FFF; padding:10px 20px; text-decoration:none; border-radius:8px; margin-bottom:20px; }
        .back-btn:hover { background:#444; }
        .no-data { text-align:center; padding:40px; color:#666; }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        
        <div class="header">
            <h1><i class="fas fa-users"></i> Customer Management</h1>
            <div>
                <input type="text" id="searchInput" class="search-box" placeholder="Search customers..." value="<?php echo htmlspecialchars($search); ?>">
                <button onclick="searchCustomers()" style="background:var(--accent); color:#000; padding:12px 20px; border:none; border-radius:8px; cursor:pointer; margin-left:10px;">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $customer_count; ?></div>
                <div>Total Customers</div>
            </div>
        </div>
        
        <div style="background:#1A1A1A; border-radius:10px; padding:20px; margin-top:20px;">
            <?php if(mysqli_num_rows($customers_query) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($customer = mysqli_fetch_assoc($customers_query)): ?>
                        <tr>
                            <td>#<?php echo $customer['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($customer['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo $customer['phone'] ? htmlspecialchars($customer['phone']) : 'N/A'; ?></td>
                            <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                            <td>
                                <button class="action-btn" onclick="viewCustomer(<?php echo $customer['id']; ?>)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="action-btn delete" onclick="deleteCustomer(<?php echo $customer['id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-users" style="font-size:48px; margin-bottom:10px; color:#444;"></i>
                    <h3>No customers found</h3>
                    <p>No customer accounts in the database yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    function searchCustomers() {
        const query = document.getElementById('searchInput').value;
        window.location.href = 'admin_customers.php?search=' + encodeURIComponent(query);
    }
    
    // Enter key to search
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if(e.key === 'Enter') {
            searchCustomers();
        }
    });
    
    function viewCustomer(id) {
        // Create a simple view modal
        alert('View Customer #' + id + '\n\nFeature coming soon!');
    }
    
    function deleteCustomer(id) {
        if(confirm('Are you sure you want to delete this customer?\n\nThis action cannot be undone.')) {
            fetch('delete_customer.php?id=' + id)
            .then(response => response.text())
            .then(data => {
                alert(data);
                location.reload();
            });
        }
    }
    </script>
</body>
</html>