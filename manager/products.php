<?php
include "../session.php";
include "role.php";
require_once "../config/db.php";

    $manager_id = $_SESSION['temp_user_id'];

    $sql = "SELECT * from shops1 where manager_id = '$manager_id'";
    $shop_result = mysqli_query($conn, $sql);
    $shop = mysqli_fetch_assoc($shop_result);
    $shop_id = $shop['id'];

    $sql = "SELECT * from product_categories where shop_id = '$shop_id'";
    $category_result = mysqli_query($conn, $sql);
    $categories = [];
    while($row = mysqli_fetch_assoc($category_result)){
        $categories[] = $row;
    }

    if(isset($_POST['AddCategory'])) { // add category
        $name = $_POST['CategoryName'];
        $status = $_POST['CategoryStatus'];
        $sql_check = "SELECT count(*) as count from product_categories where LOWER(name) = LOWER('$name') and shop_id = '$shop_id'";
        $result = mysqli_query($conn, $sql_check);
        $row = mysqli_fetch_assoc($result);

        if($row['count'] > 0) {
            $error ="Category exists!";
            header("Location: products.php");
            exit();
        } else {
            $sql = "INSERT into product_categories(shop_id, name, status)
            values ('$shop_id', '$name', '$status')";
            mysqli_query($conn, $sql);
            header("Location: products.php");
            exit();
        }
    }

    if(isset($_POST['EditCategoryBtn'])) { // edit category
        $id = $_POST['EditCategoryId'];
        $name = $_POST['EditCategoryName'];
        $status = $_POST['EditCategoryStatus'];

        $sql = "UPDATE product_categories set name = '$name', status = '$status' where id = '$id' and shop_id = '$shop_id'";
        mysqli_query($conn, $sql);
        header("Location: products.php");
        exit();
    }

    if(isset($_POST['DeleteCategoryBtn'])) { // delete category
        $id = $_POST['DeleteCategoryId'];

        $sql = "DELETE from product_categories where id = '$id' and shop_id = '$shop_id'";
        mysqli_query($conn, $sql);
        header("Location: products.php");
        exit();
    }

    if(isset($_GET['SearchBtn'])) {
        $search = $_GET['SearchProduct'];
        $filterCategory = $_GET['FilterCategory'];
        $filterStatus = $_GET['FilterStatus'];

        $sql = "SELECT p.*, c.name as category_name 
        FROM products p
        left join product_categories c on p.category_id = c.id
        where 1=1";

        if(!empty($search)) {
            $sql .= " AND p.name like '%$search%'";
        }
        if(!empty($filterCategory)) {
            $sql .= " AND p.category_id = '$filterCategory'";
        }
        if(!empty($filterStatus)) {
            $sql .= " AND p.status = '$filterStatus'";
        }

        $sql .= " ORDER BY p.id ASC";

        $product_result = mysqli_query($conn, $sql);
        $products = [];
        while($row = mysqli_fetch_assoc($product_result)) {
            $sql = "SELECT image from product_images where product_id = '$row[id]' order by id ASC";
            $image_result = mysqli_query($conn, $sql);
            $images = [];
            while($img = mysqli_fetch_assoc($image_result)) {
                $images[] = $img['image'];
            }
            $row['images'] = $images;
            $products[] = $row;
        }
       
    } else {
        $sql = "SELECT p.*, c.name as category_name 
        from products p
        left join product_categories c ON p.category_id = c.id
        where p.shop_id = '$shop_id'
        order by p.id ASC";

        $product_result = mysqli_query($conn, $sql);
        $products = [];
        while($row = mysqli_fetch_assoc($product_result)) {
            $sql = "SELECT image from product_images where product_id = '$row[id]' order by id ASC";
            $image_result = mysqli_query($conn, $sql);
            $images = [];
            while($img = mysqli_fetch_assoc($image_result)) {
                $images[] = $img['image'];
            }
            $row['images'] = $images;
            $products[] = $row;
        }
    }

   


    if(isset($_POST['AddProductBtn'])) { // add product
        $name = $_POST['ProductName'];
        $price = $_POST['ProductPrice'];
        $stock = $_POST['ProductQuantity'];
        $status = 'active';
        $category = $_POST['ProductCategory'];
        $sql = "INSERT into products(shop_id, category_id, name, price, stock, status) VALUES ('$shop_id', '$category', '$name', '$price', '$stock', '$status')";
        if(mysqli_query($conn, $sql)) {
            $product_id = mysqli_insert_id($conn);

            if(isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $pictures = $_FILES['images'];

                for($i = 0; $i < count($_FILES['images']['name']); $i++) {
                    $file_name = $pictures['name'][$i];
                    $file_path = $pictures['tmp_name'][$i];

                    $destination = "../images/".time()."_".$file_name; // unique name because of time()

                    if(move_uploaded_file($file_path, $destination)) {
                        $sql = "INSERT INTO product_images(product_id, image) VALUES ('$product_id', '$destination')";
                        mysqli_query($conn, $sql);
                    }
                }
            }
        }
        header("Location: products.php");
        exit();
    }

    if(isset($_POST['EditProductBtn'])) { //edit product
        $id = $_POST['EditProductId'];
        $name = $_POST['EditProductName'];
        $price = $_POST['EditProductPrice'];
        $stock = $_POST['EditProductStock'];
        $category_id = $_POST['EditProductCategory'];
        $status = $_POST['EditProductStatus'];

        $sql = "UPDATE products SET category_id = '$category_id', name = '$name', price = '$price', stock = '$stock', status = '$status' where id = '$id' and shop_id = '$shop_id'";

        if(mysqli_query($conn, $sql)) {
            if(isset($_FILES['EditProductImage']) && !empty($_FILES['EditProductImage']['name'][0])) {
                $pictures_edit = $_FILES['EditProductImage'];
                
                for($i = 0; $i < count($_FILES['EditProductImage']['name']); $i++) {
                    if($pictures_edit['error'][$i] == 0) {
                        $file_name = $pictures_edit['name'][$i];
                        $file_path = $pictures_edit['tmp_name'][$i];
                        $destination = "../images/".time()."_".$file_name;

                        if(move_uploaded_file($file_path, $destination)) {
                            $sql = "INSERT INTO product_images(product_id, image) VALUES ('$id', '$destination')";
                            mysqli_query($conn, $sql);
                        }
                    }
                }
            }
        }

        header("Location: products.php");
        exit();
    }

    if(isset($_POST['DeleteProductBtn'])) { // delete product
        $prod_id = $_POST['ProductId'];
        $sql = "DELETE from products where id = '$prod_id' and shop_id = '$shop_id'";

        mysqli_query($conn, $sql);
        header("Location: products.php");
        exit();
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Products - Digital Mall</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
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
                <a href="index.php" class="block px-2.5 py-2.5 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Dashboard</a>
                <a href="my-shop.php" class="block px-2.5 py-2.5 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">My Shop Info</a>
                <a href="products.php" class="block px-2.5 py-2.5 rounded-lg bg-purple-700 border-l-4 border-yellow-300 font-semibold">Products</a>
                <a href="orders.php" class="block px-2.5 py-2.5 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Orders</a>
                <a href="deliveries.php" class="block px-2.5 py-2.5 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Deliveries</a>
            </nav>

            <div class="p-4 border-purple-700">
                <a href="../logout.php" class="block px-2.5 py-2.5 rounded-lg bg-red-500 hover:bg-red-600 transition text-center font-semibold">Logout</a>
            </div>
        </aside>

        <main class="flex-1 min w-0">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-3xl font-extrabold text-purple-800">Products</h2>
                    <p class="text-gray-600 mt-1">Manage products for Shop</p>
                </div>
            </div>

            
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <section class="xl:col-span-3 bg-white rounded-2xl shadow p-6">
                    <div class="flex justify-between items-center gap-4 mb-4">
                        <form action="" method="GET">
                            <input type="text" name="SearchProduct" class="border-2 rounded-lg px-4 py-2 w-44 focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Search product...">
                            <select name="FilterCategory" id="" class="border-2 rounded-lg px-4 py-2 w-40 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="">All categories</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']?>"><?php echo $category['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            
                            <select name="FilterStatus" id="" class="border-2 rounded-lg px-4 py-2 w-40 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="">All statuses</option>
                                <option value="active">Active</option>
                                <option value="low_stock">Low Stock</option>
                                <option value="out_of_stock">Out of Stock</option>
                                <option value="blocked">Blocked</option>
                            </select>

                            <button type="submit" name="SearchBtn" class="bg-purple-500 text-white ml-3 px-6 py-2 rounded-lg hover:bg-purple-600 transition">Search</button>
                        </form>
                            
                         <div class="flex items-center gap-3">
                            <button onclick="viewCategoryPopup()" type="button" class="bg-blue-500 text-white px-3 py-2 rounded-lg hover:bg-blue-600 transition font-semibold">Categories</button>
                            <button onclick="addProductPopup()" type="button" class="bg-green-500 text-white px-3 py-2 rounded-lg hover:bg-green-600 transition font-semibold">+ Product</button>
                        </div>
                    </div>
                    <!-- product cards -->
                    <div class="overflow-hidden rounded-xl border">
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 p-3">
                            <?php foreach($products as $product): ?>
                            <div class="bg-white rounded-2xl border-2 shadow p-4 hover:shadow-lg hover:bg-yellow-50 transition">
                                    <div id="carousel-<?php echo $product['id']?>" class="carousel slide w-full h-40 mb-4 rounded-xl overflow-hidden" data-bs-ride="carousel">
                                        <div class="carousel-inner h-full">
                                            <?php if(empty($product['images'])): ?>
                                                <div class="carousel-item active h-full">
                                                    <img src="https://placehold.co/400x400?text=No+Image" class="w-full h-full object-cover" alt="No image">
                                                </div>
                                            <?php else: ?>
                                                <?php foreach($product['images'] as $key => $image): ?>
                                                    <div class="carousel-item <?php echo $key === 0 ? 'active': ''; ?> h-full">
                                                        <img src="<?php echo $image ?>" class="w-full h-full object-cover" alt="Product image">
                                                    </div>
                                                <?php endforeach;?>
                                                
                                            <?php endif;?>
                                        </div>

                                        <?php if(count($product['images']) > 1): ?>
                                            <button class="carousel-control-prev" type="button" data-bs-target="#carousel-<?php echo $product['id']; ?>" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon bg-black/50 rounded-full p-2" aria-hidden="true"></span>
                                                <span class="visually-hidden">Previous</span>
                                            </button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#carousel-<?php echo $product['id']; ?>" data-bs-slide="next">
                                                <span class="carousel-control-next-icon bg-black/50 rounded-full p-2" aria-hidden="true"></span>
                                                <span class="visually-hidden">Next</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="text-lg font-bold text-purple-800"><?php echo $product['name']?></h3>
                                        <?php if($product['status'] == 'active'): ?>
                                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs">Active</span>
                                        <?php elseif($product['status'] == 'low_stock'): ?>
                                            <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs">Low stock</span>
                                        <?php elseif($product['status'] == 'out_of_stock'): ?>
                                            <span class="bg-orange-100 text-orange-700 px-2 py-1 rounded-full text-xs">Out of stock</span>
                                        <?php elseif($product['status'] == 'blocked'): ?>
                                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs">Blocked</span>
                                        <?php endif;?>
                                    </div>

                                    <?php
                                    $category_name_for_status = $product['category_name'];
                                    $sql = "SELECT status from product_categories where name = '$category_name_for_status' and shop_id = '$shop_id'";
                                    $status_result = mysqli_query($conn, $sql);
                                    $status = mysqli_fetch_assoc($status_result);
                                    ?>
                                    <?php if($status['status'] == 'active'): ?>
                                    <span class="text-sm text-green-700 mb-2 px-2 py-1 bg-green-100 rounded-full"><?php echo $product['category_name']?></span>
                                    <?php elseif($status['status'] =='blocked'): ?>
                                    <span class="text-sm text-red-700 mb-2 px-2 py-1 bg-red-100 rounded-full"><?php echo $product['category_name']?></span>
                                    <?php endif;?>

                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Stock: <?php echo $product['stock']?></span>
                                        <span class="text-gray-900 font-semibold">$<?php echo $product['price']?></span>
                                    </div>

                                    <div class="flex justify-between gap-16 mt-3">
                                        <button class="flex-1 bg-yellow-400 text-white py-1 rounded-lg text-sm hover:bg-yellow-500 transition"
                                        onclick="editProductPopup(<?php echo $product['id']?>, '<?php echo $product['name']?>','<?php echo $product['category_id']?>','<?php echo $product['price']?>', <?php echo $product['stock']?>, '<?php echo $product['status']?>')">
                                            ✏️
                                        </button>

                                        <button class="flex-1 bg-red-500 text-white py-1 rounded-lg text-sm hover:bg-red-600 transition"
                                        onclick="deleteProductPopup(<?php echo $product['id'] ?> , '<?php echo $product['name']?>')">
                                            🗑
                                        </button>
                                    </div>
                            </div>
                            <?php endforeach;?>
                        </div>
                    </div>
                </section>
            </div>
        </main>
        <!-- View categories popup -->
        <div id="categoriesPopup"class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 relative">

                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-purple-800">Product Categories</h3>
                    <button type="button" onclick="closeCategoriesPopup()" class="text-gray-500 text-3xl hover:text-red-500">&times;</button>
                </div>

                <div class="space-y-3 max-h-[400px] overflow-y-auto pr-2">
                    <?php foreach($categories as $category): ?>
                        <div class="flex items-center justify-between border rounded-xl p-4 hover:bg-yellow-50 transition">
                            <div>
                                <p class="font-semibold text-purple-700"><?php echo $category['name'] ?></p>
                                <?php if(strtolower($category['status']) == 'active'): ?>
                                    <span class="text-green-600 text-xs">Active</span>
                                <?php else: ?>
                                    <span class="text-red-600 text-xs">Blocked</span>
                                <?php endif;?>
                            </div>
                            <div class="gap-2">
                                <button onclick="editCategoryPopup(<?php echo $category['id']?>, '<?php echo $category['name']?>','<?php echo $category['status']?>')" type="button" class="bg-yellow-400 text-white px-6 py-1 rounded text-xs hover:bg-yellow-500 transition">Edit</button>
                                <button onclick="deleteCategoryPopup(<?php echo $category['id'] ?>, '<?php echo $category['name']?>')" type="button" class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600 transition">Delete</button>
                            </div>
                        </div>
                    <?php endforeach;?>
                </div>

                <div class="flex justify-between mt-6">
                    <button onclick="addCategoryPopup()" type="button" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition">Add Category</button>
                    <button onclick="closeCategoriesPopup()" type="button" class="border px-4 py-2 rounded-lg hover:bg-gray-100 transition">Close</button>
                </div>
            </div>
        </div>

        <!-- edit category popup -->

        <div id="editCategoryModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-purple-800">Edit Category</h3>
                    <button type="button" onclick="closeEditCategoryPopup()" class="text-gray-500 text-3xl hover:text-red-500">&times;</button>
                </div>

                <form method="POST">
                    <input type="hidden" name="EditCategoryId" id="editCategoryId">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category Name</label>
                        <input type="text" name="EditCategoryName" id="editCategoryName" class="w-full border rounded-lg bg-gray-50 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="editCategoryStatus" name="EditCategoryStatus" class="w-full border bg-gray-50 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="active">Active</option>
                            <option value="blocked">Blocked</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="submit" name="EditCategoryBtn" class="px-4 py-2 rounded-lg bg-green-500 text-white text-sm hover:bg-green-600 transition">
                            Save Changes
                        </button>
                        <button type="button" onclick="closeEditCategoryPopup()" class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-100">
                            Cancel
                        </button>
                        
                    </div>
                </form>
            </div>
        </div>

        <!-- delete category popup -->
        
        <div id="deleteCategoryModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-purple-700">Delete Category</h3>
                    <button type="button" onclick="closeDeleteCategoryPopup()" class="text-gray-500 text-3xl hover:text-red-500">&times;</button>
                </div>

                <form method="POST">
                    <input type="hidden" name="DeleteCategoryId" id="deleteCategoryId">

                    <p class="text-sm text-gray-700 mb-6">
                        Are you sure you want to delete
                        <span id="deleteCategoryName" class="font-semibold text-purple-700"></span>?
                    </p>

                    <div class="flex justify-end gap-2">
                        <button type="submit" name="DeleteCategoryBtn" class="px-4 py-2 rounded-lg bg-red-500 text-white text-sm hover:bg-red-600 transition">
                            Delete
                        </button>
                        <button type="button" onclick="closeDeleteCategoryPopup()" class="px-4 py-2 rounded-lg border text-sm">
                            Cancel
                        </button>
                        
                    </div>
                </form>
            </div>
        </div>
        <!-- add category popup -->

        <div id="addCategoryModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 relative">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-purple-800">Add Category</h3>
                    <button type="button" onclick="closeAddCategoryPopup()" class="text-gray-500 text-3xl hover:text-red-500">&times;</button>
                </div>

                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="addCategoryName" class="block text-sm font-medium text-gray-700 mb-2">
                            Category Name
                        </label>
                        <input type="text" id="addCategoryName" name="CategoryName" placeholder="Enter category name" class="w-full border bg-gray-50 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div class="mb-6">
                        <label for="addCategoryStatus" class="block text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select id="addCategoryStatus" name="CategoryStatus" class="w-full border rounded-lg bg-gray-50 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="active">Active</option>
                            <option value="blocked">Blocked</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="submit" name="AddCategory" class="px-4 py-2 rounded-lg bg-green-500 text-white hover:bg-green-600 transition">
                            Confirm
                        </button>
                        <button type="button" onclick="closeAddCategoryPopup()" class="px-4 py-2 rounded-lg border text-gray-700 hover:bg-gray-100 transition">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- add product popup -->

        <div id="addProductModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">

                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-purple-800">Add Product</h3>
                    <button type="button" onclick="closeAddProductPopup()" class="text-gray-500 text-3xl hover:text-red-500">&times;</button>
                </div>

                <form method="POST" action="" enctype="multipart/form-data">

                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Product Name</label>
                        <input type="text" name="ProductName" class="w-full border bg-gray-50 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Price ($)</label>
                            <input type="float" name="ProductPrice"
                                class="w-full border bg-gray-50 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Quantity</label>
                            <input type="number" name="ProductQuantity"
                                class="w-full border rounded-lg bg-gray-50 px-3 py-2 focus:ring-2 focus:ring-purple-500">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Category</label>
                        <select name="ProductCategory" class="w-full border bg-gray-50 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500">
                            <?php foreach($categories as $category): ?>
                                <?php if($category['status'] == 'active'): ?>
                                <option value="<?php echo $category['id'] ?>">
                                    <?php echo $category['name'] ?>
                                </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium mb-1">Product Image</label>
                        <input type="file" name="images[]" multiple class="w-full text-sm">
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="submit" name="AddProductBtn" class="px-4 py-2 bg-green-500 text-white rounded-lg text-sm hover:bg-green-600 transition">
                            Confirm
                        </button>

                        <button type="button" onclick="closeAddProductPopup()" class="px-4 py-2 border rounded-lg text-sm hover:bg-gray-100 transition">
                            Cancel
                        </button> 
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit product popup -->
         <div id="editProductModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-purple-800">Edit Product</h3>
                    <button type="button" onclick="closeEditProductPopup()" class="text-gray-500 text-3xl hover:text-red-500">&times;</button>
                </div>

                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" id="editProductId" name="EditProductId">

                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Product Name</label>
                        <input type="text" id="editProductName" name="EditProductName" 
                            class="w-full border rounded-lg bg-gray-50 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Price ($)</label>
                            <input type="number" step="0.01" id="editProductPrice" name="EditProductPrice"
                                class="w-full border rounded-lg bg-gray-50 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Stock</label>
                            <input type="number" id="editProductStock" name="EditProductStock"
                                class="w-full border rounded-lg bg-gray-50 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Category</label>
                            <select id="editProductCategory" name="EditProductCategory"
                                class="w-full border rounded-lg bg-gray-50 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']?>">
                                        <?php echo $category['name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Status</label>
                            <select id="editProductStatus" name="EditProductStatus"
                                class="w-full border rounded-lg bg-gray-50 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="active">Active</option>
                                <option value="low_stock">Low Stock</option>
                                <option value="out_of_stock">Out of Stock</option>
                                <option value="blocked">Blocked</option>
                            </select>
                        </div>

                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium mb-1">New Product Image</label>
                        <input type="file" name="EditProductImage[]" class="w-full text-sm" multiple>
                        <p class="text-xs text-gray-500 mt-1">Leave empty if you do not want to change the image.</p>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="submit" name="EditProductBtn" class="px-4 py-2 bg-green-500 text-white rounded-lg text-sm hover:bg-green-600 transition">
                            Save Changes
                        </button>
                        <button type="button" onclick="closeEditProductPopup()" class="px-4 py-2 border rounded-lg text-sm">
                            Cancel
                        </button>
                        
                    </div>
                </form>
            </div>
        </div>

        <!-- delete product popup -->

        <div id="deleteProductModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-purple-800">Delete Product</h3>
                    <button type="button" onclick="closeDeleteProductPopup()" class="text-gray-500 text-3xl hover:text-red-500">&times;</button>
                </div>

                <form method="POST" action="">
                    <input type="hidden" id="deleteProductId" name="ProductId">

                    <p class="text-sm text-gray-700 mb-6">
                        Are you sure you want to delete
                        <span id="deleteProductName" class="font-semibold text-purple-700"></span>?
                    </p>

                    <div class="flex justify-end gap-3">
                        <button type="submit" name="DeleteProductBtn" class="px-4 py-2 bg-red-500 text-white rounded-lg text-sm hover:bg-red-600 transition">
                            Delete
                        </button>
                        <button type="button" onclick="closeDeleteProductPopup()" class="px-4 py-2 border rounded-lg text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <script src="manager_js/products.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>