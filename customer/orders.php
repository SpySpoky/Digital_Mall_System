<?php
require_once "../config/db.php";
include "../session.php";
include "role.php";

$user_id = $_SESSION['temp_user_id'];

if(isset($_POST['CancelOrderBtn'])) {
    $order_id = $_POST['order_id'];
    
    $check_sql = "SELECT status, delivery_status FROM orders WHERE id = '$order_id' AND customer_id = '$user_id'";
    $check_result = mysqli_query($conn, $check_sql);
    $order = mysqli_fetch_assoc($check_result);
    
    if(!$order) {
        $error = "Order not found";
    } elseif($order['status'] == 'pending' || $order['status'] == 'confirmed' || $order['status'] == 'preparing') {
        $sql = "UPDATE orders SET status = 'cancelled', delivery_status = 'cancelled' WHERE id = '$order_id'";
        mysqli_query($conn, $sql);
        
        $items_sql = "SELECT product_id, quantity FROM order_items WHERE order_id = '$order_id'";
        $items_result = mysqli_query($conn, $items_sql);
        while($item = mysqli_fetch_assoc($items_result)) {
            $update_stock = "UPDATE products SET stock = stock + {$item['quantity']} WHERE id = '{$item['product_id']}'";
            mysqli_query($conn, $update_stock);
        }
        
    } else {
        $error = "Cannot cancel order. Order is already in progress.";
    }
    header("Location: orders.php");
    exit();
}

$sql = "SELECT o.*, s.shop_name 
        FROM orders o
        JOIN shops1 s ON o.shop_id = s.id
        WHERE o.customer_id = '$user_id'
        ORDER BY o.id DESC";
$result = mysqli_query($conn, $sql);
$orders = [];
while($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Digital Mall</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-yellow-100 via-yellow-200 to-purple-200 min-h-screen p-6">

    <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
        <div class="absolute w-96 h-96 bg-purple-300 rounded-full blur-3xl opacity-40 top-10 left-10"></div>
        <div class="absolute w-96 h-96 bg-yellow-300 rounded-full blur-3xl opacity-40 bottom-10 right-10"></div>
    </div>

    <nav class="bg-white rounded-2xl shadow p-4 mb-8 flex justify-between items-center max-w-7xl mx-auto">
        <div class="flex items-center gap-3">
            <img src="../images/logo.png" class="w-10 h-10" alt="Logo">
            <h1 class="text-xl font-bold text-purple-800">Digital Mall</h1>
        </div>
        <div class="flex gap-5 text-sm font-semibold">
            <a href="index.php" class="text-gray-600 hover:text-purple-700">Home</a>
            <a href="cart.php" class="text-gray-600 hover:text-purple-700">Cart</a>
            <a href="orders.php" class="text-purple-700 underline">Orders</a>
            <a href="profile.php" class="text-gray-600 hover:text-purple-700">Profile</a>
            <a href="../logout.php" class="text-red-500 hover:text-red-600">Log Out</a>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto">
        <div class="mb-6">
            <h2 class="text-3xl font-extrabold text-purple-800">My Orders</h2>
            <p class="text-gray-600 mt-1">Track and manage your orders</p>
        </div>

        <?php if(count($orders) == 0): ?>
            <div class="bg-white rounded-2xl shadow p-12 text-center">
                <p class="text-gray-500 text-lg">You haven't placed any orders yet</p>
                <a href="index.php" class="inline-block mt-4 bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition">
                    Start Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach($orders as $order): ?>
                    <div class="bg-white rounded-2xl shadow overflow-hidden">
                        <div class="bg-gray-50 px-6 py-4 border-b flex justify-between items-center flex-wrap gap-3">
                            <div>
                                <span class="text-sm text-gray-500">Order #<?php echo $order['id']; ?></span>
                                <span class="text-sm text-gray-500 ml-4"><?php echo date('d M Y', strtotime($order['order_date'])); ?></span>
                                <span class="font-medium text-gray-700 ml-6"><?php echo htmlspecialchars($order['shop_name']); ?></span>
                                <?php if($order['status'] == 'pending'): ?>
                                    <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-medium">Pending</span>
                                <?php elseif($order['status'] == 'confirmed'): ?>
                                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium">Confirmed</span>
                                <?php elseif($order['status'] == 'preparing'): ?>
                                    <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-medium">Preparing</span>
                                <?php elseif($order['status'] == 'ready_for_delivery'): ?>
                                    <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs font-medium">Ready</span>
                                <?php elseif($order['status'] == 'delivered'): ?>
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">Delivered</span>
                                <?php elseif($order['status'] == 'cancelled'): ?>
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-medium">Cancelled</span>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-medium text-gray-700">Delivery Status:</span>
                                <?php if($order['delivery_status'] == 'pending'): ?>
                                    <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-medium">Pending</span>
                                <?php elseif($order['delivery_status'] == 'assigned'): ?>
                                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium">Assigned</span>
                                <?php elseif($order['delivery_status'] == 'in_delivery'): ?>
                                    <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-medium">In Delivery</span>
                                <?php elseif($order['delivery_status'] == 'delivered'): ?>
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">Delivered</span>
                                <?php elseif($order['delivery_status'] == 'cancelled'): ?>
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-medium">Cancelled</span>
                                <?php endif; ?>
                                
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <div class="flex justify-between items-start flex-wrap gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Delivery Address</p>
                                    <p class="text-gray-800"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">Total Amount</p>
                                    <p class="text-2xl font-bold text-purple-700">$<?php echo number_format($order['total_amount'], 2); ?></p>
                                </div>
                            </div>
                            
                            <?php if($order['delivery_date']): ?>
                                <div class="mt-4 pt-4 border-t">
                                    <p class="text-sm text-gray-500">Expected Delivery Date</p>
                                    <p class="text-gray-800 font-medium"><?php echo date('d M Y', strtotime($order['delivery_date'])); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if($order['status'] == 'pending' || $order['status'] == 'confirmed' || $order['status'] == 'preparing'): ?>
                                <div class="mt-4 pt-4 border-t">
                                    <form action="" method="POST">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <button type="submit" name="CancelOrderBtn" 
                                                class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-600 transition">
                                            Cancel Order
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>