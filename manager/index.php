<?php
include "../session.php";
include "role.php";
require_once "../config/db.php";

$manager_id = $_SESSION['temp_user_id'];
$sql = "SELECT id from shops1 where manager_id = '$manager_id'";
$result = mysqli_query($conn, $sql);
$shop = mysqli_fetch_assoc($result);

$shop_id = $shop['id'];

    $sql = "SELECT o.*, u.name as name, u.surname as surname, c.name as courier_name, c.surname as courier_surname
    from orders o
    left join users1 u on o.customer_id = u.id
    left join users1 c on o.courier_id = c.id
    where o.shop_id = '$shop_id' AND o.status not in ('delivered', 'cancelled')
    order by o.id DESC
    LIMIT 5";
    $result = mysqli_query($conn, $sql);
    $recent_orders = [];

    while($row = mysqli_fetch_assoc($result)) {
        $recent_orders[] = $row;
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard - Digital Mall</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-yellow-100 via-yellow-200 to-purple-200 min-h-screen p-6">
    <header class="h-8 flex mb-6 justify-end px-6">
        <div class="flex items-center gap-4">
            <div class=" border-2 border-purple-500 flex items-center gap-2 cursor-pointer hover:bg-gray-100 px-3 py-2 rounded-lg transition">

            <a href="profile.php" class="w-8 h-8 bg-purple-600 text-white flex items-center justify-center rounded-full text-sm">M</a>
            <?php 
                $sql = "SELECT * from users1 where id = '$manager_id'";
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
                <p class="text-sm text-purple-200 mt-1">Manager Panel</p>
            </div>

            <nav class="flex-1 p-4 space-y-2">
                <a href="index.php" class="block px-4 py-3 rounded-lg bg-purple-700 border-l-4 border-yellow-300 font-semibold">Dashboard</a>
                <a href="my-shop.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">My Shop Info</a>
                <a href="products.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Products</a>
                <a href="orders.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Orders</a>
                <a href="deliveries.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Deliveries</a>
            </nav>

            <div class="p-4 border-purple-700">
                <a href="../logout.php" class="block px-4 py-3 rounded-lg bg-red-500 hover:bg-red-600 transition text-center font-semibold">Logout</a>
            </div>
        </aside>

        <main class="flex-1 min-w-0"> 
            <section class="mb-8">
                    <h2 class="text-3xl font-extrabold text-purple-800">Manager Dashboard</h2>
                    <p class="text-gray-600 mt-1">Welcome back, Manager</p>
            </section>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-purple-700">
                    <p class="text-gray-500 text-sm">Total Products</p>
                    <?php 
                    $sql = "SELECT count(*) as count from products where shop_id = '$shop_id' AND status not in ('out_of_stock', 'blocked')";
                    $result = mysqli_query($conn, $sql);
                    $total_prod = mysqli_fetch_assoc($result);
                    ?>
                    <h3 class="text-3xl font-bold text-purple-800 mt-2"><?php echo $total_prod['count']?></h3>
                </div>

                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-blue-500">
                    <p class="text-gray-500 text-sm">Active Orders</p>
                    <?php 
                    $sql = "SELECT count(*) as count from orders where shop_id = '$shop_id' AND status not in ('delivered', 'cancelled') AND delivery_status not in ('delivered', 'cancelled')";
                    $result = mysqli_query($conn, $sql);
                    $active_orders = mysqli_fetch_assoc($result);
                    ?>
                    <h3 class="text-3xl font-bold text-purple-800 mt-2"><?php echo $active_orders['count']?></h3>
                </div>

                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-green-500">
                    <p class="text-gray-500 text-sm">Revenue</p>
                    <?php 
                    $sql = "SELECT SUM(total_amount) as revenue from orders where shop_id = '$shop_id' and status = 'delivered'";
                    $result = mysqli_query($conn, $sql);
                    $revenue = mysqli_fetch_assoc($result);
                    ?>
                    <?php if($revenue['revenue'] > 0):?>
                    <h3 class="text-3xl font-bold text-purple-800 mt-2">$<?php echo $revenue['revenue']?></h3>
                    <?php else:?>
                    <h3 class="text-3xl font-bold text-purple-800 mt-2">$0</h3>
                    <?php endif;?>
                </div>

                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-yellow-400">
                    <p class="text-gray-500 text-sm">Low Stock Items</p>
                    <?php
                    $sql = "SELECT count(*) as count from products where shop_id = '$shop_id' AND stock < 10";
                    $result = mysqli_query($conn, $sql);
                    $lowStock = mysqli_fetch_assoc($result);
                    ?>
                    <h3 class="text-3xl font-bold text-purple-800 mt-2"><?php echo $lowStock['count']?></h3>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <section class="bg-white rounded-2xl shadow p-6 xl:col-span-2 self-start">
                    <div class="flex items-center justify-between mb-6 ">
                        <h3 class="text-xl font-bold text-purple-800">Recent Orders</h3>
                        <a href="orders.php" class="text-sm text-purple-700 font-semibold hover:underline">View All</a>
                    </div>

                    <div class="overflow-hidden rounded-xl border">
                        <table class="w-full text-sm text-left">
                            <thead class="text-gray-500 border-b bg-gray-50 tracking-wider uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-4">Order ID</th>
                                    <th class="px-6 py-4">Customer</th>
                                    <th class="px-6 py-4">Total</th>
                                    <th class="px-6 py-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach($recent_orders as $order): ?>
                                <tr class="hover:bg-yellow-50">
                                    <td class="px-6 py-4">#<?php echo $order['id'] ?></td>
                                    <td class="px-6 py-4"><?php echo $order['name']. " ". $order['surname']?></td>
                                    <td class="px-6 py-4"><?php echo $order['total_amount'] ?></td>
                                    <td class="px-6 py-4">
                                        <?php if($order['status'] == 'pending'):?>
                                        <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-medium">Pending</span>
                                        <?php elseif($order['status'] == 'confirmed'):?>
                                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium">Confirmed</span>
                                        <?php elseif($order['status'] == 'preparing'):?>
                                        <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-medium">Preparing</span>
                                        <?php else: ?>
                                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">Ready for Delivery</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach;?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <div class="grid grid-cols-1 xl:grid:cols-2 gap-6 mb-8">
                    <section class="bg-white rounded-2xl shadow p-6 self-start">
                        <h3 class="text-xl font-bold text-purple-800 mb-4">Alerts & Quick Actions</h3>
                        <div class="space-y-4">
                                <div class="flex justify-between items-center border rounded-xl p-4">
                                    <p class="font-medium text-gray-700">Pending Orders</p>
                                    <?php 
                                    $sql = "SELECT count(*) as count from orders where shop_id = '$shop_id' AND status = 'pending'";
                                    $result = mysqli_query($conn, $sql);
                                    $pending_orders = mysqli_fetch_assoc($result);
                                    ?>
                                    <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-medium"><?php echo $pending_orders['count']?></span>
                                </div>
                                <div class="flex justify-between items-center border rounded-xl p-4">
                                    <p class="font-medium text-gray-700">Delivered Orders</p>
                                    <?php 
                                    $sql = "SELECT count(*) as count from orders where shop_id = '$shop_id' AND status = 'delivered'";
                                    $result = mysqli_query($conn, $sql);
                                    $delivered_orders = mysqli_fetch_assoc($result);
                                    ?>
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium"><?php echo $delivered_orders['count']?></span>
                                </div>
                                <div class="flex justify-between items-center border rounded-xl p-4">
                                    <p class="font-medium text-gray-700">Cancelled Orders</p>
                                    <?php 
                                    $sql = "SELECT count(*) as count from orders where shop_id = '$shop_id' AND status = 'cancelled'";
                                    $result = mysqli_query($conn, $sql);
                                    $cancelled_orders = mysqli_fetch_assoc($result);
                                    ?>
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-medium"><?php echo $cancelled_orders['count']?></span>
                                </div>
                                    
                            <div class="space-y-4">
                                <a href="products.php" class="block w-full bg-purple-700 text-white py-3 rounded-lg text-center font-semibold hover:bg-purple-800 transition">Add New Product</a>
                                <a href="orders.php" class="block w-full bg-blue-500 text-white py-3 rounded-lg text-center font-semibold hover:bg-blue-600 transition">Check Orders</a>
                                <a href="deliveries.php" class="block w-full bg-green-500 text-white py-3 rounded-lg text-center font-semibold hover:bg-green-600 transition">Check Deliveries</a>
                                <a href="my-shop.php" class="block w-full bg-yellow-300 text-white py-3 rounded-lg text-center font-semibold hover:bg-yellow-500 transition">Edit Shop Info</a>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
    
</body>
</html>