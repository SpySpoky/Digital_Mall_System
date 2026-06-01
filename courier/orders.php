<?php
require_once "../config/db.php";
include "../session.php";
include "role.php";

$courier_id = $_SESSION['temp_user_id'];

if(isset($_GET['SearchBtn'])) {
    $search = $_GET['Search'];
    $filter = $_GET['Filter'];

    $sql = "SELECT o.id as id, u.name as name, u.surname as surname, u.phone as phone, o.shipping_address, o.payment_type, o.total_amount, o.delivery_status, o.delivery_date
    FROM orders o
    left join users1 u on o.customer_id = u.id
    where courier_id = '$courier_id'";

    if(!empty($search)) {
        $sql .= " AND (o.id LIKE '%$search%' or u.name LIKE '%$search%' or u.surname LIKE '%$search%' or o.shipping_address LIKE '%$search%')";
    }

    if(!empty($filter)) {
        $sql .= " AND o.delivery_status = '$filter'";
    }

    $sql .= " ORDER BY 
                CASE o.delivery_status
                    WHEN 'pending' THEN 1
                    WHEN 'assigned' THEN 2
                    WHEN 'in_delivery' THEN 3
                    WHEN 'delivered' THEN 4
                    WHEN 'cancelled' THEN 5
                    ELSE 6
                end,
                o.id DESC";
    
    $result = mysqli_query($conn, $sql);
    $orders = [];
    while($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
    
}else {
    $sql = "SELECT o.id as id, u.name as name, u.surname as surname, u.phone as phone, o.shipping_address, o.payment_type, o.total_amount, o.delivery_status, o.delivery_date
    FROM orders o
    left join users1 u on o.customer_id = u.id
    where courier_id = '$courier_id'
    ORDER BY 
        CASE o.delivery_status
        WHEN 'pending' THEN 1
        WHEN 'assigned' THEN 2
        WHEN 'in_delivery' THEN 3
        WHEN 'delivered' THEN 4
        WHEN 'cancelled' THEN 5
        else 6
        end,
        o.id DESC";

    $result = mysqli_query($conn, $sql);
    $orders = [];
    while($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }

}

if(isset($_POST['EditOrderBtn'])) {
    $id = $_POST['delivery_id'];
    $status = $_POST['delivery_status'];

    $sql = "UPDATE orders set delivery_status = '$status' where id = '$id'";
    mysqli_query($conn, $sql);
    if($status == 'delivered' || $status == 'cancelled') {
    $sql = "UPDATE orders set status = '$status' where id = '$id'";
    mysqli_query($conn, $sql);
    }
    header("Location: orders.php");
    exit();
}

// profile

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courier Orders - Digital Mall</title>
    <script src="https://cdn.tailwindcss.com"></script>

</head>
<body class="bg-gradient-to-br from-yellow-100 via-yellow-200 to-purple-200 min-h-screen p-6">
    <header class="h-8 flex mb-6 justify-end px-6">
            <div class="flex items-center gap-4">

                <div class="border-2 border-purple-500 flex items-center gap-2 cursor-pointer hover:bg-gray-100 px-3 py-2 rounded-lg transition">
                    <a href="profile.php" class="w-8 h-8 bg-purple-600 text-white flex items-center justify-center rounded-full text-sm">C</a>
                    <?php 
                        $sql = "SELECT * from users1 where id = '$courier_id'";
                        $result = mysqli_query($conn,$sql);
                        $admin = mysqli_fetch_assoc($result);
                    ?>
                    <a href="profile.php" class="text-sm font-medium"><?php echo $admin['name']?></a>
                </div>
            </div>
        </header>
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
        <div class="absolute w-96 h-96 bg-purple-300 rounded-full blur-3xl opacity-40 top-10 left-10"></div>
        <div class="absolute w-96 h-96 bg-yellow-300 rounded-full blur-3xl opacity-40 bottom-10 right-10"></div>
    </div>

    <div class="flex min-h-screen w-full max-w-7xl gap-6">
        <aside class="w-64 bg-purple-800 text-white flex flex-col rounded-2xl shadow-xl sticky self-start top-6 shrink-0">
            <div class="p-6 border-b border-purple-700">
                <img src="../images/logo.png" class="w-16 mx-auto mb-2" alt="Logo.png">
                <h1 class="text-2xl font-extrabold tracking-wide">Digital Mall</h1>
                <p class="text-sm text-purple-200 mt-1">Courier Panel</p>
            </div>

            <nav class="flex-1 p-4 space-y-2">
                <a href="index.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Dashboard</a>
                <a href="orders.php" class="block px-4 py-3 rounded-lg bg-purple-700 border-l-4 border-yellow-300 font-semibold">Orders</a>
                <a href="delivery_history.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Delivery History</a>
            </nav>

            <div class="p-4 border-purple-700">
                <a href="../logout.php" class="block px-4 py-3 rounded-lg bg-red-500 hover:bg-red-600 transition text-center font-semibold">Logout</a>
            </div>
        </aside>

        
        <section class="bg-white rounded-2xl shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xl font-bold text-purple-800">My Orders</h3>
                    <p class="text-sm text-gray-500">Orders assigned to you for delivery</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-4 mb-4">
                <form action="" method="GET">
                    <input 
                        type="text"
                        name="Search"
                        placeholder="Search order..."
                        class="border rounded-lg px-4 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-purple-500"
                    >

                    <select class="border rounded-lg px-4 py-2" name="Filter">
                        <option value="">All statuses</option>
                        <option value="pending">Pending</option>
                        <option value="assigned">Assigned</option>
                        <option value="in_delivery">In Delivery</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <button type="submit" name="SearchBtn" class="bg-purple-500 text-white ml-3 px-6 py-2 rounded-lg hover:bg-purple-600 transition">Search</button>
                </form>
                

            </div>

            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm text-left">
                    <thead class="text-gray-500 border-b bg-gray-50 tracking-wider uppercase text-xs text-center">
                        <tr>
                            <th class="px-6 py-4 truncate">Order ID</th>
                            <th class="px-6 py-4">Customer</th>
                            <th class="px-6 py-4">Phone</th>
                            <th class="px-6 py-4">Address</th>
                            <th class="px-6 py-4">Payment</th>
                            <th class="px-6 py-4">Total</th>
                            <th class="px-6 py-4 truncate">Delivery Date</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach($orders as $order): ?>
                        <tr class="border-b hover:bg-yellow-50 transition">
                            <td class="px-6 py-4 font-semibold text-purple-700">
                                #<?php echo $order['id']?>
                            </td>

                            <td class="px-6 py-4 truncate">
                                <?php echo $order['name']. " ". $order['surname']?>
                            </td>

                            <td class="px-6 py-4 truncate">
                                <?php echo $order['phone']?>
                            </td>

                            <td class="px-6 py-4 max-w-[220px] truncate">
                                <?php echo $order['shipping_address']?>                                
                            </td>

                            <td class="px-6 py-4">
                                <?php echo $order['payment_type'] ?>
                            </td>

                            <td class="px-6 py-4 font-semibold">
                                $<?php echo $order['total_amount'] ?>
                            </td>

                            <td class="px-6 py-4">
                                <?php echo $order['delivery_date']?>
                            </td>

                            <td class="px-6 py-4">
                                <?php if($order['delivery_status'] == 'assigned'): ?>
                                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium">Assigned</span>
                                <?php elseif($order['delivery_status'] == 'pending'): ?>
                                    <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-medium">Pending</span>
                                <?php elseif($order['delivery_status'] == 'in_delivery'): ?>
                                    <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-medium">Delivery</span>
                                <?php elseif($order['delivery_status'] == 'delivered'): ?>
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">Delivered</span>
                                <?php else: ?>
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-medium">Cancelled</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4">
                                <button
                                    type="button"
                                    onclick='openUpdateDeliveryPopup(
                                    <?php echo $order["id"]?>,
                                    <?php echo json_encode($order["name"])?>,
                                    <?php echo json_encode($order["surname"])?>,
                                    <?php echo json_encode($order["shipping_address"])?>,
                                    <?php echo json_encode($order["delivery_date"])?>,
                                    <?php echo json_encode($order["delivery_status"])?>,
                                    )'
                                    class="bg-yellow-400 text-white px-3 py-2 rounded-lg text-xs hover:bg-yellow-500 transition font-semibold"
                                >
                                    ✏️
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

        <div id="updateDeliveryPopup" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl p-6">

                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-purple-800">Update Delivery</h3>
                    <button type="button" onclick="closeUpdateDeliveryPopup()" class="text-gray-500 hover:text-red-500 text-3xl leading-none">&times;</button>
                </div>

                <form method="POST" class="space-y-4">
                    <input type="hidden" name="delivery_id" id="updateDeliveryId">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Order ID</label>
                            <input type="text" id="updateOrderId" class="w-full border rounded-lg px-4 py-2 bg-gray-100" readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                            <input type="text" id="updateCustomer" class="w-full border rounded-lg px-4 py-2 bg-gray-100" readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <input type="text" id="updateAddress" class="w-full border rounded-lg px-4 py-2 bg-gray-100" readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Date</label>
                            <input type="text" id="updateDate" class="w-full border rounded-lg px-4 py-2 bg-gray-100" readonly>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Status</label>
                            <select name="delivery_status" id="updateDeliveryStatus" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="pending">Pending</option>
                                <option value="assigned">Assigned</option>
                                <option value="in_delivery">In Delivery</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="submit" name="EditOrderBtn" class="px-5 py-2 rounded-lg bg-green-500 text-white hover:bg-green-600 transition">
                            Save Changes
                        </button>
                        <button type="button" onclick="closeUpdateDeliveryPopup()" class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 transition">
                            Cancel
                        </button>

                        
                    </div>
                </form>

            </div>
        </div>
<script src="./courier_js/order.js"></script>
</body>
</html>