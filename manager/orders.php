<?php
require_once "../config/db.php";
include "../session.php";
include "role.php";

$manager_id = $_SESSION['temp_user_id'];
$sql = "SELECT id from shops1 where manager_id = '$manager_id'";
$result = mysqli_query($conn, $sql);
$shop = mysqli_fetch_assoc($result);

$shop_id = $shop['id'];

if(isset($_GET['SearchBtn'])) {
    $search = $_GET['Search'];
    $filter_payment = $_GET['FilterPayment'];
    $filter_status = $_GET['FilterStatus'];

    $sql = "SELECT o.*, u.name as name, u.surname as surname, u.phone as phone, c.name as courier_name, c.surname as courier_surname
    from orders o
    left join users1 u on o.customer_id = u.id
    left join users1 c on o.courier_id = c.id
    where o.shop_id = '$shop_id'";

    if(!empty($search)) {
        $sql .= " AND (o.id LIKE '%$search%' OR u.name LIKE '%$search%' OR u.surname LIKE '%$search%')";
    }
    if(!empty($filter_payment)) {
        $sql .= " AND o.payment_type = '$filter_payment'";
    }
    if(!empty($filter_status)) {
        $sql .= " AND o.status = '$filter_status'";
    }

    $sql .= " ORDER BY o.id DESC";

    $result = mysqli_query($conn, $sql);
    $orders = [];

    while($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }

    $sql = "SELECT oi.*, p.name as product_name
    from order_items oi
    left join products p on oi.product_id = p.id
    where oi.order_id IN (SELECT id from orders where shop_id = '$shop_id')";

    $result = mysqli_query($conn, $sql);
    $order_items = [];

    while($row = mysqli_fetch_assoc($result)) {
        $order_id = $row['order_id'];
        if(!isset($order_items[$order_id])) {
            $order_items[$order_id] = [];
        }
        $order_items[$order_id][] = ['product_name' => $row['product_name'], 'quantity' => $row['quantity'], 'unit_price' => $row['unit_price'], 'total_price' => $row['total_price']];

    }

} else {
    $sql = "SELECT o.*, u.name as name, u.surname as surname, u.phone as phone, c.name as courier_name, c.surname as courier_surname
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

    $sql = "SELECT oi.*, p.name as product_name
    from order_items oi
    left join products p on oi.product_id = p.id
    where oi.order_id IN (SELECT id from orders where shop_id = '$shop_id')";

    $result = mysqli_query($conn, $sql);
    $order_items = [];

    while($row = mysqli_fetch_assoc($result)) {
        $order_id = $row['order_id'];
        if(!isset($order_items[$order_id])) {
            $order_items[$order_id] = [];
        }
        $order_items[$order_id][] = ['product_name' => $row['product_name'], 'quantity' => $row['quantity'], 'unit_price' => $row['unit_price'], 'total_price' => $row['total_price']];

    }
}

