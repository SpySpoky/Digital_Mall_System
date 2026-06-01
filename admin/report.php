<?php
require_once "../config/db.php";
include "../session.php";
include "role.php";

$user_id = $_SESSION['temp_user_id'];

    $sql = "SELECT sum(total_amount) as total_revenue from orders where status = 'delivered' and delivery_status = 'delivered'";
    $result = mysqli_query($conn, $sql);
    $total_revenue = mysqli_fetch_assoc($result);

    $sql = "SELECT count(*) as total_orders from orders";
    $result = mysqli_query($conn, $sql);
    $total_orders = mysqli_fetch_assoc($result);

    $sql = "SELECT count(*) as total_users from users1";
    $result = mysqli_query($conn, $sql);
    $total_users = mysqli_fetch_assoc($result);

    $sql = "SELECT count(*) as total_shops from shops1";
    $result = mysqli_query($conn, $sql);
    $total_shops = mysqli_fetch_assoc($result);

    $sql = "SELECT s.shop_name as name, count(o.id) as orders, COALESCE(sum(o.total_amount), 0) as revenue
    from shops1 s
    left join orders o on s.id = o.shop_id AND o.status = 'delivered' AND o.delivery_status = 'delivered'
    group by s.id
    ORDER BY revenue DESC
    LIMIT 5";
    $result = mysqli_query($conn, $sql);
    $top_shops = [];
    while($row = mysqli_fetch_assoc($result)) {
        $top_shops[] = $row;
    }

    $sql = "SELECT c.name, count(distinct s.id) as shops, coalesce(sum(o.total_amount), 0) as revenue
    from shop_category c
    left join shops1 s on c.id = s.category_id
    left join orders o on s.id = o.shop_id and o.status = 'delivered' AND o.delivery_status = 'delivered'
    GROUP BY c.id
    ORDER BY revenue DESC
    LIMIT 5";
    $result = mysqli_query($conn, $sql);
    $topCategories = [];
    while($row = mysqli_fetch_assoc($result)) {
        $topCategories[] = $row;
    }

    $sql = "SELECT count(*) as pending_shops from shops1 where status = 'pending'";
    $result = mysqli_query($conn, $sql);
    $pending_shops = mysqli_fetch_assoc($result);

    $sql = "SELECT count(*) as blocked_users from users1 where status = 'blocked'";
    $result = mysqli_query($conn, $sql);
    $blocked_users = mysqli_fetch_assoc($result);

    $sql = "SELECT count(*) as temp_shops from shops1 where status = 'temporary_closed'";
    $result = mysqli_query($conn, $sql);
    $temp_shops = mysqli_fetch_assoc($result);

$alerts = [
    ["label" => "Pending Shops", "value" => $pending_shops['pending_shops']],
    ["label" => "Blocked Users", "value" => $blocked_users['blocked_users']],
    ["label" => "Temporary Closed Shops", "value" => $temp_shops['temp_shops']]
];
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Report - Digital Mall</title>
    <script src="https://cdn.tailwindcss.com"></script>

</head>
<body class="bg-gradient-to-br from-yellow-100 via-yellow-200 to-purple-200 min-h-screen p-6">
    <header class="h-8 flex mb-6 justify-end px-6">
        <div class="flex items-center gap-4">
            <div class=" border-2 border-purple-500 flex items-center gap-2 cursor-pointer hover:bg-gray-100 px-3 py-2 rounded-lg transition">

            <a href="profile.php" class="w-8 h-8 bg-purple-600 text-white flex items-center justify-center rounded-full text-sm">A</a>
            <?php 
                $sql = "SELECT * from users1 where id = '$user_id'";
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
                <p class="text-sm text-purple-200 mt-1">Admin Panel</p>
            </div>

            <nav class="flex-1 p-4 space-y-2">
                <a href="index.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Dashboard</a>
                <a href="users.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Users</a>
                <a href="shops.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Shops</a>
                <a href="category.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Categories</a>
                <a href="report.php" class="block px-4 py-3 rounded-lg bg-purple-700 border-l-4 border-yellow-300 font-semibold">Reports</a>
            </nav>

            <div class="p-4 border-purple-700">
                <a href="../logout.php" class="block px-4 py-3 rounded-lg bg-red-500 hover:bg-red-600 transition text-center font-semibold">Logout</a>
            </div>
        </aside>
        <main class="flex-1">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">

            <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-green-500">
                <p class="text-gray-500 text-sm">Total Revenue</p>
                <h3 class="text-3xl font-bold text-purple-800 mt-2">$<?php echo $total_revenue['total_revenue']?></h3>
            </div>

            <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-blue-500">
                <p class="text-gray-500 text-sm">Total Orders</p>
                <h3 class="text-3xl font-bold text-purple-800 mt-2"><?php echo $total_orders['total_orders']?></h3>
            </div>

            <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-purple-500">
                <p class="text-gray-500 text-sm">Total Users</p>
                <h3 class="text-3xl font-bold text-purple-800 mt-2"><?php echo $total_users['total_users']?></h3>
            </div>

            <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-yellow-400">
                <p class="text-gray-500 text-sm">Total Shops</p>
                <h3 class="text-3xl font-bold text-purple-800 mt-2"><?php echo $total_shops['total_shops']?></h3>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <section class="bg-white rounded-2xl shadow p-6 self-start">
                <h4 class="text-lg font-bold text-purple-800 mb-4">Top Shops</h4>
                <div class="space-y-4">
                    <?php foreach($top_shops as $shop):?>
                        <div class="border rounded-xl p-4 hover:bg-yellow-50 transition">
                            <div class="flex justify-between items-center mb-2">
                                <p class="font-semibold text-purple-700"><?php echo $shop['name']?></p>
                                <span class="text-sm text-gray-500"><?php echo $shop['orders']?></span>
                            </div>
                            <p class="text-sm text-gray-600">Revenue:<?php echo $shop['revenue']?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            
            <section class="bg-white rounded-2xl shadow p-6 self-start">
                <h4 class="text-lg font-bold text-purple-800 mb-4">Top Categories</h4>
                <div class="space-y-4">
                    <?php foreach($topCategories as $category):?>
                        <div class="border rounded-xl p-4 hover:bg-yellow-50 transition">
                            <div class="flex justify-between items-center mb-2">
                                <p class="font-semibold text-purple-700"><?php echo $category['name']?></p>
                                <span class="text-sm text-gray-500"><?php echo $category['shops']?></span>
                            </div>
                            <p class="text-sm text-gray-600">Revenue:<?php echo $category['revenue']?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            
            <section class="bg-white rounded-2xl shadow p-6 self-start">
                <h4 class="text-lg font-bold text-purple-800 mb-4">Alerts</h4>
                <div class="space-y-4">
                        <?php foreach($alerts as $alert):?>
                            <div class="flex justify-between items-center border rounded-xl p-4 hover:bg-yellow-50 transition">
                                <p class="font-medium text-gray-700"><?php echo $alert['label']?></p>
                                <?php if(strtolower($alert['label']) == 'pending shops'): ?>
                                    <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-medium"><?php echo $alert['value']?></span>
                                <?php elseif(strtolower($alert['label']) == 'blocked users'): ?>
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-medium"><?php echo $alert['value']?></span>
                                <?php elseif(strtolower($alert['label']) == 'temporary closed shops'): ?>
                                    <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-medium"><?php echo $alert['value']?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                </div>
            </section>

            

        </div>
        </main>

        
            

</div>
</body>
</html>