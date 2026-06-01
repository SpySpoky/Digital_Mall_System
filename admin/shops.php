<?php 
include "../session.php";
include "role.php";
require_once "../config/db.php";

$user_id = $_SESSION['temp_user_id'];

$sql = "SELECT * from shop_category where status = 'active'";
$result = mysqli_query($conn, $sql);
$categories = [];
while($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}

if(isset($_GET['SearchBtn'])) {
    $search = $_GET['Search'];
    $filter = $_GET['Filter'];

    $sql = "SELECT s.*, u.name as name, u.surname as surname, c.name as category, (SELECT COUNT(*) FROM products WHERE shop_id = s.id) as products_count
            FROM shops1 s
            LEFT JOIN users1 u ON s.manager_id = u.id
            LEFT JOIN shop_category c ON s.category_id = c.id
            WHERE 1=1";
    
    if(!empty($search)) {
         $sql .= " AND (s.shop_name LIKE '%$search%' 
                   OR u.name LIKE '%$search%' 
                   OR u.surname LIKE '%$search%'
                   OR s.location LIKE '%$search%')";
    }

    if(!empty($filter)) {
        $sql .= " AND c.id = '$filter'";
    }

    $sql .= " ORDER BY s.id ASC";
    $result = mysqli_query($conn, $sql);
    $shops = [];

    while($row = mysqli_fetch_assoc($result)) {
        $shops[] = $row;
    }

} else {
    $sql = "SELECT s.*, u.name as name, u.surname as surname, c.name as category, (SELECT COUNT(*) FROM products WHERE shop_id = s.id) as products_count
    from shops1 s
    left join users1 u on s.manager_id = u.id
    left join shop_category c on s.category_id = c.id";

    $result = mysqli_query($conn, $sql);
    $shops = [];
    while($row = mysqli_fetch_assoc($result)) {
        $shops[] = $row;
}

}




if(isset($_POST['ChangeManagerBtn'])) { // change manager
    $shop_id = $_POST['shop_id'];
    $old_manager_id = $_POST['old_manager_id'];
    $new_manager_id = $_POST['new_manager_id'];

    $check_sql = "SELECT id FROM shops1 WHERE manager_id = '$new_manager_id' AND id != '$shop_id'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if(mysqli_num_rows($check_result) < 1) {
        $sql = "UPDATE shops1 SET manager_id = '$new_manager_id' WHERE id = '$shop_id'";
        mysqli_query($conn, $sql);
    }
    header("Location: shops.php");
    exit();
}

if(isset($_POST['EditShopBtn'])) { // edit shop
    $shop_id = $_POST['EditId'];
    $shop_name = $_POST['EditName'];
    $shop_category_id = $_POST['EditShopCategory'];
    $shop_location = $_POST['EditShopLocation'];
    $shop_status = $_POST['EditShopStatus'];

    $sql = "UPDATE shops1 set shop_name = ?, category_id = ?, location = ?, status = ? where id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sissi", $shop_name, $shop_category_id, $shop_location, $shop_status, $shop_id);
    mysqli_stmt_execute($stmt);

    header("Location: shops.php");
    exit();
}

