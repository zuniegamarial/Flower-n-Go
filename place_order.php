<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $postal_code = mysqli_real_escape_string($conn, $_POST['postal_code']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $delivery_date = mysqli_real_escape_string($conn, $_POST['delivery_date']);
    $delivery_time = mysqli_real_escape_string($conn, $_POST['delivery_time']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $instructions = mysqli_real_escape_string($conn, $_POST['instructions']);
    
    // Calculate totals
    $delivery_fee = 150.00;
    $service_fee = 50.00;
    $subtotal = 0;
    
    // Check if this is a direct purchase
    if(isset($_SESSION['direct_order'])) {
        // DIRECT PURCHASE (from Buy Now button)
        $direct_order = $_SESSION['direct_order'];
        $subtotal = $direct_order['price'] * $direct_order['quantity'];
        $total = $subtotal + $delivery_fee + $service_fee;
        
        // Insert order
        $order_sql = "INSERT INTO orders (user_id, address, phone, payment, city, postal_code, country, 
                                          delivery_date, delivery_time, instructions, total, status) 
                      VALUES ('$user_id', '$address', '$phone', '$payment_method', '$city', '$postal_code', '$country',
                              '$delivery_date', '$delivery_time', '$instructions', '$total', 'to_ship')";
        
        if (mysqli_query($conn, $order_sql)) {
            $order_id = mysqli_insert_id($conn);
            
            // Insert order item
            $product_id = $direct_order['product_id'];
            $quantity = $direct_order['quantity'];
            $price = $direct_order['price'];
            $item_total = $price * $quantity;
            
            $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price, total_price) 
                         VALUES ('$order_id', '$product_id', '$quantity', '$price', '$item_total')";
            mysqli_query($conn, $item_sql);
            
            // Update product stock
            $update_sql = "UPDATE products SET stock = stock - $quantity WHERE id = $product_id";
            mysqli_query($conn, $update_sql);
            
            // Clear direct order from session
            unset($_SESSION['direct_order']);
            
            // Redirect to success page
            header("Location: order_success.php?id=$order_id");
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
        
    } else {
        // CART PURCHASE (from shopping cart)
        // Fetch cart items to calculate subtotal
        $cart_sql = "SELECT p.id, p.price, c.quantity, (p.price * c.quantity) as subtotal
                    FROM shopping_cart c
                    JOIN products p ON c.product_id = p.id
                    WHERE c.user_id = $user_id";
        $cart_result = mysqli_query($conn, $cart_sql);
        
        $cart_items = [];
        while ($item = mysqli_fetch_assoc($cart_result)) {
            $cart_items[] = $item;
            $subtotal += $item['subtotal'];
        }
        
        if(empty($cart_items)) {
            header("Location: view_cart.php?error=Cart is empty");
            exit();
        }
        
        $total = $subtotal + $delivery_fee + $service_fee;
        
        // Insert order
        $order_sql = "INSERT INTO orders (user_id, address, phone, payment, city, postal_code, country, 
                                          delivery_date, delivery_time, instructions, total, status) 
                      VALUES ('$user_id', '$address', '$phone', '$payment_method', '$city', '$postal_code', '$country',
                              '$delivery_date', '$delivery_time', '$instructions', '$total', 'to_ship')";
        
        if (mysqli_query($conn, $order_sql)) {
            $order_id = mysqli_insert_id($conn);
            
            // Insert order items and clear cart
            foreach($cart_items as $item) {
                $product_id = $item['id'];
                $quantity = $item['quantity'];
                $price = $item['price'];    
                $item_total = $item['subtotal'];
                
                // Insert order item
                $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price, total_price) 
                             VALUES ('$order_id', '$product_id', '$quantity', '$price', '$item_total')";
                mysqli_query($conn, $item_sql);
                
                // Update product stock
                $update_sql = "UPDATE products SET stock = stock - $quantity WHERE id = $product_id";
                mysqli_query($conn, $update_sql);
            }
            
            // Clear shopping cart
            $clear_cart_sql = "DELETE FROM shopping_cart WHERE user_id = $user_id";
            mysqli_query($conn, $clear_cart_sql);
            
            // Redirect to success page
            header("Location: order_success.php?id=$order_id");
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
} else {
    header("Location: checkout_final.php");
    exit();
}
?>