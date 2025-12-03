<?php
session_start();
include "db.php";

$user = $_SESSION['user_id'];
mysqli_query($conn,"INSERT INTO orders(user_id,status) VALUES('$user','to_ship')");
$order_id = mysqli_insert_id($conn);

foreach($_SESSION['cart'] as $pid=>$qty){
  mysqli_query($conn,"INSERT INTO order_items(order_id,product_id,quantity)
  VALUES('$order_id','$pid','$qty')");
}

unset($_SESSION['cart']);
header("Location: orders.php");