if(isset($_POST['DeleteShopBtn'])) {
    $shop_id = $_POST['Shop_id'];

    $sql = "UPDATE shops1 SET status = 'blocked' where id = '$shop_id'";
    mysqli_query($conn, $sql);
    header("Location: shops.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Shops - Digital Mall</title>
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
        <aside class="w-64 flex-shrink-0 bg-purple-800 text-white flex flex-col rounded-2xl shadow-xl sticky self-start top-6">
            <div class="p-6 border-b border-purple-700">
                <img src="../images/logo.png" class="w-16 mx-auto mb-2" alt="Logo.png">
                <h1 class="text-2xl font-extrabold tracking-wide">Digital Mall</h1>
                <p class="text-sm text-purple-200 mt-1">Admin Panel</p>
            </div>

            <nav class="flex-1 p-4 space-y-2">
                <a href="index.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Dashboard</a>
                <a href="users.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Users</a>
                <a href="shops.php" class="block px-4 py-3 rounded-lg bg-purple-700 border-l-4 border-yellow-300 font-semibold">Shops</a>
                <a href="category.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Categories</a>
                <a href="report.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Reports</a>
            </nav>

            <div class="p-4 border-purple-700">
                <a href="../logout.php" class="block px-4 py-3 rounded-lg bg-red-500 hover:bg-red-600 transition text-center font-semibold">Logout</a>
            </div>
        </aside>    

        <section class="bg-white rounded-2xl shadow p-6">
            
            <h3 class="text-xl font-bold text-purple-800 mb-6">Shop Management</h3>
            <div class="flex mb-4">
                <form action="" method="GET">
                    <input type="text" name="Search" placeholder="Search shop..."class="border rounded-lg px-4 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <select name="Filter" class="border rounded-lg ml-6 px-4 py-2">
                        <option value="">All categories</option>
                        <?php foreach($categories as $category): ?>
                        <option value="<?php echo $category['id']?>"><?php echo $category['name']?></option>
                        <?php endforeach;?>
                    </select>
                    <button type="submit" name="SearchBtn" class="bg-purple-500 text-white ml-3 px-6 py-2 rounded-lg hover:bg-purple-600 transition">Search</button>
                </form>
                
            </div>
            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm text-center">
                    <thead class = "text-gray-500 border-b bg-gray-50 tracking-wider">
                        <tr>
                            <th class="px-8 py-4">ID</th>
                            <th class="px-8  py-4">Name</th>
                            <th class="px-8  py-4">Manager</th>
                            <th class="px-8  py-4">Category</th>
                            <th class="px-8 py-4">Location</th>
                            <th class="px-8 py-4">Products</th>
                            <th class="px-8 py-4">Status</th>
                            <th class="px-8 py-4">Created</th>
                            <th class="px-8 py-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($shops as $shop): ?>
                            <tr class = 'border-b hover:bg-yellow-50 transition-colors'>
                            <td><?php echo $shop['id']?></td>
                            <td class='px-6 py-4'> <?php echo $shop['shop_name']?></td>
                            <td> <?php echo $shop['name']. " ". $shop['surname']?></td>
                            <td> <?php echo $shop['category']?></td>
                            <td> <?php echo $shop['location'] ?></td>
                            <td><?php echo $shop['products_count']?></td>
                            <td><?php if($shop['status'] == 'active'): ?>
                                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs">Active</span>
                                <?php elseif($shop['status'] == 'blocked'): ?>
                                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs">Blocked</span>
                                <?php elseif($shop['status'] == 'temporary_closed'): ?>
                                    <span class="bg-orange-100 text-orange-700 px-2 py-1 rounded-full text-xs">Temp Closed</span>
                                <?php else: ?>
                                    <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo $shop['created_at'] ?>
                            </td>
                            <td class="flex flex-col gap-3">
                                <div class="flex flex-col gap-2 items-center">
                                <!-- <a href="#" class="bg-blue-500 text-white px-3 py-1 mt-1 rounded text-xs hover:bg-blue-600 transition">👁</a> -->
                                <button type="button" class="bg-yellow-400 text-white px-3 py-1 rounded text-xs hover:bg-yellow-500 transition"
                                    onclick='editShop(
                                        <?php echo $shop["id"]; ?>,
                                        <?php echo json_encode($shop["shop_name"]); ?>,
                                        <?php echo $shop["manager_id"]; ?>,
                                        <?php echo json_encode($shop["name"]) ?>,
                                        <?php echo json_encode($shop["surname"]) ?>,
                                        <?php echo $shop["category_id"]; ?>,
                                        <?php echo json_encode($shop["location"]); ?>,
                                        <?php echo json_encode($shop["status"]); ?>,
                                    )'>✏️</button>
                                <button type="button" class="bg-red-500 text-white px-3 py-1 mb-1 rounded text-xs hover:bg-red-600 transition"
                                onclick="deleteShop(
                                <?php echo $shop['id'] ?>,
                                '<?php echo $shop['shop_name']?>',
                                )">🗑</button>
                                </div>
                            </td>
                            </tr>
                            <?php ?>
                            <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- delete shop popup -->
        <div id="deletePopup" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-lg">
                <div class="flex items-center justify-between mb-6">
                    <h3 id="showShopId" class="text-xl font-bold text-purple-800">Delete Shop:</h3>
                    <button type="button" onclick="closeDeletePopup()" class="text-gray-500 hover:text-black text-xl">✕</button>
                </div>
                <p class="text-gray-600 mb-6">
                    Are you sure you want to blocked shop: <span id="deleteShopName" class="font-semibold"></span>?
                </p>
                
                <form action="" method="POST" class="flex justify-end gap-3">
                    <input type="hidden" name="Shop_id" id="deleteShopId">

                    <button type="submit" name="DeleteShopBtn" class="px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600">Delete</button>
                    <button onclick="closeDeletePopup()" class="px-4 py-2 rounded-lg border">Close</button>
                    
                </form>
            </div>
        </div>
        <!-- Edit shop popup -->
        <div id="editPopup" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 relative">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-purple-800">Edit Shop</h3>
                    <button type="button" onclick="closeEditPopup()" class="text-gray-500 hover:text-black text-xl">✕</button>
                </div>

                <form action="" method="POST" class="space-y-4">
                    <input type="hidden" name="EditId" id="editShopId">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="EditName" id="editShopName" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Manager</label>
                                <input type="text" name="" id="editShopOwner" readonly class="w-full border rounded-lg px-4 py-2 bg-gray-100">
                            </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select name="EditShopCategory" id="editShopCategory" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <?php foreach($categories as $category): ?>
                                <option value="<?php echo $category['id']?>"><?php echo $category['name']?></option>
                                <?php endforeach;?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                            <input type="text" name="EditShopLocation" id="editShopLocation" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="EditShopStatus" id="editShopStatus" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="active">Active</option>
                                <option value="blocked">Blocked</option>
                                <option value="pending">Pending</option>
                                <option value="temporary_closed">Temp Closed</option>
                            </select>
                        </div>
                    </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" onclick="openChangeManagerPopup()" class="px-5 py-2 rounded-lg bg-purple-500 text-white hover:bg-purple-600 transition">
                                Change Manager
                            </button>
                            <button type="submit" name="EditShopBtn" class="px-5 py-2 rounded-lg bg-green-500 text-white hover:bg-green-600 transition">
                                Save Changes
                            </button>
                            <button type="button" onclick="closeEditPopup()" class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 transition">
                                Cancel
                            </button>
                        </div>
                    
                </form>
            </div>
        </div>

        <div id="changeManagerPopup" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[60] p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 relative">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-purple-800">Change Manager</h3>
                    <button type="button" onclick="closeChangeManagerPopup()" class="text-gray-500 hover:text-black text-xl">✕</button>
                </div>

                <form method="POST" class="space-y-4">
                    <input type="hidden" name="shop_id" id="changeShopId">
                    <input type="hidden" name="old_manager_id" id="changeOldManagerId">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Manager</label>
                        <input type="text" id="currentManagerName" readonly 
                            class="w-full border rounded-lg px-4 py-2 bg-gray-100">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Manager</label>
                        <select name="new_manager_id" id="newManagerSelect" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500" required>
                            <option value="">--Select New Manager--</option>
                            <?php 
                            $sql_free = "SELECT u.id, u.name, u.surname 
                                        FROM users1 u
                                        WHERE u.role = 'manager' 
                                        AND u.id NOT IN (SELECT manager_id FROM shops1 WHERE manager_id IS NOT NULL)
                                        ORDER BY u.name";
                            $result_free = mysqli_query($conn, $sql_free);
                            while($manager = mysqli_fetch_assoc($result_free)): 
                            ?>
                                <option value="<?php echo $manager['id']; ?>">
                                    <?php echo $manager['name'] . ' ' . $manager['surname']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Only available managers (without shops) are shown</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="submit" name="ChangeManagerBtn" class="px-5 py-2 rounded-lg bg-green-500 text-white hover:bg-green-600 transition">
                            Confirm Change
                        </button>
                        <button type="button" onclick="closeChangeManagerPopup()" class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 transition">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
            
    <script src="admin_js/shops.js"></script>
</body>
</html>