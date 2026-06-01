<?php
require_once "../config/db.php";
include "../session.php";
include "role.php";

$courier_id = $_SESSION['temp_user_id'];

$sql = "SELECT * FROM users1 where id = '$courier_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

$sql = "SELECT o.id, u.name as name, u.surname as surname, o.shipping_address, o.delivery_date, o.delivery_status
FROM orders o
left join users1 u on o.customer_id = u.id
where o.courier_id = '$courier_id' AND delivery_status in ('assigned', 'in_delivery', 'pending')
ORDER BY o.id DESC";

$result = mysqli_query($conn, $sql);
$active_delivery = [];
while($row = mysqli_fetch_assoc($result)) {
    $active_delivery[] = $row;
}

if(isset($_POST['EditDeliveryBtn'])) {
    $id = $_POST['delivery_id'];
    $status = $_POST['delivery_status'];

    $sql = "UPDATE orders SET delivery_status = '$status'";
    if($status == 'delivered') {
        $sql .= ", status = '$status'";
    }
    $sql .= " where id = '$id'";
    mysqli_query($conn, $sql);
    header("Location: index.php");
    exit();

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courier Dashboard - Digital Mall</title>
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
        <aside class="w-64 bg-purple-800 text-white flex flex-col rounded-2xl shadow-xl sticky self-start top-6">
            <div class="p-6 border-b border-purple-700">
                <img src="../images/logo.png" class="w-16 mx-auto mb-2" alt="Logo.png">
                <h1 class="text-2xl font-extrabold tracking-wide">Digital Mall</h1>
                <p class="text-sm text-purple-200 mt-1">Courier Panel</p>
            </div>

            <nav class="flex-1 p-4 space-y-2">
                <a href="index.php" class="block px-4 py-3 rounded-lg bg-purple-700 border-l-4 border-yellow-300 font-semibold">Dashboard</a>
                <a href="orders.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Orders</a>
                <a href="delivery_history.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Delivery History</a>
            </nav>

            <div class="p-4 border-purple-700">
                <a href="../index.php" class="block px-4 py-3 rounded-lg bg-red-500 hover:bg-red-600 transition text-center font-semibold">Logout</a>
            </div>
        </aside>

        <main class="flex-1">
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-purple-800">Courier Dashboard</h2>
                <p class="text-gray-600 mt-1">
                    Welcome back, Courier. Here is your delivery overview for today.
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-blue-500">
                    <p class="text-gray-500 text-sm">Assigned Deliveries</p>
                    <h3 class="text-3xl font-bold text-purple-800 mt-2">
                        <?php 
                        $sql = "SELECT count(*) as assigned from orders where delivery_status = 'assigned' AND courier_id = '$courier_id'";
                        $result = mysqli_query($conn, $sql);
                        $assigned = mysqli_fetch_assoc($result);
                        ?>
                        <?php echo $assigned['assigned'];?>
                    </h3>
                </div>

                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-orange-500">
                    <p class="text-gray-500 text-sm">In Delivery</p>
                    <h3 class="text-3xl font-bold text-purple-800 mt-2">
                        <?php 
                        $sql = "SELECT count(*) as in_delivery from orders where delivery_status = 'in_delivery' AND courier_id = '$courier_id'";
                        $result = mysqli_query($conn, $sql);
                        $in_delivery = mysqli_fetch_assoc($result);
                        ?>
                        <?php echo $in_delivery['in_delivery'];?>
                    </h3>
                </div>

                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-green-500">
                    <p class="text-gray-500 text-sm">This Month Delivered</p>
                    <h3 class="text-3xl font-bold text-purple-800 mt-2">
                        <?php 
                        $sql = "SELECT count(*) as delivered_today from orders where delivery_status = 'delivered' AND courier_id = '$courier_id' AND MONTH(delivery_date) = MONTH(CURDATE()) AND YEAR(delivery_date) = YEAR(CURDATE())";
                        $result = mysqli_query($conn, $sql);
                        $delivered_today = mysqli_fetch_assoc($result);
                        ?>
                        <?php echo $delivered_today['delivered_today'];?>
                    </h3>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <!-- Active Deliveries -->
                <div class="xl:col-span-2 bg-white rounded-2xl shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-purple-800">My Active Deliveries</h3>
                            <p class="text-sm text-gray-500">Deliveries currently assigned to you</p>
                        </div>

                        <a href="orders.php" class="bg-purple-700 text-white px-4 py-2 rounded-lg hover:bg-purple-800 transition text-sm font-semibold">
                            View All
                        </a>
                    </div>

                    <div class="overflow-x-auto rounded-xl border self-start">
                        <table class="w-full text-sm text-left">
                            <thead class="text-gray-500 border-b bg-gray-50 tracking-wider uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-4">ID</th>
                                    <th class="px-6 py-4">Customer</th>
                                    <th class="px-6 py-4">Address</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4">Delivery Date</th>
                                    <th class="px-6 py-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($active_delivery as $delivery): ?>
                                    <tr class="border-b hover:bg-yellow-50 transition">
                                        <td class="px-6 py-4 font-semibold">#<?php echo $delivery['id']; ?></td>
                                        <td class="px-6 py-4"><?php echo $delivery['name']." ". $delivery['surname']; ?></td>
                                        <td class="px-6 py-4"><?php echo $delivery['shipping_address']; ?></td>
                                        <td class="px-6 py-4">
                                            <?php if($delivery['delivery_status'] == 'assigned'): ?>
                                                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium">
                                                    Assigned
                                                </span>
                                            <?php elseif($delivery['delivery_status'] == 'in_delivery'): ?>
                                                <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-medium">
                                                    Delivery
                                                </span>
                                            <?php elseif($delivery['delivery_status'] == 'pending'): ?>
                                                <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-medium">
                                                    Pending
                                                </span>
                                                
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4"><?php echo $delivery['delivery_date']; ?></td>
                                        <td class="px-6 py-4">
                                            <button class="bg-yellow-300 text-white px-3 py-2 rounded-lg text-xs hover:bg-yellow-500 transition font-semibold"
                                            onclick="openEditDeliveryPopup(<?php echo $delivery['id']?>, '<?php echo $delivery['name']?>', '<?php echo $delivery['surname']?>', '<?php echo $delivery['shipping_address']?>', '<?php echo $delivery['delivery_status']?>', '<?php echo $delivery['delivery_date']?>')">
                                                ✏️
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                    <div class="bg-white rounded-2xl shadow p-6 self-start">
                        <h3 class="text-xl font-bold text-purple-800 mb-4">Quick Actions</h3>

                        <div class="space-y-3">
                            <a href="orders.php" class="block w-full text-center bg-purple-700 text-white px-4 py-3 rounded-xl hover:bg-purple-800 transition font-semibold">
                                Open Orders
                            </a>

                            <a href="delivery_history.php" class="block w-full text-center bg-yellow-400 text-white px-4 py-3 rounded-xl hover:bg-yellow-500 transition font-semibold">
                                View Delivery History
                            </a>

                            <a href="profile.php" class="block w-full text-center bg-blue-500 text-white px-4 py-3 rounded-xl hover:bg-blue-600 transition font-semibold">
                                Edit Profile
                            </a>
                        </div>
            </div>
        </main>

        <div id="editDeliveryPopup" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl p-6 relative">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-purple-800">Update Delivery Status</h3>
                    <button type="button" class="text-gray-500 hover:text-red-500 text-3xl leading-none"
                    onclick="closeEditDeliveryPopup()">&times;</button>
                </div>

                <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="delivery_id" id="editDeliveryId">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Order ID</label>
                        <input
                            type="text"
                            id="editOrderDisplayId"
                            class="w-full border rounded-lg px-4 py-2 bg-gray-100"
                            readonly
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                        <input
                            type="text"
                            id="editDeliveryCustomer"
                            class="w-full border rounded-lg px-4 py-2 bg-gray-100"
                            readonly
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <input
                            type="text"
                            id="editDeliveryAddress"
                            class="w-full border rounded-lg px-4 py-2 bg-gray-100"
                            readonly
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input
                            type="text"
                            id="editDeliveryDate"
                            class="w-full border rounded-lg px-4 py-2 bg-gray-100"
                            readonly
                        >
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Status</label>
                        <select
                            name="delivery_status"
                            id="editDeliveryStatus"
                            class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
                        >
                            <option value="pending">Pending</option>
                            <option value="assigned">Assigned</option>
                            <option value="in_delivery">In Delivery</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                    <button
                        type="submit"
                        name="EditDeliveryBtn"
                        class="px-5 py-2 rounded-lg bg-green-500 text-white hover:bg-green-700 transition">
                        Save Changes
                    </button>
                    <button
                        type="button"
                        onclick="closeEditDeliveryPopup()"
                        class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 transition">
                        Cancel
                    </button>

                    
                </div>
            </form>
            </div>
            
        </div>
<script src="courier_js/index.js"></script>
</body>


</html>

 