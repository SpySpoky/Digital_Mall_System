<?php

require_once "../config/db.php";
include "../session.php";
include "role.php";

    $manager_id = $_SESSION['temp_user_id'];
    $sql = "SELECT id from shops1 where manager_id = '$manager_id'";
    $result = mysqli_query($conn, $sql);
    $shop = mysqli_fetch_assoc($result);
    $shop_id = $shop['id'];

    $sql = "SELECT * from users1 where role = 'courier' and shop_id_courier = '$shop_id'";
    $result = mysqli_query($conn, $sql);
    $couriers = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $couriers[] = $row;
    }

    $sql_free_couriers = "SELECT * FROM users1 
                      WHERE role = 'courier' 
                      AND (shop_id_courier IS NULL OR shop_id_courier = '')
                      AND status = 'active'
                      ORDER BY id";
    $result_free = mysqli_query($conn, $sql_free_couriers);
    $free_couriers = [];
    while($row = mysqli_fetch_assoc($result_free)) {
        $free_couriers[] = $row;
    }

    if(isset($_GET['SearchBtn'])) {
        $search = $_GET['Search'];
        $filter = $_GET['Filter'];

        $sql = "SELECT o.*, u.name as name, u.surname as surname, c.name as courier_name, c.surname as courier_surname
        from orders o
        left join users1 u on o.customer_id = u.id
        left join users1 c on o.courier_id = c.id
        where shop_id = '$shop_id'";

        if(!empty($search)) {
            $sql .= " AND (o.id LIKE '%$search%'
            OR u.name LIKE '%$search%' 
            OR u.surname LIKE '%$search%' 
            OR o.shipping_address LIKE '%$search%')";
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

        $sql = "SELECT o.*, u.name as name, u.surname as surname, c.name as courier_name, c.surname as courier_surname
        from orders o
        left join users1 u on o.customer_id = u.id
        left join users1 c on o.courier_id = c.id
        where shop_id = '$shop_id'
        order by o.id DESC";

        $result = mysqli_query($conn, $sql);
        $orders = [];

        while($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }

    }



    if(isset($_POST['EditDeliveryBtn'])) {
        $order_id = $_POST['EditOrderId'];
        $status = $_POST['delivery_status'];
        $courier = $_POST['delivery_courier'];

        if($courier === "" || $courier === null) {
            $sql = "UPDATE orders SET delivery_status = '$status', courier_id = NULL WHERE id = '$order_id'";
        } else {
            $sql = "UPDATE orders SET delivery_status = '$status', courier_id = '$courier' WHERE id = '$order_id'";
        }
        
        mysqli_query($conn, $sql);

        if($status == 'delivered') {
            $sql = "UPDATE orders SET status = '$status' where id = '$order_id'";
            mysqli_query($conn, $sql);
        }

        header("Location: deliveries.php");
        exit();

    }

    if(isset($_POST['AddCourierBtn'])) {
        $name = $_POST['Name'];
        $surname = $_POST['Surname'];
        $email = $_POST['Email'];
        $phone = $_POST['Phone'];
        $password = $_POST['Password'];
        $confirm = $_POST['ConfirmPass'];
        $status = $_POST['Status'];
        $address = 'Default';
        $role = 'courier';

        $sql = "SELECT id from users1 where email = '$email'";
        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) > 0) {
            $error = "User with this email already exists!";
            header("Location: deliveries.php");
            exit();
        } else {
            if($password == $confirm) {
                $hashed_password = password_hash($_POST['Password'], PASSWORD_DEFAULT);

                $sql = "INSERT into users1(name, surname, phone, email, role, password, status, address, shop_id_courier) VALUES ('$name', '$surname', '$phone', '$email', '$role', '$hashed_password', '$status', '$address', '$shop_id')";
                mysqli_query($conn, $sql);

                header("Location: deliveries.php");
                exit();

            } else {
                $error = "Passwords does not match!";
                header("Location: deliveries.php");
                exit();
            }
        }

        
    }

    if(isset($_POST['EditCourierBtn'])) {// edit courier
        $id = $_POST['EditId'];
        $status = $_POST['EditStatus'];

        $sql = "UPDATE users1 SET status = '$status' where id = '$id'";
        mysqli_query($conn, $sql);

        header("Location: deliveries.php");
        exit();

    }

    if(isset($_POST['DeleteCourierBtn'])) {//delete courier
        $id = $_POST['courier_id'];
        
        $sql = "SELECT * from orders where courier_id = '$id'";
        $result = mysqli_query($conn, $sql);
        if(mysqli_num_rows($result) > 0) {
            $error = "Courier has deliveries.";
            $sql = "UPDATE users1 SET status = 'blocked' where id = '$id'";
            mysqli_query($conn, $sql);
        }
        else {
            $sql = "UPDATE users1 SET status = 'fired' where id = '$id'";
            mysqli_query($conn, $sql);
        }
        header("Location: deliveries.php");
        exit();
    }

    if(isset($_POST['AddToMyShopBtn'])) {
        $id = $_POST['FreeCourierId'];
        $sql = "UPDATE users1 SET shop_id_courier = '$shop_id' where id = '$id'";
        mysqli_query($conn, $sql);
        header("Location: deliveries.php");
        exit();
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Deliveries - Digital Mall</title>
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
        <aside class="w-64 flex-shrink-0 bg-purple-800 text-white flex flex-col rounded-2xl shadow-xl sticky self-start top-6">
            <div class="p-6 border-b border-purple-700">
                <img src="../images/logo.png" class="w-16 mx-auto mb-2" alt="Logo.png">
                <h1 class="text-2xl font-extrabold tracking-wide">Digital Mall</h1>
                <p class="text-sm text-purple-200 mt-1">Manager Panel</p>
            </div>

            <nav class="flex-1 p-4 space-y-2">
                <a href="index.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Dashboard</a>
                <a href="my-shop.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">My Shop Info</a>
                <a href="products.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Products</a>
                <a href="orders.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Orders</a>
                <a href="deliveries.php" class=" block px-4 py-3 rounded-lg bg-purple-700 border-l-4 border-yellow-300 font-semibold">Deliveries</a>
            </nav>

            <div class="p-4 border-purple-700">
                <a href="../logout.php" class="block px-4 py-3 rounded-lg bg-red-500 hover:bg-red-600 transition text-center font-semibold">Logout</a>
            </div>
        </aside>

        <main class="flex-1">
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-purple-800">Deliveries Management</h2>
                <p class="text-gray-600 mt-1">Appoint a courier and manage deliveries.</p>
                
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-yellow-500">
                    <p class="text-gray-500 text-sm">Ready for Delivery</p>
                    <?php 
                        $sql = "SELECT count(*) as ready_ from orders where shop_id = '$shop_id' AND status = 'ready_for_delivery'";
                        $result = mysqli_query($conn, $sql);
                        $ready_stats = mysqli_fetch_assoc($result);
                    ?>
                    <h3 class="text-3xl font-bold text-purple-800 mt-2"><?php echo $ready_stats['ready_'] ?></h3>
                </div>
                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-blue-500">
                    <p class="text-gray-500 text-sm">Assigned to Courier</p>
                    <?php 
                        $sql = "SELECT count(*) as assigned from orders where shop_id = '$shop_id' AND courier_id is not NULL";
                        $result = mysqli_query($conn, $sql);
                        $assigned_stats = mysqli_fetch_assoc($result);
                    ?>
                    <h3 class="text-3xl font-bold text-purple-800 mt-2"><?php echo $assigned_stats['assigned'] ?></h3>
                    
                </div>
                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-purple-500">
                    <p class="text-gray-500 text-sm">In Delivery</p>
                    <?php 
                        $sql = "SELECT count(*) as in_delivery from orders where shop_id = '$shop_id' AND delivery_status = 'in_delivery'";
                        $result = mysqli_query($conn, $sql);
                        $in_delivery_stats = mysqli_fetch_assoc($result);
                    ?>
                    <h3 class="text-3xl font-bold text-purple-800 mt-2"><?php echo $in_delivery_stats['in_delivery'] ?></h3>
                </div>
                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-green-500">
                    <p class="text-gray-500 text-sm">Delivered</p>
                    <?php 
                        $sql = "SELECT count(*) as delivered from orders where shop_id = '$shop_id' AND delivery_status = 'delivered'";
                        $result = mysqli_query($conn, $sql);
                        $delivered_stat = mysqli_fetch_assoc($result);
                    ?>
                    <h3 class="text-3xl font-bold text-purple-800 mt-2"><?php echo $delivered_stat['delivered'] ?></h3>
                </div>
                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-red-500">
                    <p class="text-gray-500 text-sm">Not Assigned</p>
                    <?php 
                        $sql = "SELECT count(*) as not_assigned from orders where shop_id = '$shop_id' AND courier_id is NULL";
                        $result = mysqli_query($conn, $sql);
                        $not_assigned_stat = mysqli_fetch_assoc($result);
                    ?>
                    <h3 class="text-3xl font-bold text-purple-800 mt-2"><?php echo $not_assigned_stat['not_assigned'] ?></h3>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow p-6">
                <div class="flex flex-wrap items-center gap-4 mb-4">
                    <form action="" method="GET">
                        <input
                            type="text"
                            placeholder="Search delivery..."
                            class="border rounded-lg px-4 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-purple-500"
                            name="Search">

                        <select name="Filter" class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">All statuses</option>
                            <option value="pending">Pending</option>
                            <option value="assigned">Assigned</option>
                            <option value="in_delivery">In Delivery</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <button type="submit" name="SearchBtn" class="bg-purple-500 text-white ml-3 px-6 py-2 rounded-lg hover:bg-purple-600 transition">Search</button>
                    </form>
                    <button
                    type="button"
                    onclick="openCouriersModal()"
                    class="bg-blue-500 text-white px-3 py-2 rounded-lg hover:bg-blue-600 transition font-semibold shadow">
                    Manage Couriers
                </button>
                </div>

                <div class="overflow-hidden rounded-xl border">
                    <table class="w-full text-sm text-left">
                        <thead class="text-gray-500 border-b bg-gray-50 tracking-wider uppercase text-xs">
                            <tr>
                                <th class="px-6 py-4">Order ID</th>
                                <th class="px-6 py-4">Customer</th>
                                <th class="px-6 py-4">Shipping Address</th>
                                <th class="px-6 py-4">Delivery Status</th>
                                <th class="px-6 py-4">Courier</th>
                                <th class="px-6 py-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $order): ?>
                                <tr class="border-b hover:bg-yellow-50 transition-color">
                                    <td class="px-6 py-4">#<?php echo $order['id']?></td>
                                    <td class="px-6 py-4"><?php echo $order['name'].' '. $order['surname']?></td>
                                    <td class="px-6 py-4"><?php echo $order['shipping_address']?></td>
                                    <td class="px-6 py-4">
                                        <?php if($order['delivery_status'] == 'assigned'):?>
                                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium">Assigned</span>
                                        <?php elseif($order['delivery_status'] == 'in_delivery'):?>
                                            <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-medium">In Delivery</span>
                                        <?php elseif($order['delivery_status'] == 'delivered'):?>
                                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">Delivered</span>
                                        <?php elseif($order['delivery_status'] == 'pending'):?>
                                            <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-medium">Pending</span>
                                        <?php elseif($order['delivery_status'] == 'cancelled'):?>
                                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-medium">Cancelled</span>
                                        <?php endif;?>
                                    </td>
                                    <?php if($order['courier_id'] != NULL):?>
                                    <td class="px-6 py-4"><?php echo $order['courier_name'].' '. $order['courier_surname']?></td>
                                    <?php else: ?>
                                    <td class="px-6 py-4">Not Assigned</td>
                                    <?php endif;?>
                                    <td class="px-6 py-4">
                                        <button class="bg-yellow-400 text-white px-2 py-1 rounded text-xs hover:bg-yellow-500 transition" type="button"
                                        onclick='openDeliveryModal(<?php echo $order["id"]?>,
                                        <?php echo json_encode($order["name"])?>,
                                        <?php echo json_encode($order["surname"])?>,
                                        <?php echo json_encode($order["delivery_status"])?>,
                                        <?php echo json_encode($order["courier_id"])?>
                                        )'>✏️</button>
                                    </td>
                                </tr>
                            <?php endforeach;?>
                        </tbody>
                    </table>
                </div>
        </main>

        <!-- edit delivery modal -->
         <div id="editDeliveryModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl p-6">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-2xl font-bold text-purple-800">Edit Delivery</h3>
                    <button type="button" onclick="closeEditDeliveryModal()" class="text-gray-500 hover:text-red-500 text-xl font-bold">✕</button>
                </div>

                <form action="" method="POST" class="space-y-4">

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Order ID</label>
                            <input
                                type="text"
                                id="editOrderId"
                                name="EditOrderId"
                                class="w-full border rounded-lg px-4 py-2 bg-gray-100"
                                readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Customer</label>
                            <input
                                type="text"
                                id="editCustomer"
                                class="w-full border rounded-lg px-4 py-2 bg-gray-100"
                                readonly>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <select
                            name="delivery_status"
                            id="editStatus"
                            class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="pending">Pending</option>
                            <option value="assigned">Assigned</option>
                            <option value="in_delivery">In Delivery</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Assign Courier</label>
                        <select
                            name="delivery_courier"
                            id="editCourier"
                            class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">Not assigned</option>
                            <?php foreach($couriers as $courier): ?>
                                <?php if($courier['status'] === 'active'): ?>
                                <option value="<?php echo $courier['id'] ?>">
                                    <?php echo $courier['name']. ' ' . $courier['surname'] ?> (<?php echo $courier['status'] ?>)
                                </option>
                                <?php endif;?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">

                        <button
                            type="submit"
                            name="EditDeliveryBtn"
                            class="px-5 py-2.5 rounded-lg bg-green-500 text-white hover:bg-green-600 transition font-semibold">
                            Save Changes
                        </button>
                        <button
                            type="button"
                            onclick="closeEditDeliveryModal()"
                            class="px-5 py-2.5 rounded-lg border border-gray-300 hover:bg-gray-100 transition">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

    <!-- list courier popup -->
     <div id="couriersModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white w-full max-w-4xl rounded-2xl shadow-2xl p-6">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-2xl font-bold text-purple-800">Couriers List</h3>

                <div class="flex items-center gap-3">

                    <button
                        type="button"
                        onclick="openFreeCouriersModal()"
                        class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition font-semibold">
                        Free Couriers
                    </button>
                    <button
                        type="button"
                        onclick="openAddCourierModal()"
                        class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition font-semibold">
                        + Add Courier
                    </button>

                    <button type="button" onclick="closeCouriersModal()" class="text-gray-500 hover:text-red-500 text-xl font-bold">✕</button>
                </div>
            </div>

            <div class="max-h-[400px] overflow-y-auto rounded-xl border">
                <table class="w-full text-sm text-left">
                    <thead class="sticky top-0 bg-gray-50 z-10 text-gray-500 border-b uppercase text-xs">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Name & Surname</th>
                            <th class="px-6 py-4">Phone</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-center w-32">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($couriers as $courier): ?>
                            <tr class="border-b hover:bg-yellow-50 transition">
                                <td class="px-6 py-4">#<?php echo $courier['id'] ?></td>
                                <td class="px-6 py-4 font-medium"><?php echo $courier['name'] ." ". $courier['surname']?></td>
                                <td class="px-6 py-4 truncate"><?php echo $courier['phone'] ?></td>
                                <td class="px-6 py-4"><?php echo $courier['email'] ?></td>
                                <td class="px-6 py-4">
                                    <?php if($courier['status'] == 'active'): ?>
                                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">Active</span>
                                    <?php elseif($courier['status'] == 'busy'): ?>
                                        <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-medium">Busy</span>
                                    <?php elseif($courier['status'] == 'fired'): ?>
                                        <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-medium">Fired</span>
                                    <?php elseif($courier['status'] == 'blocked'): ?>
                                        <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-medium">Blocked</span>
                                    <?php else: ?>
                                        <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-medium">Offline</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">

                                    
                                        <button
                                            type="button"
                                            class="bg-yellow-400 text-white px-3 py-2 rounded-lg text-xs hover:bg-yellow-500 transition font-semibold"
                                            onclick="openEditCourierModal(<?php echo $courier['id']?>, '<?php echo $courier['name']?>', '<?php echo $courier['surname']?>', '<?php echo $courier['phone']?>', '<?php echo $courier['email']?>', '<?php echo $courier['status']?>')">
                                            ✏️
                                        </button> 
                                        <button
                                            type="button"
                                            class="bg-red-500 text-white px-3 py-2 rounded-lg text-xs hover:bg-red-600 transition font-semibold"
                                            onclick="openDeleteCourierModal(<?php echo $courier['id']?>, '<?php echo $courier['name']?>', '<?php echo $courier['surname']?>')">
                                            🗑
                                        </button>   
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- add courier popup -->
        <div id="addCourierModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[60] p-4">
                <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6 max-h-[90vh] overflow-y-auto">
                    <div class="flex justify-between items-center mb-5">
                        <h3 class="text-2xl font-bold text-purple-800">Add Courier</h3>
                        <button type="button" onclick="closeAddCourierModal()" class="text-gray-500 hover:text-red-500 text-xl font-bold">✕</button>
                    </div>

                    <form method="POST" class="space-y-4" id="AddCourierForm">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Name</label>
                            <input
                                type="text"
                                name="Name"
                                placeholder="Enter courier name"
                                class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Surname</label>
                            <input
                                type="text"
                                name="Surname"
                                placeholder="Enter courier surname"
                                class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Phone</label>
                            <input
                                type="tel"
                                name="Phone"
                                placeholder="+7-915-472-72-36"
                                pattern="[\+\d\s\-\(\)]{5,20}"
                                class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                            <input
                                type="email"
                                name="Email"
                                placeholder="Enter email"
                                class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                            <input
                                type="password"
                                name="Password"
                                id="Password"
                                placeholder="Enter password"
                                class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500"
                                minlength="6"
                                required>
                            <p class="text-xs text-gray-500 mt-1">At least 6 characters</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm password</label>
                            <input
                                type="password"
                                name="ConfirmPass"
                                id="ConfPass"
                                placeholder="Enter password again"
                                class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500"
                                minlength="6"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                            <select
                                name="Status"
                                class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="active">Active</option>
                                <option value="busy">Busy</option>
                                <option value="offline">Offline</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                             <button
                                type="submit"
                                name="AddCourierBtn"
                                class="px-5 py-2.5 rounded-lg bg-green-500 text-white hover:bg-green-600 transition font-semibold">
                                Add Courier
                            </button>

                            <button
                                type="button"
                                onclick="closeAddCourierModal()"
                                class="px-5 py-2.5 rounded-lg border border-gray-300 hover:bg-gray-100 transition">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        
        <!-- delete courier popup-->

        <div id="deleteCourierModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[70] p-4">
            <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-2xl font-bold text-red-600">Delete Courier</h3>
                    <button
                        type="button"
                        onclick="closeDeleteCourierModal()"
                        class="text-gray-500 hover:text-red-500 text-xl font-bold">
                        ✕
                    </button>
                </div>

                <p class="text-gray-600 mb-6 leading-relaxed">
                    Are you sure you want to delete
                    <span id="deleteCourierName" class="font-semibold text-gray-800"></span>?
                </p>

                <form action="" method="POST" class="flex justify-end gap-3">
                    <input type="hidden" name="courier_id" id="deleteCourierId">

                    <button
                        type="submit"
                        name="DeleteCourierBtn"
                        class="px-5 py-2.5 rounded-lg bg-red-500 text-white hover:bg-red-600 transition font-semibold">
                        Delete
                    </button>
                    <button
                        type="button"
                        onclick="closeDeleteCourierModal()"
                        class="px-5 py-2.5 rounded-lg border border-gray-300 hover:bg-gray-100 transition">
                        Cancel
                    </button>

                    
                </form>
            </div>
        </div>

        <!-- edit courier popup -->
