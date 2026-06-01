<?php 
session_start();
require_once 'config/db.php';
$error = "";
$success = "";

if (isset($_POST['SubmitSignUp'])) {
    $name = trim($_POST['Name']);
    $surname = trim($_POST['Surname']);
    $phone = trim($_POST['Phone']); 
    $country = trim($_POST['Country']);
    $city = trim($_POST['City']);
    $street = trim($_POST['Street']);
    $email = trim($_POST['Email']);
    $password = $_POST['Password'];
    $confirm_pass = $_POST['ConfirmPass'];

    $full_address = implode(', ', [$street, $city, $country]);
    $role = "customer";
    $status = "active";

    if($password === $confirm_pass) {
        $check_email = "SELECT email from users1 where email = '$email'";
        $result_email = mysqli_query($conn, $check_email);

        if(mysqli_num_rows($result_email) > 0) {
            $error = "Person with that Email (".$email.") already registered!";
        } else {
            $hash_pass = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT into users1(name, surname, phone, email, role, password, status, address) 
            values ('$name', '$surname', '$phone', '$email', '$role', '$hash_pass', '$status', '$full_address')";

            $result = mysqli_query($conn, $sql);

            if($result) {
                // $success = "Congratulations! You have registered!"; // relocate that to customer index.php
                $sql_new_customer = "SELECT * from users1 where email = '$email'";
                $result_new_customer = mysqli_query($conn, $sql_new_customer);
                $fetched_user = mysqli_fetch_assoc($result_new_customer);

                $_SESSION['user_id'] = $fetched_user['id'];
                $_SESSION['user_email'] = $fetched_user['email'];
                $_SESSION['user_role'] = $fetched_user['role'];

                header('Location: customer/index.php');
                exit();

            } else {
                $error = "Something went wrong, try again...";
            }
        }

    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Mall - Sign-up</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-yellow-100 via-yellow-200 to-purple-200 flex items-center justify-center min-h-screen p-6">
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
    <div class="absolute w-96 h-96 bg-purple-300 rounded-full blur-3xl opacity-40 top-10 left-10"></div>
    <div class="absolute w-96 h-96 bg-yellow-300 rounded-full blur-3xl opacity-40 bottom-10 right-10"></div>
    </div>
    
    <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">
        <div class="text-center mb-6">
            <img src="images/logo.png" class="w-36 mx-auto mb-2" alt="Logo.png">
            <h1 class="text-purple-700 text-2xl font-bold">Digital Mall</h1>
            <p class="text-gray-500 text-sm">Create your account</p>
        </div>

        <form action="" method="POST" id="signupForm" class="space-y-4">
            <div>
                <label for="" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" id="nameInput" name="Name" placeholder="Artem" required 
                class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
            </div>
            <div>
                <label for="" class="block text-sm font-medium text-gray-700 mb-1">Surname</label>
                <input type="text" id="surnameInput" name="Surname" placeholder="Titov" required
                class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">

            </div>
            <div>
                <label for="" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="tel" pattern="[\+\d\s\-\(\)]{5,20}" id="phoneInput" name="Phone" placeholder="+7-915-472-27-36" required
                class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
            </div>
            <div>
                <label for="" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                <input type="text" id="countryInput" name="Country" placeholder="Russia" required
                class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">

            </div>
            <div>
                <label for="" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <input type="text" id="cityInput" name="City" placeholder="Moscow" required
                class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">

            </div>
            <div>
                <label for=""class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                <input type="text" id="streetInput" name="Street" placeholder="Arbat Street" required
                class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
            </div>
            <div>
                <label for="" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="emailInput" name="Email" placeholder="name@example.com" required
                class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">

            </div>
            <div>
                <label for="" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" id="passwordInput" name="Password" placeholder="••••••••" required minlength="6"
                class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                <p class="text-xs text-gray-500 mt-1">At least 6 characters</p>
            </div>
            <div>
                <label for="" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <input type="password" id="confirmPassInput" name="ConfirmPass" placeholder="••••••••" required minlength="6"
                class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
            </div>

            <button id="signupBtn" type="submit" name="SubmitSignUp" class="w-full bg-purple-700 text-white p-3 rounded-lg font-semibold hover:bg-purple-800 hover:scale-[1.02] active:scale-[0.98] transition">
                Sign Up
            </button>
            
            <p class="text-center text-sm mt-4 text-gray-600">
                Already have an account?
                <a href="index.php" class="text-purple-600 font-semibold hover:text-purple-700 hover:underline transition">Login</a>
            </p>
        </form>
        <?php if($error !==""):?>
            <script>alert('<?php echo $error ?>')</script>
        <?php endif; ?>
        <?php if($success !==""):?>
            <script>alert('<?php echo $success ?>') console.log(success)</script>
        <?php endif; ?>
    </div>
    <script src="js/singup.js"></script>
</body>
</html>