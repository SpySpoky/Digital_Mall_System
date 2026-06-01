<?php
include "../session.php";
include "role.php";
require_once "../config/db.php";

    $user_id = $_SESSION['temp_user_id'];

    $sql = "SELECT s.*, u.name as manager_name, u.surname as manager_surname, c.name as category_name 
    from shops1 s
    left join users1 u on s.manager_id = u.id
    left join shop_category c on s.category_id = c.id
    where s.status = 'pending'
    LIMIT 6";

    $result = mysqli_query($conn, $sql);
    $shops = [];

    while($row = mysqli_fetch_assoc($result)) {
        $shops[] = $row;
    }

    $sql = "SELECT count(*) as total_shops from shops1";
    $result = mysqli_query($conn, $sql);
    $total_shops = mysqli_fetch_assoc($result);


    $sql = "SELECT count(*) as total_users from users1";
    $result = mysqli_query($conn, $sql);
    $total_users = mysqli_fetch_assoc($result);

    $sql = "SELECT count(*) as blocked_users from users1 where status = 'blocked'";
    $result = mysqli_query($conn, $sql);
    $blocked_users = mysqli_fetch_assoc($result);

    $sql = "SELECT count(*) as active_manager from users1 where role = 'manager' and status = 'active'";
    $result = mysqli_query($conn, $sql);
    $active_manager = mysqli_fetch_assoc($result);

    $sql = "SELECT count(*) as total_products from products where status != 'blocked'";
    $result = mysqli_query($conn, $sql);
    $total_products = mysqli_fetch_assoc($result);

    $sql = "SELECT count(*) as total_orders from orders";
    $result = mysqli_query($conn, $sql);
    $total_orders = mysqli_fetch_assoc($result);

    if(isset($_POST['ApproveBtn'])) {
        $shop_id = $_POST['Shop_id'];
        $sql = "UPDATE shops1 SET status = 'active' where id = '$shop_id'";
        mysqli_query($conn, $sql);
        header("Location:index.php");
        exit();
    } elseif(isset($_POST['RejectBtn'])) {
        $shop_id = $_POST['Shop_id'];
        $sql = "UPDATE shops1 SET status = 'blocked' where id = '$shop_id'";
        mysqli_query($conn, $sql);
        header("Location:index.php");
        exit();
    }

    
    

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Digital Mall</title>
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
                <a href="index.php" class="block px-4 py-3 rounded-lg bg-purple-700 border-l-4 border-yellow-300 font-semibold">Dashboard</a>
                <a href="users.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Users</a>
                <a href="shops.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Shops</a>
                <a href="category.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Categories</a>
                <a href="report.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Reports</a>
            </nav>

            <div class="p-4 border-purple-700">
                <a href="../logout.php" class="block px-4 py-3 rounded-lg bg-red-500 hover:bg-red-600 transition text-center font-semibold">Logout</a>
            </div>
        </aside>

        <main class="flex-1">
            <div class="">
                <div>
                    <h2 class="text-3xl font-extrabold text-purple-800">Admin Dashboard</h2>
                    <p class="text-gray-600 mt-1">Welcome back, Admin</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8 mt-8">
                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-purple-700">
                <p class="text-gray-500 text-sm">Total Users</p>
                <h3 class="text-3xl font-bold text-purple-800 mt-2"> <?php echo $total_users['total_users'] ?></h3>
                </div>

                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-yellow-400">
                    <p class="text-gray-500 text-sm">Total Shops</p>
                    <h3 class="text-3xl font-bold text-purple-800 mt-2"><?php echo $total_shops['total_shops'] ?></h3>
                </div>

                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-green-500">
                    <p class="text-gray-500 text-sm">Total Products</p>
                    <h3 class="text-3xl font-bold text-purple-800 mt-2"><?php echo $total_products['total_products']?></h3>
                </div>

                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-blue-500">
                    <p class="text-gray-500 text-sm">Total Orders</p>
                    <h3 class="text-3xl font-bold text-purple-800 mt-2"><?php echo $total_orders['total_orders']?></h3>
                </div>
                    
                <section class="bg-white rounded-2xl shadow p-6 w-full col-span-3">
                    
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-purple-800">Recent Shop Requests</h3>
                    </div>
                    <!-- example of card -->
                     <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
                     <?php
                     foreach($shops as $shop): ?>
                     <form action="" method="POST">
                        <div class='border rounded-xl p-5 hover:shadow-lg hover:-translate-y-1 hover:border-green-400 transition duration-200'>
                                <div class='flex items-center justify-between mb-4'>
                                    <h4 class='font-bold text-purple-800'><?php echo $shop['shop_name']?></h4>
                                    <input name="Shop_id" type="hidden" value="<?php echo $shop['id']?>">
                                </div>
                                
                                <div class='text-sm text-gray-600 space-y-1 mb-4'>
                                    <p class="truncate">Manager: <?php echo $shop['manager_name'].' '.$shop['manager_surname']?></p>
                                    <p class="truncate">Category: <?php echo $shop['category_name']?></p>
                                    <p>Status: <span class='bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs'><?php echo $shop['status']?></span></p>
                                </div>

                                <div class='flex gap-2'>
                                    <button type="submit" name="ApproveBtn" class='flex-1 bg-green-500 text-white py-1 text-sm rounded-lg hover:bg-green-600 transition'>Approve</button>
                                    <button type="submit" name="RejectBtn" class='flex-1 bg-red-500 text-white py-1 text-sm rounded-lg hover:bg-red-600 transition'>Reject</button>
                                </div>
                        </div>
                    </form>
                        <?php endforeach; ?>
                    
                    </div>
                </section>

                <section class="bg-white rounded-2xl shadow p-6 self-start">
                        <h4 class="text-lg font-bold text-purple-800 mb-3">System Status</h4>
                        <div class="space-y-3 text-sm text-gray-700">
                            <p class="flex justify-between"><span>Pending Shops</span><span class="font-semibold text-yellow-600"><?php echo count($shops)?></span></p>
                            <p class="flex justify-between"><span>Blocked Users</span><span class="font-semibold text-red-500"><?php echo $blocked_users['blocked_users']?></span></p>
                            <p class="flex justify-between"><span>Active Managers</span><span class="font-semibold text-green-600"><?php echo $active_manager['active_manager']?></span></p>
                        </div>
                </section>
            </div>
        </main>

    </div>
</body>
</html>