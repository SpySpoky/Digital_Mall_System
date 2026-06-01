<?php
require_once "../config/db.php";
include "../session.php";
include "role.php";


    $manager_id = $_SESSION['temp_user_id'];
    $sql = "SELECT s.*, c.name as category_name 
    from shops1 s
    left join shop_category c on s.category_id = c.id 
    where manager_id = '$manager_id'";

    $result = mysqli_query($conn, $sql);
    $shop = mysqli_fetch_assoc($result);

    $shop_id = $shop['id'];

    $sql = "SELECT SUM(total_amount) as revenue from orders where shop_id = '$shop_id' and status = 'delivered'";
    $result = mysqli_query($conn, $sql);
    $revenue = mysqli_fetch_assoc($result);

    $sql = "SELECT count(*) as active_orders from orders where shop_id = '$shop_id' and status not in ('delivered', 'cancelled')";
    $result = mysqli_query($conn, $sql);
    $active_orders = mysqli_fetch_assoc($result);

    $sql = "SELECT AVG(rating) as rating from rating where shop_id = '$shop_id'";
    $result = mysqli_query($conn, $sql);
    $rating = mysqli_fetch_assoc($result);

    $sql = "SELECT * FROM notes where manager_id = '$manager_id'";
    $result = mysqli_query($conn, $sql);
    $notes = [];
    while($row = mysqli_fetch_assoc($result)) {
        $notes[] = $row;
    }

    $sql = "SELECT * from shop_category where status = 'active'";
    $result = mysqli_query($conn, $sql);
    $categories = [];
    while($row = mysqli_fetch_assoc($result)){
        $categories[] = $row;
    }

    if(isset($_POST['EditDescrBtn'])) {// edit description
        $text = $_POST['Description'];

        $sql = "UPDATE shops1 SET description = '$text' where id = '$shop_id'";
        mysqli_query($conn, $sql);
        header("Location: my-shop.php");
        exit();
    }

    if(isset($_POST['AddNoteBtn'])) { // add note
        $title = $_POST['NoteTitle'];
        $content = $_POST['NoteContent'];

        $sql = "INSERT into notes(title, content, manager_id) VALUES ('$title', '$content', '$manager_id')";
        mysqli_query($conn, $sql);

        header("Location: my-shop.php");
        exit();
    }

    if(isset($_POST['EditNoteBtn'])) { // edit note
        $note_id = $_POST['EditNoteId'];
        $title = $_POST['EditNoteTitle'];
        $content = $_POST['EditNoteContent'];

        $sql = "UPDATE notes set title = '$title', content = '$content' where id = '$note_id'";
        mysqli_query($conn, $sql);

        header("Location: my-shop.php");
        exit();
    }

    if(isset($_POST['DeleteNoteBtn'])) { // delete note
        $note_id = $_POST['DeleteNoteId'];

        $sql = "DELETE from notes where id = '$note_id'";
        mysqli_query($conn, $sql);

        header("Location: my-shop.php");
        exit();
    }

    if (isset($_POST['EditShopBtn'])) { // edit shop
        $name = $_POST['EditName'];
        $category = $_POST['EditCategory'];
        $location = $_POST['EditLocation'];
        $email = $_POST['EditEmail'];
        $phone = $_POST['EditPhone'];
        $hours = $_POST['EditHours'];

        $sql = "UPDATE shops1 set shop_name = '$name', shop_email = '$email', shop_phone = '$phone', category_id = '$category', location = '$location', work_hours = '$hours' where id = '$shop_id'";
        if(mysqli_query($conn, $sql)) {
            if (isset($_FILES['EditImage']) && $_FILES['EditImage']['error'] == 0) {
                $file_name = $_FILES['EditImage']['name'];
                $file_tmp = $_FILES['EditImage']['tmp_name'];

                $destination = "../images/".time()."_".$file_name;

                if(move_uploaded_file($file_tmp, $destination)) {
                    $sql = "UPDATE shops1 set logo = '$destination' where id = '$shop_id'";

                    mysqli_query($conn, $sql);
                }
            }
        }
        header("Location: my-shop.php");
        exit();        
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager My-Shop - Digital Mall</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                <a href="index.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Dashboard</a>
                <a href="my-shop.php" class="block px-4 py-3 rounded-lg bg-purple-700 border-l-4 border-yellow-300 font-semibold">My Shop Info</a>
                <a href="products.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Products</a>
                <a href="orders.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Orders</a>
                <a href="deliveries.php" class="block px-4 py-3 border-l-4 border-transparent rounded-lg hover:bg-purple-700 hover:translate-x-1 hover:border-yellow-300 transition">Deliveries</a>
            </nav>

            <div class="p-4 border-purple-700">
                <a href="../logout.php" class="block px-4 py-3 rounded-lg bg-red-500 hover:bg-red-600 transition text-center font-semibold">Logout</a>
            </div>
        </aside>

        <main class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-3xl font-extrabold text-purple-800">My Shop</h2>
                    <p class="text-gray-600 mt-1">Manage your shop information</p>
                </div>

                <button type="button" class="bg-purple-700 text-white px-4 py-2 rounded-md mr-6 text-sm font-medium hover:bg-purple-800 transition"
                onclick="openEditShopPopup(
                <?php echo $shop['id'] ?>,
                '<?php echo $shop['shop_name']?>',
                '<?php echo $shop['category_id']?>',
                '<?php echo $shop['location']?>',
                '<?php echo $shop['shop_email']?>',
                '<?php echo $shop['shop_phone']?>',
                '<?php echo $shop['work_hours']?>'
                )">Edit Shop Info</button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <section class="lg:col-span-2 bg-white rounded-2xl shadow p-6 self-start">
                    <div class="flex items-start gap-6">
                        <div class="w-24 h-24 rounded-2xl bg-purple-100 flex items-center justify-center text-3xl">
                        <img src="<?php echo $shop['logo']?>" alt="Logo">
                        </div>

                        <div class="flex-1">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-2xl font-bold text-purple-800"><?php echo $shop['shop_name'] ?></h3>
                                    <?php if(strtolower($shop['status']) == 'active'): ?>
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">Active</span>
                                    <?php elseif(strtolower($shop['status']) == 'blocked'): ?>
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-medium">Blocked</span>
                                    <?php elseif(strtolower($shop['status']) == 'pending'): ?>
                                    <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-medium">Pending</span>
                                    <?php elseif(strtolower($shop['status']) == 'temporary_closed'): ?>
                                    <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-medium">Temporary Closed</span>
                                    <?php endif;?>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-gray-700">
                                    <p><span class="font-semibold">Category:</span> <?php echo $shop['category_name']?></p>
                                    <p><span class="font-semibold">Location:</span> <?php echo $shop['location']?></p>
                                    <p><span class="font-semibold">Email:</span> <?php echo $shop['shop_email']?></p>
                                    <p><span class="font-semibold">Phone:</span> <?php echo $shop['shop_phone']?></p>
                                    <p><span class="font-semibold">Created:</span> <?php echo $shop['created_at']?></p>
                                    <p><span class="font-semibold">Working Hours:</span> <?php echo $shop['work_hours']?></p>
                                </div>
                        </div>
                    </div>
                </section>

                <section class="bg-white rounded-2xl shadow p-6 self-start">
                    <h3 class="text-xl font-bold text-purple-800 mb-4">Quick Stats</h3>
                    <div class="space-y-4 text-sm text-gray-700">
                        <div class="flex justify-between border rounded-xl p-3">
                            <span>Total Products</span>
                            <?php
                                $sql = "SELECT count(*) as count_products from products where shop_id = '$shop_id'";
                                $result = mysqli_query($conn, $sql);
                                $count = mysqli_fetch_assoc($result);
                            ?>
                            <span class="font-semibold text-purple-700"><?php echo $count['count_products'] ?></span>
                        </div>

                        <div class="flex justify-between border rounded-xl p-3">
                            <span>Active Orders</span>
                            <span class="font-semibold text-blue-600"><?php echo $active_orders['active_orders'] ?></span>
                        </div>

                        <div class="flex justify-between border rounded-xl p-3">
                            <span>Rating</span>
                            <?php if($rating['rating'] > 0.00) : ?>
                            <span class="font-semibold text-yellow-600">★<?php echo number_format($rating['rating'], 1)?></span>
                            <?php else: ?>
                            <span class="font-semibold text-yellow-600">★0</span>
                            <?php endif; ?>                        
                        </div>

                        <div class="flex justify-between border rounded-xl p-3">
                            <span>Revenue</span>
                            <?php if($revenue['revenue'] > 0): ?>
                            <span class="font-semibold text-green-600">$<?php echo $revenue['revenue'] ?></span>
                            <?php else: ?>
                            <span class="font-semibold text-green-600">$0</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

                <section class="bg-white rounded-2xl shadow p-6 self-start">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-purple-800">Shop Description</h3>

                        <button onclick="openEditDesc('<?php echo $shop['description']?>')" class="bg-yellow-400 text-white px-4 py-2 mb-1 rounded text-xs hover:bg-yellow-500 transition">
                            ✏️
                        </button>
                    </div>
                    <p class="text-gray-700 leading-7"><?php echo $shop['description'] ?></p>
                </section>

                <section class="bg-white rounded-2xl shadow p-6 self-start">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-purple-800">Manager Notes</h3>
                        <button class="bg-green-500 text-white px-3 py-1.5 rounded-md text-sm font-medium hover:bg-green-600 transition"
                        onclick="openAddNote()">
                            Add Note
                        </button>
                    </div>

                    <div class="space-y-4 text-sm text-gray-700">
                        <?php foreach($notes as $note): ?>
                        <div class="border rounded-xl p-4">
                            <div class="flex items-start justify-between mb-2">
                                <p class="font-medium text-purple-700"><?php echo $note['title'] ?></p>
                                <div class="flex gap-2">
                                    <button class="bg-yellow-400 text-white px-2 py-1 mb-1 rounded text-xs hover:bg-yellow-500 transition"
                                    onclick="editNotePopup(
                                    <?php echo $note['id']?>,
                                    '<?php echo $note['title']?>',
                                    '<?php echo $note['content']?>')
                                    ">
                                        ✏️
                                    </button>
                                    <button class="bg-red-500 text-white px-2 py-1 mb-1 rounded text-xs hover:bg-red-600 transition"
                                    onclick="deleteNotePopup(
                                    <?php echo $note['id'] ?>,
                                    '<?php echo $note['title']?>'
                                    )">
                                        🗑
                                    </button>
                                </div>
                            </div>
                            <p><?php echo $note['content'] ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        </main>
    </div>
    <!-- Edit Shop Info popup -->
    <div id="editShopPopup" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 relative">

        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-purple-800">Edit Shop Info</h3>
            <button type="button" onclick="closeEdPop()" class="text-gray-500 hover:text-red-500 text-3xl leading-none">&times;</button>
        </div>

        <form method="POST" action="" class="space-y-4" enctype="multipart/form-data">
            <input type="hidden" name="shop_id" id="editShopId">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shop Name</label>
                    <input type="text" name="EditName" id="editShopName"
                        class="w-full border rounded-lg bg-gray-50 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="EditCategory" id="editShopCategory" class="w-full border rounded-lg bg-gray-50 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <?php foreach($categories as $category): ?>
                        <option value="<?php echo $category['id']?>"><?php echo $category['name']?></option>
                        <?php endforeach;?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <input type="text" name="EditLocation" id="editShopLocation"
                        class="w-full border rounded-lg bg-gray-50 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="EditEmail" id="editShopEmail"
                        class="w-full border rounded-lg bg-gray-50 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="EditPhone" id="editShopPhone"
                        class="w-full border rounded-lg bg-gray-50 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Working Hours</label>
                    <input type="text" name="EditHours" id="editShopHours" class="w-full border rounded-lg bg-gray-50 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-1">New Product Image</label>
                    <input type="file" name="EditImage" class="w-full text-sm">
                    <p class="text-xs text-gray-500 mt-1">Leave empty if you do not want to change the image.</p>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="submit" name="EditShopBtn" class="px-5 py-2 rounded-lg bg-green-500 text-white hover:bg-green-600 transition">
                    Save Changes
                </button>

                <button type="button" onclick="closeEdPop()" class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
    </div>

    <!-- Edit description popup -->
     <div class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" id="editShopDescriptionPopup">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 relative">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-purple-800">Edit Description</h3>
                <button onclick="closeDescPop()" class="text-gray-500 hover:text-red-500 text-3xl leading-none">&times;</button>
            </div>

            <form action="" method="POST" class="space-y-4">
                <div>
                    <label for="" class="block text-sm font-medium text-gray-700 mb-2">Shop Description</label>
                    <textarea name="Description" id="description" class="w-full border rounded-lg bg-gray-50 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none" rows="7"></textarea>      
                </div>

                <div class="flex justify-end gap-3">
                    <button type="submit" name="EditDescrBtn" class="px-5 py-2 rounded-lg bg-green-500 text-white hover:bg-green-600 transition">Save Changes</button>
                    <button type="button" onclick="closeDescPop()" class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 transition">Cancel</button>
                </div>           
            </form>
        </div>
     </div>

     <!-- Add note Popup -->
    <div class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" id="addNotesPopup">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 relative">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-purple-800">Add Note</h3>
                <button onclick="closeAddNotePop()" class="text-gray-500 hover:text-red-500 text-3xl leading-none">&times;</button>
            </div>

            <form action="" method="POST" class="space-y-4">
                <div>
                    <label for="addNoteTitle" class="block text-sm font-medium text-gray-700 mb-1">Note Title</label>
                    <input type="text" class="w-full border rounded-lg bg-gray-50 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
                    id="addNoteTitle"
                    name="NoteTitle"
                    placeholder="Enter note title"
                    required>
                </div>

                <div>
                    <label for="addNoteContent" class="block text-sm font-medium text-gray-700 mb-1">Note Content</label>
                    <textarea
                        name="NoteContent"
                        id="addNoteContent"
                        rows="6"
                        class="w-full border rounded-lg bg-gray-50 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none"
                        placeholder="Write your note here..."
                        required></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                <button type="submit" name="AddNoteBtn" class=" px-5 py-2 rounded-lg bg-green-500 text-white hover:bg-green-600 transition">
                    Confirm
                </button>

                <button type="button" onclick="closeAddNotePop()" class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 transition">
                    Cancel
                </button>
                </div>

            </form>
        </div>
    </div>

    <!-- Edit Note popup -->
    <div class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" id="editNotePopup">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 relative">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-purple-800">Edit Note</h3>
                <button onclick="closeEditNotePop()" class="text-gray-500 hover:text-red-500 text-3xl leading-none">&times;</button>
            </div>

            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="EditNoteId" id="editNoteId">

                <div>
                    <label for="editNoteTitle" class="block text-sm font-medium text-gray-700 mb-1">Note Title</label>
                    <input type="text" class="w-full border rounded-lg bg-gray-50 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
                    id="editNoteTitle"
                    name="EditNoteTitle">
                </div>

                <div>
                    <label for="editNoteContent" class="block text-sm font-medium text-gray-700 mb-1">Note Content</label>
                    <textarea
                        name="EditNoteContent"
                        id="editNoteContent"
                        rows="6"
                        class="w-full border rounded-lg bg-gray-50 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                <button type="submit" name="EditNoteBtn" class=" px-5 py-2 rounded-lg bg-green-500 text-white hover:bg-green-600 transition">
                    Save Changes
                </button>

                <button type="button" onclick="closeEditNotePop()" class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 transition">
                    Cancel
                </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Note Popup -->

    <div class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" id="deleteNotePopup">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 relative">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-purple-800">Delete Note</h3>
                <button onclick="closeDeleteNotePop()" class="text-gray-500 hover:text-red-500 text-3xl leading-none">&times;</button>
            </div>

            <p class="text-gray-600 mb-6">Are you sure you want to delete note:
                <span class="font-semibold text-purple-700" id="deleteNoteTitle"></span>
            </p>

            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="DeleteNoteId" id="deleteNoteId">
                <div class="flex justify-end gap-3 pt-2">
                <button type="submit" name="DeleteNoteBtn" class=" px-5 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600 transition">
                    Delete
                </button>

                <button type="button" onclick="closeDeleteNotePop()" class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 transition">
                    Cancel
                </button>
                </div>
            </form>
        </div>
    </div>

    <script src="manager_js/my-shop.js"></script>
</body>
</html>