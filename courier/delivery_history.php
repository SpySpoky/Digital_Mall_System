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
        $sql .= " ORDER BY o.id DESC";

        $result = mysqli_query($conn, $sql);
        $orders = [];
        while($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }

    } else {

        $sql = "SELECT o.id as id, u.name as name, u.surname as surname, u.phone as phone, o.shipping_address, o.payment_type, o.total_amount, o.delivery_status, o.delivery_date
        FROM orders o
        left join users1 u on o.customer_id = u.id
        where courier_id = '$courier_id' AND delivery_status in ('delivered', 'cancelled')
        ORDER BY o.id DESC";

        $result = mysqli_query($conn, $sql);
        $orders = [];
        while($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }
    }
//profile

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courier Orders History - Digital Mall</title>
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
                <a href="index.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Dashboard</a>
                <a href="orders.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Orders</a>
                <a href="delivery_history.php" class="block px-4 py-3 rounded-lg bg-purple-700 border-l-4 border-yellow-300 font-semibold">Delivery History</a>
            </nav>

            <div class="p-4 border-purple-700">
                <a href="../logout.php" class="block px-4 py-3 rounded-lg bg-red-500 hover:bg-red-600 transition text-center font-semibold">Logout</a>
            </div>
        </aside>

        <main class="flex-1 min-w-0 max-w-full overflow-x-auto">
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-purple-800">Delivery History</h2>
                <p class="text-gray-600 mt-1">View your completed and failed deliveries.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-purple-700">
                    <p class="text-gray-500 text-sm">Total Completed</p>
                    <h3 class="text-3xl font-bold text-purple-800 mt-2">
                        <?php 
                        $sql = "SELECT count(*) as total_comp from orders where courier_id = '$courier_id' AND delivery_status = 'delivered'";
                        $result = mysqli_query($conn, $sql);
                        $total_comp = mysqli_fetch_assoc($result);
                        ?>
                        <?php echo $total_comp['total_comp']?>
                    </h3>
                </div>

                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-red-500">
                    <p class="text-gray-500 text-sm">Cancelled</p>
                    <h3 class="text-3xl font-bold text-purple-800 mt-2">
                        <?php 
                        $sql = "SELECT count(*) as total_can from orders where courier_id = '$courier_id' AND (delivery_status = 'cancelled' or status = 'cancelled')";
                        $result = mysqli_query($conn, $sql);
                        $total_can = mysqli_fetch_assoc($result);
                        ?>
                        <?php echo $total_can['total_can']?>
                    </h3>
                </div>
            </div>

            <section class="bg-white rounded-2xl shadow p-6 w-full">
                <form action="" method="GET">
                    <div class="flex flex-wrap items-center gap-4 mb-4">
                        <input
                            type="text"
                            name="Search"
                            placeholder="Search history..."
                            class="border rounded-lg px-4 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-purple-500"
                        >

                        <select name="Filter" class="border rounded-lg px-4 py-2">
                            <option value="">All statuses</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <button type="submit" name="SearchBtn" class="bg-purple-500 text-white ml-3 px-6 py-2 rounded-lg hover:bg-purple-600 transition">Search</button>
                    </div>
                </form>
                

                <div class="overflow-x-auto rounded-xl border">
                    <table class="w-full text-sm text-left">
                        <thead class="text-gray-500 border-b bg-gray-50 tracking-wider uppercase text-xs">
                            <tr>
                                <th class="px-6 py-4">ID</th>
                                <th class="px-6 py-4">Customer</th>
                                <th class="px-6 py-4 text-center">Phone</th>
                                <th class="px-6 py-4">Address</th>
                                <th class="px-6 py-4">Payment</th>
                                <th class="px-6 py-4">Total</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4">Delivery Date</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach($orders as $order): ?>
                                <tr class="border-b hover:bg-yellow-50 transition">
                                    <td class="px-6 py-4 font-semibold text-purple-700">
                                        #<?php echo $order['id']?>
                                    </td>

                                    <td class="px-6 py-4 truncate">
                                        <?php echo $order['name'] ." ". $order['surname']?>
                                    </td>

                                    <td class="px-6 py-4 truncate">
                                        <?php echo $order['phone']?>
                                    </td>

                                    <td class="px-6 py-4 max-w-[220px] truncate">
                                        <?php echo $order['shipping_address']?>
                                    </td>

                                    <td class="px-6 py-4">
                                        <?php echo $order['payment_type']?>
                                    </td>

                                    <td class="px-6 py-4 font-semibold">
                                        <?php echo $order['total_amount']?>
                                    </td>

                                    <td class="px-6 py-4">
                                        <?php if($order['delivery_status'] === 'delivered'): ?>
                                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">
                                                Delivered
                                            </span>
                                        <?php else: ?>
                                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-medium">
                                                Cancelled
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-6 py-4 text-gray-600 truncate">
                                        <?php echo $order['delivery_date']?>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
        
    </div>
    
</body>
</html>