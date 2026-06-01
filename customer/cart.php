<?php
require_once "../config/db.php";
include "../session.php";
include "role.php";

    $user_id = $_SESSION['temp_user_id'];

    if(isset($_POST['UpdateCartBtn'])) {
        $item_id = $_POST['item_id'];
        $new_quantity = (int)$_POST['quantity'];
        
        // Получаем остаток товара
        $stock_sql = "SELECT p.stock from cart c join products p on c.product_id = p.id where c.id = '$item_id' and c.user_id = '$user_id'";
        $stock_result = mysqli_query($conn, $stock_sql);
        $product = mysqli_fetch_assoc($stock_result);
        
        if($new_quantity > $product['stock']) {
            $error = "Not enough stock. Only " . $product['quantity'] . " units available.";
        } elseif($new_quantity < 1) {
            $error = "Quantity must be at least 1";
        } else {
            $sql = "UPDATE cart SET quantity = '$new_quantity' where id = '$item_id' and user_id = '$user_id'";
            mysqli_query($conn, $sql);
        }
        header("Location: cart.php");
        exit();
    }

    if(isset($_POST['RemoveItemBtn'])) {
        $item_id = $_POST['item_id'];
        $sql = "DELETE from cart where id = '$item_id' and user_id = '$user_id'";
        mysqli_query($conn, $sql);
        header("Location: cart.php");
        exit();
    }

    if(isset($_POST['ClearCartBtn'])) {
        $sql = "DELETE from cart where user_id = '$user_id'";
        mysqli_query($conn, $sql);
        header("Location: cart.php");
        exit();
    }

    $sql = "SELECT c.id as cart_id, c.quantity, c.product_id, p.* 
    from cart c
    join products p on c.product_id = p.id
    where c.user_id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    $cart_items = [];
    $subtotal = 0;

    while($row = mysqli_fetch_assoc($result)) {
        $row['item_total'] = $row['price'] * $row['quantity'];
        $subtotal += $row['item_total'];
        $cart_items[] = $row;
    }

    $shipping_cost = ($subtotal > 0) ? 5.00 : 0;
    $tax = $subtotal * 0.1;
    $total = $subtotal + $shipping_cost + $tax;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Digital Mall</title>
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
            <a href="index.php" class="text-gray-600 hover:text-purple-700">Home</a>
            <a href="cart.php" class="text-purple-700 underline">Cart</a>
            <a href="orders.php" class="text-gray-600 hover:text-purple-700">Orders</a>
            <a href="profile.php" class="text-gray-600 hover:text-purple-700">Profile</a>
            <a href="../logout.php" class="text-red-500 hover:text-red-600">Log Out</a>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto">
        <div class="mb-6">
            <h2 class="text-3xl font-extrabold text-purple-800">Shopping Cart</h2>
            <p class="text-gray-600 mt-1">Review your items before checkout</p>
        </div>

        <?php if(count($cart_items) == 0): ?>
            <div class="bg-white rounded-2xl shadow p-12 text-center">
                <p class="text-gray-500 text-lg">Your cart is empty</p>
                <a href="index.php" class="inline-block mt-4 bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition">
                    Continue Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 ">
                <div class="lg:col-span-2 bg-white rounded-2xl shadow overflow-hidden self-start">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b text-gray-500 uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-4 text-left">Product</th>
                                    <th class="px-6 py-4 text-center">Quantity</th>
                                    <th class="px-6 py-4 text-right">Price</th>
                                    <th class="px-6 py-4 text-right">Total</th>
                                    <th class="px-6 py-4 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($cart_items as $item): ?>
                                    <tr class="border-b hover:bg-yellow-50 transition">
                                        <td class="px-6 py-4">
                                            <span class="font-medium text-purple-800"><?php echo $item['name']; ?></span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <form action="" method="POST" class="inline">
                                                <input type="hidden" name="item_id" value="<?php echo $item['cart_id']; ?>">
                                                <div class="flex items-center justify-center gap-1">
                                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                                           min="1" max="<?php echo $item['quantity']; ?>"
                                                           class="w-16 text-center border rounded-lg px-2 py-1 text-sm">
                                                    <button type="submit" name="UpdateCartBtn" class="text-blue-500 hover:text-blue-700" title="Update">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                        <td class="px-6 py-4 text-right font-semibold">
                                            $<?php echo number_format($item['price'], 2); ?>
                                        </td>
                                        <td class="px-6 py-4 text-right font-bold text-purple-700">
                                            $<?php echo number_format($item['item_total'], 2); ?>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <form action="" method="POST" class="inline">
                                                <input type="hidden" name="item_id" value="<?php echo $item['cart_id']; ?>">
                                                <button type="submit" name="RemoveItemBtn" class="text-red-500 hover:text-red-700" title="Remove">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="p-4 border-t flex justify-between">
                        <form action="" method="POST">
                            <button type="submit" name="ClearCartBtn" class="text-red-500 hover:text-red-700 text-sm">
                                Clear Cart
                            </button>
                        </form>
                        <a href="index.php" class="text-purple-600 hover:text-purple-800 text-sm">
                            ← Continue Shopping
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow p-6 h-fit self-start">
                    <form action="create_order.php" method="POST">
                        <input type="hidden" name="subtotal" value="<?php echo $subtotal; ?>">
                        <input type="hidden" name="shipping_cost" value="<?php echo $shipping_cost; ?>">
                        <input type="hidden" name="tax" value="<?php echo $tax; ?>">
                        <input type="hidden" name="total" value="<?php echo $total; ?>">

                        <h3 class="text-xl font-bold text-purple-800 mb-4">Order Summary</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-semibold">$<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Shipping:</span>
                                <span class="font-semibold">$<?php echo number_format($shipping_cost, 2); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tax (10%):</span>
                                <span class="font-semibold">$<?php echo number_format($tax, 2); ?></span>
                            </div>
                            <div class="border-t pt-3 mt-3">
                                <div class="flex justify-between">
                                    <span class="text-lg font-bold text-gray-800">Total:</span>
                                    <span class="text-xl font-bold text-purple-700">$<?php echo number_format($total, 2); ?></span>
                                </div>
                            </div>
                        </div>
                        <br><br>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Shipping Address</label>
                            <textarea name="shipping_address" rows="2" required
                                    class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
                                    placeholder="Enter your full address (street, city, postal code)"></textarea>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                            <select name="payment_type" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="card">Credit/Debit Card</option>
                                <option value="cash">Cash on Delivery</option>
                            </select>
                        </div>

                        <button name="CreateOrderBtn" type="submit" class="block w-full text-center bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition mt-6">
                            Create Order →
                        </button>
                    </form>
                    
                </div>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>