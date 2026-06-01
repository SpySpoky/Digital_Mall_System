<?php
include "../session.php";
include "role.php";
require_once "../config/db.php";


$user_id = $_SESSION['temp_user_id'];
$role = $_SESSION['user_role'] ?? '';

$sql = "SELECT * FROM users1 WHERE id = '$user_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);
$curr_pass_error = "";

// Обработка обновления профиля
if(isset($_POST['UpdateProfileBtn'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $surname = mysqli_real_escape_string($conn, $_POST['surname']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $check_sql = "SELECT id FROM users1 WHERE email = '$email' AND id != '$user_id'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if(mysqli_num_rows($check_result) > 0) {
        $error = "Email already exists!";
    } else {
        $update_sql = "UPDATE users1 SET name = '$name', surname = '$surname', phone = '$phone', address = '$address', email = '$email' WHERE id = '$user_id'";
        
        if(mysqli_query($conn, $update_sql)) {
           header("Location: index.php");
           exit();
        } else {
            $error = "Failed to update profile";
        }
    }
}

if(isset($_POST['ChangePasswordBtn'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $check_sql = "SELECT password FROM users1 WHERE id = '$user_id'";
    $check_result = mysqli_query($conn, $check_sql);
    $check_row = mysqli_fetch_assoc($check_result);
    
    if(!password_verify($current_password, $check_row['password'])) {
        $curr_pass_error = "Current password is incorrect";
    
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users1 SET password = '$hashed_password' WHERE id = '$user_id'";
        
        mysqli_query($conn, $update_sql);
        header("Location: profile.php");
        exit();
    }
    
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Digital Mall</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-yellow-100 via-yellow-200 to-purple-200 min-h-screen p-6">
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
    <div class="absolute w-96 h-96 bg-purple-300 rounded-full blur-3xl opacity-40 top-10 left-10"></div>
    <div class="absolute w-96 h-96 bg-yellow-300 rounded-full blur-3xl opacity-40 bottom-10 right-10"></div>
    </div>

    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-purple-800">My Profile</h1>
                <p class="text-gray-600 mt-1">Manage your personal information</p>
            </div>
            <a href="index.php" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                ← Back to Dashboard
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Левая колонка - Аватар и информация -->
            <div class="bg-white rounded-2xl shadow p-6 text-center">
                <div class="w-32 h-32 bg-purple-600 text-white flex items-center justify-center rounded-full text-4xl font-bold mx-auto mb-4">
                    <?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)) . strtoupper(substr($user['surname'] ?? 'ser', 0, 1)); ?>
                </div>
                <h2 class="text-xl font-bold text-purple-800"><?php echo $user['name'] . ' ' . $user['surname']; ?></h2>
                <p class="text-gray-500 text-sm mt-1 capitalize"><?php echo $role; ?></p>
                <p class="text-gray-500 text-sm">Member since: <?php echo date('M Y', strtotime($user['registered_at'] ?? 'now')); ?></p>
                
                <div class="mt-6 pt-6 border-t">
                </div>
            </div>

            <div class="md:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow p-6">
                    <h3 class="text-xl font-bold text-purple-800 mb-4">Edit Profile</h3>
                    
                    <form method="POST" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                                <input type="text" name="name" required 
                                       value="<?php echo $user['name'] ?? '' ?>"
                                       class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Surname *</label>
                                <input type="text" name="surname" required 
                                       value="<?php echo $user['surname'] ?? '' ?>"
                                       class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                <input type="email" name="email" required 
                                       value="<?php echo $user['email'] ?? ''; ?>"
                                       class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input type="tel" name="phone" 
                                       value="<?php echo $user['phone'] ?? ''; ?>"
                                       class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                <textarea name="address" rows="2" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"><?php echo $user['address'] ?? ''; ?></textarea>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" name="UpdateProfileBtn" 
                                    class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-2xl shadow p-6">
                    <h3 class="text-xl font-bold text-purple-800 mb-4">Change Password</h3>
                    
                    <form method="POST" class="space-y-4" id="PassForm">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                            <input type="password" name="current_password" required minlength="6"
                                   class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input type="password" name="new_password" id="new_pass" required minlength="6" 
                                   class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <p class="text-xs text-gray-500 mt-1">At least 6 characters</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <input type="password" name="confirm_password" id="conf_pass" required minlength="6"
                                   class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" name="ChangePasswordBtn" 
                                    class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition">
                                Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php if($curr_pass_error !== ""): ?>
    <script>alert("Old Password is incorrect!")</script>
    <?php endif;?>
    <script src="admin_js/profile.js"></script>
    
</body>
</html>