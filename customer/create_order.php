<?php
require_once "../config/db.php";
include "../session.php";
include "role.php";

    $user_id = $_SESSION['temp_user_id'];

    if(!isset($_POST['CreateOrderBtn'])) {
        header("Location: cart.php");
        exit();
    }

    $total = $_POST['total'];
    $subtotal = $_POST['subtotal'];
    $shipping_cost = $_POST['shipping_cost'];
    $tax = $_POST['tax'];
    $shipping_address = $_POST['shipping_address'];
    $payment_type = $_POST['payment_type'];
    $delivery_date = date('Y-m-d', strtotime('+4 days'));

    $cart_sql = "SELECT c.*, p.name, p.price, p.shop_id, p.stock as product_stock
                FROM cart c
                JOIN products p ON c.product_id = p.id
                WHERE c.user_id = '$user_id'";
    $result = mysqli_query($conn, $cart_sql);
    if(mysqli_num_rows($result) == 0) {
        header("Location: cart.php");
        exit();
    }
    $items = [];
    $shop_id = null;

    while($row = mysqli_fetch_assoc($result)) {
        $shop_id = $row['shop_id'];
        $items[] = $row;
    }

    $sql = "INSERT INTO orders (shop_id, customer_id, shipping_address, subtotal, shipping_cost, tax, total_amount, payment_type, status, delivery_status, order_date, delivery_date) 
              VALUES ('$shop_id', '$user_id', '$shipping_address', '$subtotal', '$shipping_cost', '$tax', '$total', '$payment_type', 'pending', 'pending', NOW(), '$delivery_date')";

    if(mysqli_query($conn, $sql)) {
        $order_id = mysqli_insert_id($conn);

        foreach($items as $item) {
            $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES ('$order_id', '{$item['product_id']}', '{$item['quantity']}', '{$item['price']}', '" . ($item['price'] * $item['quantity']) . "')";
            mysqli_query($conn, $item_sql);

            $update_stock_sql = "UPDATE products SET stock = stock - {$item['quantity']} WHERE id = '{$item['product_id']}'";
            mysqli_query($conn, $update_stock_sql);
        }
        mysqli_query($conn, "DELETE FROM cart WHERE user_id = '$user_id'");
        header("Location: orders.php");
        exit();

    }


?>