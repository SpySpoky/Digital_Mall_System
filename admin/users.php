<?php
include "../session.php";
include "role.php";
require_once "../config/db.php";
$error = "";
$success = "";

$user_id = $_SESSION['temp_user_id'];

    if(isset($_GET['Search'])) {
        $search = $_GET['SearchUser'];
        $filter = $_GET['FilterRole'];

        $sql = "SELECT * from users1 where 1=1";

        if(!empty($search)) {
            $sql .= " AND (name like '%$search%' or surname like '%$search%' or email like '%$search%' or phone like '%$search%')";
        }

        if(!empty($filter)) {
            $sql .= " AND role = '$filter'";
        }

        $sql .= " ORDER BY id ASC";

        $result = mysqli_query($conn, $sql);
        $users = [];
        while($row = mysqli_fetch_assoc($result)){
            $users[] = $row;
        }

    } else {
        $sql = "SELECT * from users1";
        $result = mysqli_query($conn, $sql);
        $users = [];
        while($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
    }

    if(isset($_POST['DeleteUserBtn'])) {
        $user_id = $_POST['UserId'];

        $check_user_sql = "SELECT COUNT(*) as order_count FROM orders WHERE customer_id = '$user_id'";
        $result = mysqli_query($conn, $check_user_sql);
        $order_count = mysqli_fetch_assoc($result);

        $check_shop_assigned_sql = "SELECT count(*) as shop_count from shops1 where manager_id = '$user_id'";
        $result = mysqli_query($conn, $check_shop_assigned_sql);
        $shop_data = mysqli_fetch_assoc($result);

        if($order_count['order_count'] > 0) {
            $sql = "UPDATE users1 SET status = 'blocked' WHERE id = '$user_id'";
            mysqli_query($conn, $sql);
            header("Location: users.php");
            exit();
        }

        if($shop_data['shop_count'] > 0) {
            $sql = "UPDATE users1 SET status = 'blocked' WHERE id = '$user_id'";
            mysqli_query($conn, $sql);
            header("Location: users.php");
            exit();
        }
            $sql = "DELETE from users1 where id = '$user_id'";
            mysqli_query($conn, $sql);
            header("Location: users.php");
            exit();
    }

    if(isset($_POST['EditUserBtn'])) {
        $id = $_POST['EditId'];
        $name = $_POST['EditName'];
        $surname = $_POST['EditSurname'];
        $phone = $_POST['EditPhone'];
        $email = $_POST['EditEmail'];
        $role = $_POST['EditRole'];
        $address = $_POST['EditAddress'];
        $status = $_POST['EditStatus'];

        $sql = "SELECT role from users1 where id = '$id'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);

        if($row['role'] == 'manager' && ($role == 'customer' || $role == 'courier')) { 
            $sql = "SELECT count(*) as shop_count from shops1 where manager_id = '$id'";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            if($row['shop_count'] > 0) {
                $error = "You can not change role for that manager";
                header("Location: users.php");
                exit();
            } else {
                $sql = "SELECT email FROM users1 where email = '$email' and id != '$id'";
                $result = mysqli_query($conn, $sql);
                
                if(mysqli_num_rows($result) > 0) {
                    $error = "Email exists!";
                    header("Location: users.php");
                    exit();
                    } else {
                        $sql = "UPDATE users1 SET 
                        name = '$name', surname = '$surname',phone = '$phone', email = '$email', role = '$role', address = '$address', status = '$status'
                        where id = '$id'";
                        mysqli_query($conn, $sql);
                        $success = "User is updated";
                        header("Location: users.php");
                        exit();
                    }
            }

        }

        if($row['role'] == 'courier' && ($role == 'customer' || $role == 'manager')) {
            $sql = "SELECT count(*) as delivery_count from orders where courier_id = '$id'";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            if($row['delivery_count'] > 0) {
                $error = "You can not change role for that courier";
                header("Location: users.php");
                exit();
            } else {
                $sql = "SELECT email FROM users1 where email = '$email' and id != '$id'";
                $result = mysqli_query($conn, $sql);
                
                if(mysqli_num_rows($result) > 0) {
                    $error = "Email exists!";
                    header("Location: users.php");
                    exit();
                    } else {
                        $sql = "UPDATE users1 SET 
                        name = '$name', surname = '$surname',phone = '$phone', email = '$email', role = '$role', address = '$address', status = '$status', shop_id_courier = NULL
                        where id = '$id'";
                        mysqli_query($conn, $sql);
                        $success = "User is updated";
                        header("Location: users.php");
                        exit();
                    }
            }

        }

        if($row['role'] == 'customer' && ($role == 'courier' || $role == 'manager')) {
            $sql = "SELECT email FROM users1 where email = '$email' and id != '$id'";
            $result = mysqli_query($conn, $sql);
                
            if(mysqli_num_rows($result) > 0) {
                $error = "Email exists!";
                header("Location: users.php");
                exit();
            } else {
                        $sql = "UPDATE users1 SET 
                        name = '$name', surname = '$surname',phone = '$phone', email = '$email', role = '$role', address = '$address', status = '$status'
                        where id = '$id'";
                        mysqli_query($conn, $sql);
                        $success = "User is updated";
                        header("Location: users.php");
                        exit();
            }
        }

        $sql = "SELECT email FROM users1 WHERE email = '$email' AND id != '$id'";
        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) > 0) {
            $error = "Email exists!";
            header("Location: users.php");
            exit();
        }

         $sql = "UPDATE users1 SET 
            name = '$name', 
            surname = '$surname',
            phone = '$phone', 
            email = '$email', 
            role = '$role', 
            address = '$address', 
            status = '$status'
            WHERE id = '$id'";
    
            mysqli_query($conn, $sql);
            header("Location: users.php");
            exit();
        
    }

    if(isset($_POST['AddBtn'])) {
        $name = $_POST['addname'];
        $surname = $_POST['addsurname'];
        $email = $_POST['addemail'];
        $role = $_POST['addrole'];
        $address = $_POST['addaddress'];
        $status = $_POST['addstatus'];
        $default_password = password_hash('password', PASSWORD_DEFAULT);
        $phone = $_POST['addphone'];

        $sql = "SELECT id from users1 where email = '$email'";
        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) > 0) {
            $error = "Email already exists!";
            header("Location: users.php");
            exit();
        }

        $sql = "INSERT INTO users1(name, surname, phone, email, role, password, status, address)
        values('$name', '$surname', '$phone', '$email', '$role', '$default_password', '$status', '$address')";
        mysqli_query($conn, $sql);
        header("Location: users.php");
        exit();


    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Users - Digital Mall</title>
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
                <a href="users.php" class="block px-4 py-3 rounded-lg bg-purple-700 border-l-4 border-yellow-300 font-semibold">Users</a>
                <a href="shops.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Shops</a>
                <a href="category.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Categories</a>
                <a href="report.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Reports</a>
            </nav>

            <div class="p-4 border-purple-700">
                <a href="../logout.php" class="block px-4 py-3 rounded-lg bg-red-500 hover:bg-red-600 transition text-center font-semibold">Logout</a>
            </div>
        </aside>

        <section class="bg-white rounded-2xl shadow p-6">
            
            <h3 class="text-xl font-bold text-purple-800 mb-6">User Management</h3>
            <div class="flex mb-4">
                <form action="" method="GET">
                    <input type="text" name="SearchUser" placeholder="Search users..."class="border rounded-lg px-4 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <select name="FilterRole" class="border rounded-lg ml-6 px-4 py-2">
                        <option value="">All roles</option>
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="customer">Customer</option>
                        <option value="courier">Courier</option>
                    </select>
                    <button type="submit" name="Search" class="bg-purple-500 text-white ml-6 px-6 py-2 rounded-lg hover:bg-purple-600 transition">Search</button>
                </form>
                
                <button type="button" onclick="add_user()" class="bg-green-500 text-white px-6 py-2 rounded-lg ml-6 hover:bg-green-600 transition">Add User</button>
            </div>
            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm text-center">
                    <thead class = "text-gray-500 border-b bg-gray-50 tracking-wider">
                        <tr>
                            <th class="px-8 py-4">ID</th>
                            <th class="px-8  py-4">Name</th>
                            <th class="px-8  py-4">Surname</th>
                            <th class="px-8  py-4">Phone</th>
                            <th class="px-8  py-4">Email</th>
                            <th class="px-8 py-4">Role</th>
                            <th class="px-8 py-4">Address</th>
                            <th class="px-8 py-4">Status</th>
                            <th class="px-8 py-4">Registered</th>
                            <th class="px-8 py-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                            <tr class = 'border-b hover:bg-yellow-50 transition-colors'>
                            <td class ='py-3'>#<?php echo $user['id']?></td>
                            <td> <?php echo $user['name']?></td>
                            <td> <?php echo $user['surname']?></td>
                            <td> <?php echo $user['phone']?></td>
                            <td> <?php echo $user['email']?></td>
                            <td> <?php if ($user['role'] === 'admin'): ?>
                                <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded text-xs">Admin</span>
                            <?php elseif($user['role'] === 'manager'): ?>
                                <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs">Manager</span>
                            <?php elseif($user['role'] === 'customer'): ?>
                                <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">Customer</span>
                            <?php else: ?>
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Courier</span>
                            <?php endif; ?>
                            </td>
                            <td><?php echo $user['address']?></td>
                            <td><?php if($user['status'] === 'active'):?>
                                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-medium">Active</span>
                                <?php else: ?>
                                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-medium">Blocked</span>
                                    <?php endif; ?>
                            </td>
                            <td>
                                <?php echo $user['registered_at'] ?>
                            </td>
                            <td class="flex flex-col gap-3">
                                <div class="flex flex-col gap-2 items-center">
                                    
                                    <?php if($user['role'] !== 'admin'): ?>
                                    <button name="" id="" class="bg-yellow-400 text-white px-3 py-1 rounded text-xs hover:bg-yellow-500 transition"
                                    onclick="edit_user(
                                    <?php echo $user['id']?>,
                                    '<?php echo $user['name']?>',
                                    '<?php echo $user['surname']?>',
                                    '<?php echo $user['phone']?>',
                                    '<?php echo $user['email']?>',
                                    '<?php echo $user['role']?>',
                                    '<?php echo $user['address']?>',
                                    '<?php echo $user['status']?>'
                                    )">✏️</button>
                                    <button onclick="delete_user(<?php echo $user['id']?>, '<?php echo $user['name']?>', '<?php echo $user['surname']?>')" class="bg-red-500 text-white px-3 py-1 mb-1 rounded text-xs hover:bg-red-600 transition">🗑</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                            </tr>
                            <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </section>
        <!-- delete user popup -->
        <div id="deletePopup" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-lg">
                <div class="flex items-center justify-between mb-6">
                    <h3 id="showUserId" class="text-xl font-bold text-purple-800"></h3>
                    <button type="button" onclick="closeDeletePopup()" class="text-gray-500 hover:text-black text-xl">✕</button>
                </div>
                <p class="text-gray-600 mb-6">
                    Are you sure you want to delete user: <span id="deleteUserName" class="font-semibold"></span>?
                </p>
                
                <form action="" method="POST" class="flex justify-end gap-3">
                    <input type="hidden" name="UserId" id="deleteUserId">

                    <button type="submit" name="DeleteUserBtn" class="px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600">Delete</button>
                    <button type="button" onclick="closeDeletePopup()" class="px-4 py-2 rounded-lg border">Close</button>
                    
                </form>
            </div>
        </div>
        <!-- Edit user popup -->
        <div id="editPopup" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 relative">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-purple-800">Edit User</h3>
                    <button type="button" onclick="closeEditPopup()" class="text-gray-500 hover:text-black text-xl">✕</button>
                </div>

                <form action="" method="POST" class="space-y-4">
                    <input type="hidden" name="EditId" id="editUserId">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" name="EditName" id="editName" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Surname</label>
                            <input type="text" name="EditSurname" id="editSurname" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input type="tel" name="EditPhone" id="editPhone" pattern="[\+\d\s\-\(\)]{5,20}" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="EditEmail" id="editEmail" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <select name="EditRole" id="editRole" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="manager">Manager</option>
                                <option value="customer">Customer</option>
                                <option value="courier">Courier</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <input type="text" name="EditAddress" id="editAddress" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="EditStatus" id="editStatus" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="active">Active</option>
                                <option value="blocked">Blocked</option>
                            </select>
                        </div>

                    </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button type="submit" name="EditUserBtn" class="px-5 py-2 rounded-lg bg-green-500 text-white hover:bg-green-600 transition">Save Changes</button>
                            <button type="button" onclick="closeEditPopup()" class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 transition">Cancel</button>
                        </div>
                </form>
            </div>
        </div>
        <!-- Add user Popup -->
        <div id="addPopup" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 relative">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-purple-800">Add User</h3>
                    <div class="bg-yellow-100 text-yellow-800 text-xs px-3 py-1 rounded-full">
                        ⚠️ Default password: <strong>password</strong>
                    </div>
                    <button type="button" onclick="closeAddPopup()" class="text-gray-500 hover:text-black text-xl">✕</button>
                </div>

                <form action="" method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" name="addname" id="addName" required class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Surname</label>
                            <input type="text" name="addsurname" required id="addSurname" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input type="tel" name="addphone" required id="addPhone" pattern="[\+\d\s\-\(\)]{5,20}" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="addemail"required id="addEmail" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <select name="addrole" required id="addRole" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="manager">Manager</option>
                                <option value="customer">Customer</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <input type="text" required name="addaddress" id="addAddress" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="addstatus" required id="addStatus" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="active">Active</option>
                                <option value="blocked">Blocked</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="submit" name="AddBtn" class="px-5 py-2 rounded-lg bg-green-500 text-white hover:bg-green-600 transition">Confirm</button>
                        <button type="button" onclick="closeAddPopup()" class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 transition">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        
    <script src="admin_js/users.js"></script>
</body>
</html>