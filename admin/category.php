<?php
include "../session.php";
include "role.php";
require_once "../config/db.php";

$user_id = $_SESSION['temp_user_id'];

    if(isset($_GET['SearchBtn'])) {
        $search = $_GET['Search'];
        $sql = "SELECT c.*,(SELECT count(*) from shops1 where category_id = c.id) as count_shops from shop_category c where 1=1";

        if(!empty($search)) {
            $sql .= " AND c.name LIKE '%$search%' OR c.description LIKE '%$search%'";
        }
        $result = mysqli_query($conn, $sql);
        $categories = [];
        while($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row;
        }

    } else {
        $sql = "SELECT c.*,(SELECT count(*) from shops1 where category_id = c.id) as count_shops from shop_category c";
        $result = mysqli_query($conn, $sql);
        $categories = [];
        while($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row;
        }
    }

    if(isset($_POST['AddCategoryBtn'])) {
        $name = $_POST['CatName'];
        $desc = $_POST['CatDesc'];
        $status = $_POST['CatStatus'];

        $sql = "INSERT into shop_category(name, description, status) VALUES (?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $name, $desc, $status);
        mysqli_stmt_execute($stmt);

        header("Location: category.php");
        exit();       
    }

    if(isset($_POST['EditCategoryBtn'])) {
        $id = $_POST['EditId'];
        $name = $_POST['EditName'];
        $desc = $_POST['EditDesc'];
        $status = $_POST['EditStatus'];

        $sql = "UPDATE shop_category SET name = ?, description = ?, status = ? where id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssi", $name, $desc, $status, $id);
        mysqli_stmt_execute($stmt);
        header("Location: category.php");
        exit();
    }

    if(isset($_POST['DeleteCatBtn'])) {
        $id = $_POST['DeleteId'];

        $check_sql = "SELECT * from shops1 where category_id = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "i", $id);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        if(mysqli_num_rows($result) > 0) {
            $sql = "UPDATE shop_category SET status = 'blocked' where id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
        } else {
            $sql = "DELETE from shop_category where id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
        }

        header("Location: category.php");
        exit();


    }


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Category - Digital Mall</title>
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
                <a href="category.php" class="block px-4 py-3 rounded-lg bg-purple-700 border-l-4 border-yellow-300 font-semibold">Categories</a>
                <a href="report.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Reports</a>
            </nav>

            <div class="p-4 border-purple-700">
                <a href="../logout.php" class="block px-4 py-3 rounded-lg bg-red-500 hover:bg-red-600 transition text-center font-semibold">Logout</a>
            </div>
        </aside>
        <section class="bg-white rounded-2xl shadow p-6">
            <h3 class="text-xl font-bold text-purple-800 mb-6">Category Management</h3>
            <div class="flex flex-wrap items-center gap-4 mb-4">
                <form action="" method="GET">
                    <input type="text" name="Search" placeholder="Search category..." class="border rounded-lg px-4 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <button type="submit" name="SearchBtn" class="bg-purple-500 text-white ml-3 px-6 py-2 rounded-lg hover:bg-purple-600 transition">Search</button>
                </form>
                
                <button type="button" onclick="addCategory()"class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition">Add Category</button>
            </div>

            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm text-center">
                    <thead class = "text-gray-500 border-b bg-gray-50 tracking-wider">
                        <tr>
                            <th class="px-8 py-4">ID</th>
                            <th class="px-8  py-4">Name</th>
                            <th class="px-8  py-4">Description</th>
                            <th class="px-8  py-4">Shops</th>
                            <th class="px-8 py-4">Status</th>
                            <th class="px-8 py-4">Action</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($categories as $category): ?>
                            <tr class="border-b hover:bg-yellow-50 transition-colors">
                                <td class="py-3"><?php echo $category['id'] ?></td>
                                <td class="py-3"><?php echo $category['name'] ?></td>
                                <td class="max-w-[250px] truncate"><?php echo $category['description'] ?></td>
                                <td class="py-3"><?php echo $category['count_shops'] ?></td>
                                <td class="py-3">
                                    <?php if($category['status'] == 'active'):?>
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">Active</span>
                                    <?php elseif($category['status'] == 'blocked'): ?>
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-medium">Blocked</span>
                                    <?php else: ?>
                                    <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-medium">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-8 py-4">
                                    <div class="flex justify-center gap-2">
                                        <button type="button" class="bg-yellow-400 text-white px-2 py-1 rounded text-xs hover:bg-yellow-500 transition"
                                        onclick="editCategory(
                                        <?php echo $category['id']?>,
                                        '<?php echo $category['name']?>',
                                        '<?php echo $category['description']?>',
                                        '<?php echo $category['status']?>'
                                        )">✏️</button>

                                        <button type="button" class="bg-red-500 text-white px-2 py-1 rounded text-xs hover:bg-red-600 transition"
                                        onclick ="deleteCategory(
                                        <?php echo $category['id']?> ,
                                        '<?php echo $category['name']?>'
                                        )">🗑</button>
                                    </div>
                                </td>


                            </tr>
                    </tbody>
                    <?php endforeach; ?>
                </table>
            </div>
        </section>

        <!-- delete category popup -->
        <div id="deletePopup" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-lg">
                <div class="flex items-center justify-between mb-6">
                    <h3 id="showCategoryId" class="text-xl font-bold text-purple-800">Delete Category</h3>
                    <button type="button" onclick="closeDeletePopup()" class="text-gray-500 hover:text-black text-xl">✕</button>
                </div>
                <p class="text-gray-600 mb-6">
                    Are you sure you want to delete category: <span id="deleteCategoryName" class="font-semibold"></span>?
                </p>
                
                <form action="" method="POST" class="flex justify-end gap-3">
                    <input type="hidden" name="DeleteId" id="deleteCategoryId">

                    <button type="submit" name="DeleteCatBtn" class="px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600">Delete</button>
                    <button type="button" onclick="closeDeletePopup()" class="px-4 py-2 rounded-lg border">Close</button>
                    
                </form>
            </div>
        </div>
        <!-- Edit category popup -->
        <div id="editPopup" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 relative">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-purple-800">Edit Category</h3>
                    <button type="button" onclick="closeEditPopup()" class="text-gray-500 hover:text-black text-xl">✕</button>
                </div>

                <form method="POST" class="space-y-4">
                    <input type="hidden" name="EditId" id="editCategoryId">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" name="EditName" id="editCategoryName" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="EditDesc" id="editCategoryDesc" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="EditStatus" id="editCategoryStatus" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="blocked">Blocked</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="submit" name="EditCategoryBtn" class="px-5 py-2 rounded-lg bg-green-500 text-white hover:bg-green-600 transition">Save Changes</button>
                        <button type="button" onclick="closeEditPopup()" class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 transition">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Add category Popup -->
        <div id="addPopup" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 relative">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-purple-800">Add Category</h3>
                    <button type="button" onclick="closeAddPopup()" class="text-gray-500 hover:text-black text-xl">✕</button>
                </div>

                <form action="" method="POST" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                <input type="text" name="CatName" id="addCategoryName" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="CatDesc" id="addCategoryDesc" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="CatStatus" id="addCategoryStatus" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <option value="active">Active</option>
                                    <option value="pending">Pending</option>
                                    <option value="blocked">Blocked</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-4">   
                            <button type="submit" name="AddCategoryBtn" class="px-5 py-2 rounded-lg bg-green-500 text-white hover:bg-green-600 transition">Confirm</button>
                            <button type="button" onclick="closeAddPopup()" class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 transition">Cancel</button>
                        </div> 
                </form>
            </div>
        </div>
    <script src="admin_js/category.js"></script>
        
</body>
</html>