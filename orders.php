<?php
session_start();
include "db.php";

if(!isset($_SESSION['user_id'])){
  header("Location: signin.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$status = $_GET['status'] ?? 'all';

$where = ($status !== 'all') ? "AND o.status='$status'" : "";

$sql = "
SELECT 
o.id,
o.status,
o.created_at,
SUM(oi.quantity * p.price) AS total
FROM orders o
JOIN order_items oi ON o.id = oi.order_id
JOIN products p ON oi.product_id = p.id
WHERE o.user_id = $user_id $where
GROUP BY o.id
ORDER BY o.created_at DESC
";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>My Orders</title>
<style>
body{background:#0d0a08;color:white;font-family:Arial}
.box{
  width:90%;
  max-width:900px;
  margin:auto;
}
.order{
  background:#1b120d;
  margin:20px 0;
  padding:20px;
  border-radius:12px;
}
.order h3{color:#D4AF37}
.row{display:flex;justify-content:space-between}
.btn{
  background:#D4AF37;
  padding:6px 12px;
  border-radius:15px;
  color:black;
  text-decoration:none;
}
.order-tabs{
  display:flex;
  justify-content:center;
  gap:15px;
  margin:20px;
}
.order-tabs a{
  padding:8px 18px;
  border-radius:20px;
  background:#222;
  color:#D4AF37;
  text-decoration:none;
}
.order-tabs a:hover{ background:#D4AF37; color:#000;}

</style>
</head>

<body>
<div class="box">

<h2>📦 My Orders</h2>

<div class="order-tabs">
  <a href="orders.php?status=all">ALL</a>
  <a href="orders.php?status=to_ship">To Ship</a>
  <a href="orders.php?status=to_receive">To Receive</a>
  <a href="orders.php?status=completed">Completed</a>
  <a href="orders.php?status=cancelled">Cancelled</a>
</div>

<?php while($o = mysqli_fetch_assoc($result)): ?>
<div class="order">
  <h3>Order #<?= $o['id'] ?></h3>
  <div class="row">
    <span>Status: <?= $o['status'] ?></span>
    <span>Total: ₱<?= number_format($o['total'],2) ?></span>
  </div>
  <br>
  <a href="receipt.php?id=<?= $o['id'] ?>" class="btn">View Receipt</a>
</div>
<?php endwhile; ?>

</div>
</body>
</html>
