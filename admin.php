<?php
// admin.php - Admin Dashboard
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #4CAF50; color: white; }
        .pending { color: orange; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🌺 Flower in Go - Admin Dashboard</h1>
    
    <h2>📋 Recent Orders</h2>
    <table>

<script>
// Check for new orders every 5 seconds
setInterval(function(){
    fetch('check_new_orders.php')
    .then(response => response.text())
    .then(data => {
        if(data == 'new') {
            location.reload(); // Reload if new orders
        }
    });
}, 5000);
</script>
        <tr><th>Order ID</th><th>Customer</th><th>Date</th><th>Amount</th><th>Status</th><th>Action</th></tr>
        <?php
        // READ ORDERS FROM FILE
        if(file_exists('orders_data.txt')){
            $orders = file('orders_data.txt');
            $order_id = count($orders);
            
            foreach(array_reverse($orders) as $order_line){
                list($date, $customer, $amount, $status) = explode('|', trim($order_line));
                echo "<tr>
                        <td>#$order_id</td>
                        <td>$customer</td>
                        <td>$date</td>
                        <td>$amount</td>
                        <td class='pending'>$status</td>
                        <td>
                            <select onchange='updateStatus($order_id, this.value)'>
                                <option value='Pending'>Pending</option>
                                <option value='Processing'>Processing</option>
                                <option value='Completed'>Completed</option>
                            </select>
                        </td>
                      </tr>";
                $order_id--;
            }
        }
        ?>
    </table>
    
    <script>
    // AUTO-REFRESH every 10 seconds
    setInterval(() => location.reload(), 10000);
    
    function updateStatus(orderId, status) {
        alert("Order #" + orderId + " updated to: " + status);
        // Here you can add AJAX to save status
    }
    </script>
</body>
</html>