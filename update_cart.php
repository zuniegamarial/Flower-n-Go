<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Check product stock
$product_query = mysqli_query($conn, "SELECT price, stock FROM products WHERE id = $product_id");
if (mysqli_num_rows($product_query) == 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

$product = mysqli_fetch_assoc($product_query);
if ($product['stock'] > 0 && $quantity > $product['stock']) {
    echo json_encode(['success' => false, 'message' => 'Only ' . $product['stock'] . ' items available']);
    exit;
}

// Update the cart
$update_query = "UPDATE shopping_cart SET quantity = $quantity WHERE user_id = $user_id AND product_id = $product_id";
if (mysqli_query($conn, $update_query)) {
    // Calculate new subtotal for this product
    $subtotal = $product['price'] * $quantity;
    
    // Calculate new cart total
    $cart_total_query = mysqli_query($conn, 
        "SELECT SUM(p.price * c.quantity) as total 
         FROM shopping_cart c 
         JOIN products p ON c.product_id = p.id 
         WHERE c.user_id = $user_id");
    $cart_total = mysqli_fetch_assoc($cart_total_query)['total'] ?? 0;
    
    echo json_encode([
        'success' => true,
        'subtotal' => $subtotal,
        'cartTotal' => $cart_total
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
}
?>