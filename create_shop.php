<?php
session_start();
require_once "config/db.php";
$error = '';

if (!isset($_SESSION['temp_user_id'])) {
    header('Location: logout.php');
    exit();
}

$user_id = $_SESSION['temp_user_id'];

$sql = "SELECT id, name FROM shop_category where status = 'active'";
$result = mysqli_query($conn, $sql);
$categories = [];

while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}

if(isset($_POST['SubmitBtn'])) {
    $shop_name = $_POST['name'];
    $shop_category = $_POST['category'];
    $shop_location = $_POST['location'];
    $shop_email = $_POST['email'];
    $shop_phone = $_POST['phone'];
    $shop_hours = $_POST['hours'];
    $shop_description = $_POST['description'];
    $shop_status = 'pending';


    $upload_dir = 'images/';
    $logo_tmp = $_FILES['logo']['tmp_name'];
    $logo_name = $_FILES['logo']['name'];
    $destination = $upload_dir . $logo_name;
    $shop_logo = "../".$destination;
    move_uploaded_file($logo_tmp, $destination);
    
    $sql = "INSERT into shops1 (shop_name, shop_email, shop_phone, manager_id, category_id, location, status, description, work_hours, logo)
    values('$shop_name', '$shop_email', '$shop_phone', '$user_id', '$shop_category', '$shop_location', '$shop_status', '$shop_description', '$shop_hours', '$shop_logo')";

    if(mysqli_query($conn, $sql)) {
        header("Location: index.php");
        exit();
    } else {
        $error = "Something has gone wrong!";
    }
}


?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Digital - Mall Shop creation</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-yellow-100 to-purple-200 min-h-screen p-6">
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-xl p-8">
        <h2 class="text-2xl font-bold text-purple-800 mb-6">Shop creation</h2>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Shop Name *</label>
                <input type="text" name="name" required class="w-full border rounded-lg px-3 py-2">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Category *</label>
                <select name="category" required class="w-full border rounded-lg px-3 py-2">
                    <option value="">Select category</option>
                    <?php foreach($categories as $category): ?>
                        <option value="<?php echo $category['id']?>"> <?php echo $category['name']?> </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Location</label>
                <input type="text" name="location" class="w-full border rounded-lg px-3 py-2" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Shop email</label>
                <input type="email" name="email" class="w-full border rounded-lg px-3 py-2" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Phone No</label>
                <input type="text" name="phone" class="w-full border rounded-lg px-3 py-2" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Working hours</label>
                <input type="text" name="hours" placeholder="09:00 - 21:00" class="w-full border rounded-lg px-3 py-2" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Description</label>
                <textarea name="description" rows="4" class="w-full border rounded-lg px-3 py-2" required></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Shop logo</label>
                <input type="file" name="logo" accept="image/*" class="w-full border rounded-lg px-3 py-2" required>
            </div>
            
            <div class="flex gap-4">
                <button type="submit" name="SubmitBtn" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition">
                    Create shop
                </button>
                <a href="logout.php" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">
                    Exit (create later)
                </a>
            </div>
        </form>
    </div>
</body>
</html>