if(isset($_POST['EditBtn'])) {
    $id = $_POST['EditId'];
    $status = $_POST['EditStatus'];
    
    $sql = "UPDATE orders SET status = '$status' where id = '$id'";

    mysqli_query($conn, $sql);

    header("Location: orders.php");
    exit();    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Orders - Digital Mall</title>
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
                <a href="orders.php" class="block px-4 py-3 rounded-lg bg-purple-700 border-l-4 border-yellow-300 font-semibold">Orders</a>
                <a href="deliveries.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Deliveries</a>
            </nav>

            <div class="p-4 border-purple-700">
                <a href="../logout.php" class="block px-4 py-3 rounded-lg bg-red-500 hover:bg-red-600 transition text-center font-semibold">Logout</a>
            </div>
        </aside>
    
        <main class="flex-1 min-w-0">
            <div class="mb-6">
                <h2 class="text-3xl font-extrabold text-purple-800">Orders</h2>
                <p class="text-gray-600 mt-1">Manage customer orders for your shop.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-purple-700">
                    <p class="text-gray-500 text-sm">Total Orders</p>
                    <?php 
                    $sql = "SELECT count(*) as count from orders where shop_id = '$shop_id'";
                    $result = mysqli_query($conn, $sql);
                    $total_orders = mysqli_fetch_assoc($result);
                    ?>
                    <h3 class="text-3xl font-bold text-purple-800 mt-2"><?php echo $total_orders['count']?></h3>
                </div>

                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-yellow-400">
                    <p class="text-gray-500 text-sm">Pending Orders</p>
                    <?php 
                    $sql = "SELECT count(*) as count from orders where shop_id = '$shop_id' AND status = 'pending'";
                    $result = mysqli_query($conn, $sql);
                    $pending_orders = mysqli_fetch_assoc($result);
                    ?>
                    <h3 class="text-3xl font-bold text-purple-800 mt-2"><?php echo $pending_orders['count']?></h3>
                </div>

                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-orange-500">
                    <p class="text-gray-500 text-sm">Preparing Orders</p>
                    <?php 
                    $sql = "SELECT count(*) as count from orders where shop_id = '$shop_id' AND status = 'preparing'";
                    $result = mysqli_query($conn, $sql);
                    $preparing_orders = mysqli_fetch_assoc($result);
                    ?>
                    <h3 class="text-3xl font-bold text-purple-800 mt-2"><?php echo $preparing_orders['count']?></h3>
                </div>

                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-green-500">
                    <p class="text-gray-500 text-sm">Ready Orders</p>
                    <?php 
                    $sql = "SELECT count(*) as count from orders where shop_id = '$shop_id' AND status = 'ready_for_delivery'";
                    $result = mysqli_query($conn, $sql);
                    $ready_orders = mysqli_fetch_assoc($result);
                    ?>
                    <h3 class="text-3xl font-bold text-purple-800 mt-2"><?php echo $ready_orders['count']?></h3>
                </div>

                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-red-500">
                    <p class="text-gray-500 text-sm">Cancelled Orders</p>
                    <?php 
                    $sql = "SELECT count(*) as count from orders where shop_id = '$shop_id' AND status = 'cancelled'";
                    $result = mysqli_query($conn, $sql);
                    $cancelled_orders = mysqli_fetch_assoc($result);
                    ?>
                    <h3 class="text-3xl font-bold text-purple-800 mt-2"><?php echo $cancelled_orders['count']?></h3>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow p-6">
                <div class="flex flex-wrap items-center gap-4 mb-4">
                    <form action="" method="GET">
                        <input
                        name="Search"
                        type="text"
                        placeholder="Search order..."
                        class="border rounded-lg px-4 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-purple-500">

                        <select name="FilterStatus" class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">All statuses</option>
                            <option value="pending">Pending</option>
                            <option value="preparing">Preparing</option>
                            <option value="ready_for_delivery">Ready for Delivery</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>

                        <select name="FilterPayment" class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">All payments</option>
                            <option value="card">Card</option>
                            <option value="cash">Cash</option>
                        </select>
                        <button type="submit" name="SearchBtn" class="bg-purple-500 text-white ml-3 px-6 py-2 rounded-lg hover:bg-purple-600 transition">Search</button>
                    </form>
                    
                </div>

                <div class="overflow-hidden rounded-xl border">
                    <table class="w-full text-sm text-left">
                        <thead class="text-gray-500 border-b bg-gray-50 tracking-wider uppercase text-xs">
                            <tr>
                                <th class="px-6 py-4">ID</th>
                                <th class="px-6 py-4">Customer</th>
                                <th class="px-6 py-4">Total</th>
                                <th class="px-6 py-4">Payment</th>
                                <th class="px-6 py-4">Address</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Order Date</th>
                                <th class="px-6 py-4">Delivery Date</th>
                                <th class="px-6 py-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="">
                                <?php foreach($orders as $order): ?>
                                <tr class="border-b hover:bg-yellow-50 transition-color">
                                <td class="px-6 py-4">#<?php echo $order['id']?></td>
                                <td class="px-6 py-4 truncate"><?php echo $order['name'] .' '. $order['surname']?></td>
                                <td class="px-6 py-4">$<?php echo $order['total_amount']?></td>
                                <td class="px-6 py-4"><?php echo $order['payment_type']?></td>
                                <td class="px-6 py-4 truncate"><?php echo $order['shipping_address']?></td>
                                <td class="px-6 py-4">
                                <?php if($order['status'] == 'pending'): ?>
                                    <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-medium">Pending</span>
                                <?php elseif($order['status'] == "confirmed"): ?>
                                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium">Confirmed</span>
                                <?php elseif($order['status'] == "preparing"): ?>
                                    <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-medium">Preparing</span>
                                <?php elseif($order['status'] == "ready_for_delivery"): ?>
                                    <span class="bg-[#e2f0d9] text-[#4a5d23] px-3 py-1 rounded-full text-xs font-medium">Ready</span>
                                <?php elseif($order['status'] == "delivered"): ?>
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">Delivered</span>
                                <?php else: ?>
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-medium">Cancelled</span>
                                <?php endif;?>
                                </td>
                                <td class="px-6 py-4 text-gray-600"><?php echo $order['order_date'] ?></td>
                                <td class="px-6 py-4 text-gray-600"><?php echo $order['delivery_date'] ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center gap-2">
                                        <button name="ViewOrder" class="bg-blue-500 text-white px-2 py-1 rounded text-xs hover:bg-blue-600 transition" type="button"
                                        onclick='viewOrder(
                                        <?php echo $order["id"] ?>,
                                        <?php echo json_encode($order["name"])?>,
                                        <?php echo json_encode($order["surname"])?>,
                                        <?php echo json_encode($order["phone"])?>,
                                        <?php echo json_encode($order["payment_type"]) ?>,
                                        <?php echo json_encode($order["order_date"])?>,
                                        <?php echo json_encode($order["delivery_date"])?>,
                                        <?php echo json_encode($order["shipping_address"])?>,
                                        <?php echo json_encode($order["status"])?>,
                                        <?php echo json_encode($order["delivery_status"])?>,
                                        <?php echo json_encode($order["courier_name"])?>,
                                        <?php echo json_encode($order["courier_surname"])?>,
                                        <?php echo $order["total_amount"]?>,
                                        <?php $items = isset($order_items[$order["id"]]) ? $order_items[$order["id"]]: [];
                                        echo json_encode($items); ?>                                                                   
                                        )'>👁</button>
                                        <button class="bg-yellow-400 text-white px-2 py-1 rounded text-xs hover:bg-yellow-500 transition" type="button"
                                        onclick='editOrder(
                                        <?php echo $order["id"]?>,<?php echo json_encode($order["name"])?>, <?php echo json_encode($order["surname"])?>, <?php echo json_encode($order["status"])?>)''>✏️</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
        </main>
        <!-- view order popup -->

        <div id="viewOrderModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[90vh] flex flex-col relative">
                
                <div class="flex justify-between items-center p-6 mb-6">
                    <h3 class="text-2xl font-bold text-purple-800">Order Details</h3>
                    <button 
                        type="button"
                        onclick="closeViewOrderModal()" 
                        class="text-gray-500 hover:text-red-500 text-3xl leading-none"
                    >
                        &times;
                    </button>
                </div>

                <div class="overflow-y-auto p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="bg-gray-50 border rounded-lg p-4">
                            <p class="text-sm text-gray-500">Order ID</p>
                            <p id="viewOrderId" class="text-lg font-bold"></p>
                        </div>

                        <div class="bg-gray-50 border rounded-lg p-4">
                            <p class="text-sm text-gray-500">Customer</p>
                            <p id="viewOrderCustomer" class="text-lg font-semibold text-gray-800"></p>
                        </div>

                        <div class="bg-gray-50 border rounded-lg p-4">
                            <p class="text-sm text-gray-500">Phone</p>
                            <p id="viewOrderPhone" class="text-lg font-semibold text-gray-800"></p>
                        </div>

                        <div class="bg-gray-50 border rounded-lg p-4">
                            <p class="text-sm text-gray-500">Payment</p>
                            <p id="viewOrderPayment" class="text-lg font-semibold text-gray-800"></p>
                        </div>

                        <div class="bg-gray-50 border rounded-lg p-4">
                            <p class="text-sm text-gray-500">Order Date</p>
                            <p id="viewOrderDate" class="text-lg font-semibold text-gray-800"></p>
                        </div>

                        <div class="bg-gray-50 border rounded-lg p-4">
                            <p class="text-sm text-gray-500">Delivery Date</p>
                            <p id="viewDeliveryDate" class="text-lg font-semibold text-gray-800"></p>
                        </div>

                        <div class="bg-gray-50 border rounded-lg p-4">
                            <p class="text-sm text-gray-500">Address</p>
                            <p id="viewOrderAddress" class="text-lg font-semibold text-gray-800"></p>
                        </div>

                        <div class="bg-gray-50 border rounded-lg p-4">
                            <p class="text-sm text-gray-500">Status</p>
                            <span id="viewOrderStatus" class="text-gray-800 rounded-full text-lg font-semibold"></span>
                        </div>

                        <div class="bg-gray-50 border rounded-lg p-4">
                            <p class="text-sm text-gray-500">Delivery Status</p>
                            <span id="viewDeliveryStatus" class="text-gray-800 rounded-full text-lg font-semibold"></span>
                        </div>
                        
                        <div class="bg-gray-50 border rounded-lg p-4">
                            <p class="text-sm text-gray-500">Courier</p>
                            <p id="viewOrderCourier" class="text-lg font-semibold text-gray-800"></p>
                        </div>

                        <div class="bg-gray-50 border rounded-lg p-4">
                            <p class="text-sm text-gray-500">Total</p>
                            <p id="viewOrderTotal" class="text-lg font-bold"></p>
                        </div>
                    </div>

                    <div class="bg-gray-50 border rounded-lg p-4">
                        <h4 class="text-lg font-bold text-purple-800 mb-4">Ordered Items</h4>

                        <div class="overflow-y-auto max-h-52 rounded-xl border bg-white">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-purple-50 text-gray-600 uppercase text-xs sticky top-0">
                                    <tr>
                                        <th class="px-4 py-3">Product</th>
                                        <th class="px-4 py-3 text-center">Qty</th>
                                        <th class="px-4 py-3 text-right">Price</th>
                                        <th class="px-4 py-3 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="viewOrderItems"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end p-6 border-t">
                    <button
                        type="button"
                        onclick="closeViewOrderModal()"
                        class="border px-4 py-2 rounded-lg hover:bg-gray-100 transition">Close
                    </button>
                </div>
            </div>
        </div>

        <!-- edit order popup -->

        <div id="editOrderModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">

                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-purple-800">Edit Order</h3>
                    <button onclick="closeEditOrderModal()" class="text-gray-500 hover:text-red-500 text-3xl leading-none">
                        &times;
                    </button>
                </div>

                <form id="editOrderForm" method="POST" class="space-y-4">

                    <input type="hidden" id="editOrderId" name="EditId">

                    <div>
                        <label class="text-sm text-gray-500">Customer</label>
                        <p id="editOrderCustomer" class="text-lg font-semibold text-gray-800"></p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-500">Status</label>
                        <select id="editOrderStatus" name="EditStatus" class="w-full mt-1 bg-gray-50 border rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="preparing">Preparing</option>
                            <option value="ready_for_delivery">Ready for Delivery</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="submit" name="EditBtn" onclick="saveOrderChanges()" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                            Save
                        </button>
                        <button type="button" onclick="closeEditOrderModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-100">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <script src="manager_js/orders.js"></script>
        
</body>
</html>