<?php
require_once "../config/db.php";
include "../session.php";
include "role.php";

    $cat_sql = "SELECT * 
    FROM shop_category 
    WHERE status = 'active'";
    $cat_result = mysqli_query($conn, $cat_sql);
    $categories = [];
    while($row = mysqli_fetch_assoc($cat_result)) {
        $categories[] = $row;
    }

    if(isset($_GET['SearchBtn'])) {
        $search = $_GET['Search'];
        $filter = $_GET['Filter'];

        $sql = "SELECT s.*, c.name as category_name
        from shops1 s
        left join shop_category c ON s.category_id = c.id
        where s.status NOT IN ('blocked', 'pending')";

        if(!empty($search)) {
            $sql .= " AND (s.shop_name LIKE '%$search%' OR s.location LIKE '%$search%' OR c.name LIKE '%$search%')";
        }

        if(!empty($filter)) {
            $sql .= " AND c.id = '$filter'";
        }

        $sql .= " ORDER BY 
            CASE s.status
            WHEN 'active' THEN 1
            WHEN 'temporary_closed' THEN 2
            else 3
            end,
        s.id DESC";

        $result = mysqli_query($conn, $sql);
        $shops = [];
        while($row = mysqli_fetch_assoc($result)) {
            $shops[] = $row;
        }
    } else {
        $sql = "SELECT s.*, c.name as category_name
        from shops1 s
        left join shop_category c ON s.category_id = c.id
        WHERE s.status not in ('blocked', 'pending')
        ORDER BY 
            CASE s.status
            WHEN 'active' THEN 1
            WHEN 'temporary_closed' THEN 2
            else 3
            end,
        s.id DESC";

        $result = mysqli_query($conn, $sql);
        $shops = [];

        while($row = mysqli_fetch_assoc($result)) {
            $shops[] = $row;
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Home - Digital Mall</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
            <a href="index.php" class="text-purple-700 underline">Home</a>
            <a href="cart.php" class="text-gray-600 hover:text-purple-700">Cart</a>
            <a href="orders.php" class="text-gray-600 hover:text-purple-700">Orders</a>
            <a href="profile.php" class="text-gray-600 hover:text-purple-700">Profile</a>
            <a href="../logout.php" class="text-red-500 hover:text-red-600">Log Out</a>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto">
        <div class="mb-8">
            <h2 class="text-3xl font-extrabold text-purple-800">Available Shops</h2>
            <p class="text-gray-600 mt-1">Choose a store and start shopping.</p>
        </div>

        <div class="bg-white rounded-2xl shadow p-4 mb-8 flex flex-wrap gap-4">
            <form action="" method="GET">
                <input type="text" name="Search" id="" class="border rounded-lg px-4 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Search shop...">
                <select name="Filter" id="" class="border rounded-lg px-4 py-2 ml-6">
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

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            <?php foreach ($shops as $shop): ?>
                <div class="bg-white rounded-2xl shadow overflow-hidden hover:shadow-xl hover:-translate-y-1 transition">
                    <?php $logo = !empty($shop['logo']) ? $shop['logo'] : 'https://via.placeholder.com/600x400?text=No+Image';?>
                    <img src="<?php echo $logo?>" alt="<?php echo $shop['shop_name']?>" class="w-full h-40 object-cover">

                    <div class="p-5">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-lg font-bold text-purple-800"><?php echo $shop['shop_name']?>
                            <?php 
                            $sql = "SELECT avg(rating) as rating from rating where shop_id = '{$shop['id']}'";
                            $result = mysqli_query($conn, $sql);
                            $rating = mysqli_fetch_assoc($result);
                            ?>
                                <?php if($rating['rating'] > 0.00) : ?>
                                <span class="font-semibold text-sm text-yellow-600">★<?php echo number_format($rating['rating'], 1)?></span>
                                <?php else: ?>
                                <span class="font-semibold text-sm text-yellow-600">★0</span>
                                <?php endif; ?> 
                            </h3>
                            <?php if($shop['status'] === 'active'): ?>
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">Open</span>

                            <?php elseif($shop['status'] === 'blocked'): ?>
                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-medium">Closed</span>

                            <?php elseif($shop['status'] === 'temporary_closed'): ?>
                            <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-medium">Temp Closed</span>

                            <?php endif; ?>
                        </div>

                        <p class="text-sm text-gray-500 mb-1"> <?php echo $shop['category_name']?></p>
                        <p class="text-sm text-gray-600 mb-2">📍<?php echo $shop['location'] ?></p>
                        <?php if($shop['status'] === 'active'): ?>
                        <a href="store.php?shop_id=<?php echo $shop['id']?>" class="block w-full text-center bg-purple-700 text-white py-2 rounded-lg font-semibold hover:bg-purple-800 transition">View Store</a>
                        <?php elseif($shop['status'] === 'temporary_closed'): ?>
                        <button disabled class="block w-full text-center bg-gray-400 text-white py-2 rounded-lg font-semibold cursor-not-allowed">Temporarily Closed</button>
                        <?php else: ?>
                        <button disabled class="block w-full text-center bg-gray-400 text-white py-2 rounded-lg font-semibold cursor-not-allowed">Closed</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    
</body>
</html>