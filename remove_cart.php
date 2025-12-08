<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

// Remove item from cart
$delete_query = "DELETE FROM shopping_cart WHERE user_id = $user_id AND product_id = $product_id";
if (mysqli_query($conn, $delete_query)) {
    // Calculate new cart total
    $cart_total_query = mysqli_query($conn, 
        "SELECT SUM(p.price * c.quantity) as total 
         FROM shopping_cart c 
         JOIN products p ON c.product_id = p.id 
         WHERE c.user_id = $user_id");
    $cart_total = mysqli_fetch_assoc($cart_total_query)['total'] ?? 0;
    
    echo json_encode([
        'success' => true,
        'cartTotal' => $cart_total
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
}
?>