<div id="editCourierModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[65] p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-2xl font-bold text-purple-800">Edit Courier</h3>
            <button type="button" onclick="closeEditCourierModal()" class="text-gray-500 hover:text-red-500 text-xl font-bold">✕</button>
        </div>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="EditId" id="editCourierId">

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Name</label>
                <input
                    type="text"
                    name="EditName"
                    id="editCourierName"
                    class="w-full border rounded-lg px-4 py-3 bg-gray-100"
                    readonly>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Surname</label>
                <input
                    type="text"
                    name="EditSurname"
                    id="editCourierSurname"
                    class="w-full border rounded-lg px-4 py-3 bg-gray-100"
                    readonly>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Phone</label>
                <input
                    type="tel"
                    name="EditPhone"
                    id="editCourierPhone"
                    class="w-full border rounded-lg px-4 py-3 bg-gray-100"
                    readonly>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                <input
                    name="EditEmail"
                    id="editCourierEmail"
                    class="w-full border rounded-lg px-4 py-3 bg-gray-100"
                    readonly>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                <select
                    name="EditStatus"
                    id="editCourierStatus"
                    class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="active">Active</option>
                    <option value="busy">Busy</option>
                    <option value="offline">Offline</option>
                    <option value="fired">Fired</option>
                    <option value="blocked">Blocked</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button
                    name="EditCourierBtn"
                    type="submit"
                    class="px-5 py-2.5 rounded-lg bg-green-500 text-white hover:bg-green-600 transition font-semibold">
                    Save Changes
                </button>
                <button
                    type="button"
                    onclick="closeEditCourierModal()"
                    class="px-5 py-2.5 rounded-lg border border-gray-300 hover:bg-gray-100 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Free Couriers Popup -->
