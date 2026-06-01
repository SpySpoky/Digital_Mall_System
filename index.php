<?php
session_start();
require_once 'config/db.php';

$error = "";

if (isset($_POST['submitSession'])) {
    $email = $_POST['Email'];
    $password = $_POST['Password'];

    $sql = "SELECT * FROM users1 WHERE email = '$email'";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            if ($user['status'] === 'active') {
                $_SESSION['temp_user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];

                $user_id = $user['id'];

                if($user['role'] == 'manager') {
                    $sql_shops = "SELECT id, status from shops1 where manager_id = '$user_id'";
                    $shop_result = mysqli_query($conn, $sql_shops);
                    
                    if(mysqli_num_rows($shop_result) == 0) {
                        header("Location: create_shop.php");
                        exit();
                    } else {
                        $shop = mysqli_fetch_assoc($shop_result);
                        $_SESSION['shop_id'] = $shop['id'];

                        if($shop['status'] == 'pending') {
                            header("Location: logout.php");
                            exit();
                        } else {
                            header('Location: manager/index.php');
                            exit();
                        }
                    } 
                }

                switch ($user['role']) {
                    case 'admin':
                        header('Location: admin/index.php');
                        break;                        
                    case 'customer':
                        header('Location: customer/index.php');
                        break;
                    case 'courier':
                        header("Location: courier/index.php");
                        break;
                    default:
                        header('Location: logout.php');
                        break;
                }
                exit();
            } else {
                $error = "You are blocked!";  
            }   
        } else {
            $error = "Invalid email or password!";  
        }
    } else {
        $error = "Invalid email or password!";  
        
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Mall - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-yellow-100 via-yellow-200 to-purple-200 flex items-center justify-center min-h-screen p-6">

    <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
    <div class="absolute w-96 h-96 bg-purple-300 rounded-full blur-3xl opacity-40 top-10 left-10"></div>
    <div class="absolute w-96 h-96 bg-yellow-300 rounded-full blur-3xl opacity-40 bottom-10 right-10"></div>
    </div>
    
    <div class="bg-white p-8 rounded-2xl shadow-lg w-96">
        <div class="text-center mb-6">
            <img src="images/logo.png" class="w-36 mx-auto mb-2" alt="Logo.png">
            <h1 class="text-2xl font-bold text-purple-700">Digital Mall</h1>
            <p class="text-gray-500 text-sm">Login to your account</p>
        </div>

        <form id="loginForm" action="" class="space-y-4" method="POST">
            <input id="email" name="Email" type="email" placeholder="Email" required class="w-full p-3 border rounded-lg hover:border-purple-400 focus:outline-none focus:ring-2 focus:ring-purple-600 transition">
            <input id="password" name="Password" type="password" placeholder="Password" required minlength="6" class="w-full p-3 border rounded-lg hover:border-purple-400 focus:outline-none focus:ring-2 focus:ring-purple-600 transition">
            <button id="loginBtn" type="submit" name="submitSession" class="w-full bg-purple-700 text-white p-3 rounded-lg font-semibold hover:bg-purple-800 hover:scale-[1.02] active:scale-[0.98] transition">
                Login
            </button>
        </form>

        <?php if ($error):?>
            <script>alert('<?php echo $error ?>');</script>
        <?php endif; ?>

        <p class="text-center text-sm mt-4">
            Don't have an account?
            <a href="singup.php" class="text-purple-600 font-semibold hover:text-purple-700 hover:underline transition">Sing-up</a>
        </p>
        <p class="text-center text-sm mt-4">
            Are you a shop owner?
            <a href="singup_manager.php" class="text-purple-600 font-semibold hover:text-purple-700 hover:underline transition">Sing-up</a>
        </p>

    </div>
    <script src="js/login.js"></script>
</body>
</html>