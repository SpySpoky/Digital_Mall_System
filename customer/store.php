<?php 
require_once "../config/db.php";
include "../session.php";
include "role.php";

    $user_id = $_SESSION['temp_user_id'];
    $shop_id = isset($_GET['shop_id']) ? (int)$_GET['shop_id'] : 0;
    if($shop_id == 0) {
        header("Location: index.php");
        exit();
    }

    $sql = "SELECT s.*, c.name as category_name 
    from shops1 s
    left join shop_category c on s.category_id = c.id
    where s.id = '$shop_id' and s.status not in ('blocked', 'pending')";

    $result = mysqli_query($conn, $sql);
    $shop = mysqli_fetch_assoc($result);

    if(!$shop) {
        header("Location: index.php");
        exit();
    }

    $sql = "SELECT p.*, c.name as category_name 
    from products p
    left join product_categories c ON p.category_id = c.id
    where p.shop_id = '$shop_id' AND p.status != 'blocked'
    order by p.id ASC";
    $result = mysqli_query($conn, $sql);
    $products = [];
    while($row = mysqli_fetch_assoc($result)) {
        $sql = "SELECT image from product_images where product_id = '$row[id]' order by id ASC";
        $image_result = mysqli_query($conn, $sql);
        $images = [];
        while($img = mysqli_fetch_assoc($image_result)) {
            $images[] = $img['image'];
        }
        $row['images'] = $images;
        $products[] = $row;
    }


    $sql = "SELECT * from product_categories where shop_id = '$shop_id' AND status = 'active'";
    $result = mysqli_query($conn, $sql);
    $categories = [];
    while($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }

    if(isset($_GET['SearchBtn'])) { //finish it
        $search = $_GET['Search'];
        $filter = $_GET['Filter'];

        $sql = "SELECT p.*, c.name as category_name 
        FROM products p
        LEFT JOIN product_categories c ON p.category_id = c.id
        WHERE p.shop_id = '$shop_id' AND p.status != 'blocked'";

        if(!empty($search)) {
            $sql .= " AND (p.name LIKE '%$search%')";
        }

        if(!empty($filter)) {
            $sql .= " AND p.category_id = '$filter'";
        }

        $sql .= " ORDER BY p.id ASC";
        $result = mysqli_query($conn, $sql);
        $products = [];
        while($row = mysqli_fetch_assoc($result)) {
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

    if(isset($_POST['AddToCartBtn'])) {
        $product_id = $_POST['product_id'];
        $quantity = $_POST['quantity'];
        $old_stock = $_POST['old_stock'];
        if($quantity > $old_stock) {
            $quantity = $old_stock;
        }

        $check_sql = "SELECT id FROM cart WHERE user_id = '$user_id' AND product_id = '$product_id'";
        $check_result = mysqli_query($conn, $check_sql);
        if(mysqli_num_rows($check_result) > 0) {
            $sql = "SELECT quantity FROM cart WHERE user_id = '$user_id' AND product_id = '$product_id'";
            $result = mysqli_query($conn, $sql);
            $prev_quantity = mysqli_fetch_assoc($result);
            if(($prev_quantity['quantity'] + $quantity) > $old_stock) {
                $error = "Invalid quantity for that product";
            } else {
                $sql = "UPDATE cart SET quantity = quantity + $quantity WHERE user_id = '$user_id' AND product_id = '$product_id'";
            }
        } else {
            $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES ('$user_id', '$product_id', '$quantity')";
        }
        mysqli_query($conn, $sql);
        header("Location: store.php?shop_id=$shop_id");
        exit();
    }

    $sql = "SELECT avg(rating) as rating from rating where shop_id = '$shop_id'";
    $result = mysqli_query($conn, $sql);
    $rating = mysqli_fetch_assoc($result);

    $check_rating = "SELECT rating from rating where customer_id = '$user_id' AND shop_id = '$shop_id'";
    $result = mysqli_query($conn, $check_rating);
    $u_rating = mysqli_fetch_assoc($result);
    $rated = $u_rating ? true: false;
    $user_rating = $u_rating['rating'] ?? 0;

    if(isset($_POST['SubmitRatingBtn'])) {
        $new_rating = $_POST['rating'];
        if($new_rating >= 1 && $new_rating <= 5) {
            $sql = "INSERT into rating (shop_id, customer_id, rating) VALUES ('$shop_id', '$user_id', '$new_rating')";
            mysqli_query($conn, $sql);
        }
        header("Location: store.php?shop_id=$shop_id");
        exit();
    }


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $shop['shop_name']?> - Digital Mall</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body class="bg-gradient-to-br from-yellow-100 via-yellow-200 to-purple-200 min-h-screen p-6">

    <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
        <div class="absolute w-96 h-96 bg-purple-300 rounded-full blur-3xl opacity-40 top-10 left-10"></div>
        <div class="absolute w-96 h-96 bg-yellow-300 rounded-full blur-3xl opacity-40 bottom-10 right-10"></div>
    </div>

    <nav class="bg-white rounded-2xl shadow p-4 mb-8 flex justify-between items-center max-w-7xl mx-auto">
        <div class="flex items-center gap-3">
            <img src="../images/logo.png" class="w-10 h-10" alt="Logo">
            <h1 class="text-xl font-bold text-purple-800">Digital Mall</h1>
        </div>

        <div class="flex gap-5 text-sm font-semibold">
            <a href="index.php" class="text-gray-600 hover:text-purple-700">Home</a>
            <a href="cart.php" class="text-gray-600 hover:text-purple-700">Cart</a>
            <a href="orders.php" class="text-gray-600 hover:text-purple-700">Orders</a>
            <a href="profile.php" class="text-gray-600 hover:text-purple-700">Profile</a>
            <a href="../logout.php" class="text-red-500 hover:text-red-600">Log Out</a>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
            <div class="md:flex">
                <div class="md:w-1/4 bg-gradient-to-br from-purple-50 to-yellow-50 flex items-center justify-center p-6">
                    <?php $logo = !empty($shop['logo']) ? $shop['logo'] : 'https://via.placeholder.com/150x150?text=Shop'; ?>
                    <img src="<?php echo $logo; ?>" alt="<?php echo $shop['shop_name']?>" class="object-cover shadow-md">                       
                </div>
                
                <div class="md:w-3/4 p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-bold text-purple-800"><?php echo $shop['shop_name']?>
                            <?php if($rating['rating'] > 0.00) : ?>
                            <span class="font-semibold text-base text-yellow-600">★<?php echo number_format($rating['rating'], 1)?></span>
                            <?php else: ?>
                            <span class="font-semibold text-base text-yellow-600">★0</span>
                            <?php endif; ?> 
                            </h2>
                            <div class="flex flex-wrap gap-3 mt-2">
                                <span class="inline-flex items-center gap-1 text-sm text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                    </svg>
                                    <?php echo $shop['category_name']?>
                                </span>
                                <span class="inline-flex items-center gap-1 text-sm text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <?php echo $shop['location']?>
                                </span>

                                <span class="inline-flex items-center gap-1 text-sm text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    <?php echo $shop['shop_phone'] ?? '+1 234 567 890' ?>
                                </span>

                                <span class="inline-flex items-center gap-1 text-sm text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <?php echo $shop['shop_email'] ?? 'info@example.com'?>
                                </span>
                            </div>
                        </div>
                        <div>
                            <?php if($shop['status'] === 'active'): ?>
                                <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">Open</span>
                            <?php elseif($shop['status'] === 'temporary_closed'): ?>
                                <span class="bg-orange-500 text-white px-3 py-1 rounded-full text-xs font-semibold">Temporary Closed</span>
                            <?php endif; ?>
                        </div>
                         <div class="mt-4 pt-3 border-t border-gray-100">
                            <?php if(!$rated): ?>
                                <form method="POST" class="flex items-center gap-3">
                                    <span class="text-sm text-gray-600">Rate this shop:</span>
                                    <input type="number" name="rating" min="1" max="5" step="0.1" required 
                                        placeholder="1-5"
                                        class="w-16 px-2 py-1 border rounded-lg text-sm text-center focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <button type="submit" name="SubmitRatingBtn" 
                                            class="bg-purple-500 text-white px-3 py-1 rounded-lg text-xs hover:bg-purple-600">
                                        Submit Rating
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-600">Your rating:</span>
                                    <div class="flex items-center">
                                        <span class="text-green-600 text-xs">✓ Rated <?php echo number_format($user_rating, 1)?>★</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if(!empty($shop['description'])): ?>
                        <p class="text-gray-600 mt-4 text-sm leading-relaxed">
                            <?php echo nl2br(htmlspecialchars(substr($shop['description'], 0, 200))); ?>
                            <?php if(strlen($shop['description']) > 200): ?>...<?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-purple-800">Products</h3>
                <div class="flex gap-2">
                    <form action="" method="GET">
                        <input type="hidden" name="shop_id" value="<?php echo $shop_id; ?>">
                        <input type="text" id="" name="Search" placeholder="Search product..." class="border rounded-lg px-4 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <select id="" name="Filter" class="border rounded-lg px-4 py-2">
                            <option value="">All categories</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']?>"><?php echo $cat['name']?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="SearchBtn" class="bg-purple-500 text-white px-6 py-2 ml-6 rounded-lg hover:bg-purple-600 transition">
                            Search
                        </button>
                    </form>
                    
                </div>
            </div>

            <?php if(count($products) == 0): ?>
                <div class="text-center py-12">
                    <p class="text-gray-500">No products available in this store.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="productsGrid">
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
                                    <span class="text-sm">Category:</span>
                                    <span class="text-sm text-green-700 mb-2 px-2 py-1 bg-green-100 rounded-full"> <?php echo $product['category_name']?></span>

                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Stock: <?php echo $product['stock']?> units</span>
                                        <span class="text-gray-900 font-semibold">$<?php echo number_format($product['price'], 2)?></span>
                                    </div>

                                    <form action="" method="POST" class="mt-3">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="old_stock" value="<?php echo $product['stock']; ?>">
                                        
                                        <?php if($product['stock'] > 0 && $shop['status'] === 'active'): ?>
                                            <div class="flex items-center gap-3">
                                                <input type="number" name="quantity" 
                                                    value="1" min="1" max="<?php echo $product['stock']; ?>" 
                                                    class="w-20 h-9 text-center border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm">
                                                <button type="submit" name="AddToCartBtn" class="flex-1 bg-purple-600 text-white py-2 rounded-lg text-sm font-semibold hover:bg-purple-700 transition flex items-center justify-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 1.5M17 13l1.5 1.5M9 21h6M12 18v3"></path>
                                                    </svg>
                                                    Add to Cart
                                                </button>
                                            </div>
                                        <?php elseif($product['stock'] == 0): ?>
                                            <button disabled class="w-full bg-gray-300 text-gray-500 py-2 rounded-lg text-sm font-semibold cursor-not-allowed">
                                                Out of Stock
                                            </button>
                                        <?php elseif($shop['status'] !== 'active'): ?>
                                            <button disabled class="w-full bg-gray-300 text-gray-500 py-2 rounded-lg text-sm font-semibold cursor-not-allowed">
                                                Shop Temporarily Closed
                                            </button>
                                        <?php endif; ?>
                                    </form>         
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    
</body>
</html>