<div id="freeCouriersModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[55] p-4">
    <div class="bg-white w-full max-w-4xl rounded-2xl shadow-2xl p-6">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-2xl font-bold text-purple-800">Free Couriers (Available to Assign)</h3>
            <button type="button" onclick="closeFreeCouriersModal()" class="text-gray-500 hover:text-red-500 text-xl font-bold">✕</button>
        </div>

        <div class="max-h-[400px] overflow-y-auto rounded-xl border">
            <table class="w-full text-sm text-left">
                <thead class="sticky top-0 bg-gray-50 z-10 text-gray-500 border-b uppercase text-xs">
                    <tr>
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Name & Surname</th>
                        <th class="px-6 py-4">Phone</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($free_couriers) > 0): ?>
                        <?php foreach($free_couriers as $courier): ?>
                            <tr class="border-b hover:bg-yellow-50 transition">
                                <td class="px-6 py-4">#<?php echo $courier['id']; ?></td>
                                <td class="px-6 py-4 font-medium"><?php echo $courier['name'] . " " . $courier['surname']; ?></td>
                                <td class="px-6 py-4 truncate"><?php echo $courier['phone']; ?></td>
                                <td class="px-6 py-4"><?php echo $courier['email']; ?></td>
                                <td class="px-6 py-4">
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">Free</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center">
                                        <form action="" method="POST">
                                            <input type="hidden" value="<?php echo $courier['id']?>" name="FreeCourierId">
                                            <button
                                                type="submit"
                                                name="AddToMyShopBtn"
                                                onclick=""
                                                class="bg-purple-500 text-white px-3 py-2 rounded-lg text-xs hover:bg-purple-600 transition font-semibold">
                                                + Add to My Shop
                                            </button>

                                        </form>
                                        
                                    </div>
                                </td>
                            </td>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                No free couriers available. Ask admin to create more couriers.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

    <script src="manager_js/deliveries.js"></script>
</body>
</html>