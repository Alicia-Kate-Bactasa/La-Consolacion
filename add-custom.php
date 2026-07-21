<?php
require_once 'db.php';
$errors = [];
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['username']) && isset($_SESSION['admin_username'])) {
    $_SESSION['username'] = $_SESSION['admin_username'];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customerName']);
    $email = trim($_POST['customerEmail']);
    $description = trim($_POST['customDescription']);
    $product_name = trim($_POST['productName']);
    $price = isset($_POST['customPrice']) ? floatval($_POST['customPrice']) : null;
    $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 1;
    $type = 'custom'; // Force type to 'custom' for all custom products
    $imagePath = '';
    if (isset($_FILES['customImage']) && $_FILES['customImage']['error'] === UPLOAD_ERR_OK) {
        $imgTmp = $_FILES['customImage']['tmp_name'];
        $imgName = basename($_FILES['customImage']['name']);
        $imgExt = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($imgExt, $allowed)) {
            $prefixName = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($customer_name));
            $newName = 'custom_' . $prefixName . '_' . uniqid() . '.' . $imgExt;
            $targetDir = 'Image/product-add/';
            $targetFile = $targetDir . $newName;
            if (move_uploaded_file($imgTmp, $targetFile)) {
                $imagePath = $newName;
            } else {
                $errors[] = 'Image upload failed.';
            }
        } else {
            $errors[] = 'Invalid image type.';
        }
    }
    // Validation
    if ($customer_name === '' || $email === '' || $description === '' || $product_name === '' || $price === null || $type === '') {
        $errors[] = 'All fields except image are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    } elseif (!is_numeric($price) || $price < 0) {
        $errors[] = 'Price must be a positive number.';
    } elseif (!is_numeric($stock) || $stock < 0) {
        $errors[] = 'Stock must be a positive number.';
    }
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            // Insert into products table first
            $productMaterial = null; // or extract from description if needed
            $stmtProduct = $pdo->prepare('INSERT INTO products (name, price, stock, type, material, description, image, deleted) VALUES (?, ?, ?, ?, ?, ?, ?, 0)');
            $stmtProduct->execute([$product_name, $price, $stock, $type, $productMaterial, $description, $imagePath]);
            $productId = $pdo->lastInsertId();
            // Insert into custom_orders using the new product id
            $stmtCustom = $pdo->prepare('INSERT INTO custom_orders (id, customer_name, email, description, status, created_at, deleted) VALUES (?, ?, ?, ?, ?, NOW(), 0)');
            $success = $stmtCustom->execute([$productId, $customer_name, $email, $description, 'pending']);

            // 1. Find or create user
            $stmtUser = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmtUser->execute([$email]);
            $user = $stmtUser->fetch();
            if ($user) {
                $user_id = $user['id'];
            } else {
                // Create a guest user (or use a default guest user_id)
                $guest_username = 'guest_' . uniqid();
                $stmtGuest = $pdo->prepare('INSERT INTO users (username, email, role, deleted) VALUES (?, ?, ?, 0)');
                $stmtGuest->execute([$guest_username, $email, 'guest']);
                $user_id = $pdo->lastInsertId();
            }

            // 2. Create order without payment (payment will be added when customer actually pays)
            $stmtOrder = $pdo->prepare('INSERT INTO orders (user_id, payment_id, status, date_ordered, deleted) VALUES (?, NULL, ?, NOW(), 0)');
            $stmtOrder->execute([$user_id, 'pending']);
            $order_id = $pdo->lastInsertId();

            // 4. Create order item
            $stmtOrderItem = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)');
            $stmtOrderItem->execute([$order_id, $productId, 1, $price]);
            $pdo->commit();
            $details = "Added custom order for: $customer_name | Description: $description";
            if (function_exists('log_action')) log_action($pdo, $_SESSION['username'], 'Add Custom Order', $details);
            $newId = $productId;
            // Send email to user using Node.js server
            $to = $email;
            $subject = 'Your Custom Order at La Consolacion Jewelry';
            $orderLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/payment-form.php?order_id=' . $newId;
            $message = "Hello $customer_name,\n\nThank you for your custom order!\n\nOrder Details:\nDescription: $description\nPrice: ₱" . number_format($price, 2) . "\n\nTo complete your order, please proceed to payment using the link below:\n$orderLink\n\nThank you!";
            
            // Send customer email
            $customerEmailData = [
                'to' => $to,
                'subject' => $subject,
                'text' => $message
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://localhost:3000/send-order-notification');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($customerEmailData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            curl_close($ch);
            
            // Send notification email to admin Gmail
            $adminEmail = 'kenzho.suarez@gmail.com'; // Using the configured Gmail
            $adminSubject = 'New Custom Order Received - La Consolacion Jewelry';
            $adminMessage = "A new custom order has been received!\n\nCustomer Details:\nName: $customer_name\nEmail: $email\n\nOrder Details:\nProduct Name: $product_name\nDescription: $description\nPrice: ₱" . number_format($price, 2) . "\nStock: $stock\n\nOrder ID: $newId\n\nView order details in admin panel.";
            
            $adminEmailData = [
                'to' => $adminEmail,
                'subject' => $adminSubject,
                'text' => $adminMessage
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://localhost:3000/send-order-notification');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($adminEmailData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            curl_close($ch);
            
            // Redirect back to admin inventory with success message
            header('Location: admin-inventory.php?custom_order_sent=1&customer_email=' . urlencode($email));
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Custom Order - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
      body {
        background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 50%, #e8eaf6 100%);
      }
      .glass {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
      }
      .gradient-bg {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      }
      .gradient-emerald {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      }
      .gradient-purple {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
      }
      .gradient-deep-blue {
        background: linear-gradient(135deg, #1f488a 0%, #123366 100%);
      }
    </style>
</head>
<body class="min-h-screen overflow-hidden">
    <!-- Header -->
    <header class="glass sticky top-0 z-50 px-6 py-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="flex items-center space-x-3">
            <a href="index.php">
              <img src="Image/LCJ.png" alt="LA Consolacion Logo" class="h-12 w-auto" />
            </a>
          </div>
        </div>
        <div class="flex items-center space-x-4">
          <span class="text-sm text-gray-600">
            Dashboard | Welcome <?php
              if (isset($_SESSION['username'])) {
                echo htmlspecialchars($_SESSION['username']);
              } elseif (isset($_SESSION['admin_username'])) {
                echo htmlspecialchars($_SESSION['admin_username']);
              } else {
                echo 'Admin';
              }
            ?>
          </span>
          <div class="w-8 h-8 gradient-bg rounded-full flex items-center justify-center">
            <i data-lucide="user" class="w-4 h-4 text-white"></i>
          </div>
        </div>
      </div>
    </header>
    <div class="flex h-[calc(100vh-80px)] overflow-hidden">
      <!-- Sidebar -->
      <aside class="fixed top-[80px] left-0 w-72 h-[calc(100vh-80px)] glass p-6 border-r border-gray-200 overflow-y-auto">
        <nav class="space-y-2">
          <a href="admin-overview.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left text-gray-600 hover:bg-gray-100 transition-all duration-200">
            <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
            <span class="font-medium">Overview</span>
          </a>
          <a href="admin-orders.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left text-gray-600 hover:bg-gray-100 transition-all duration-200">
            <i data-lucide="shopping-cart" class="w-5 h-5"></i>
            <span class="font-medium">Orders</span>
          </a>
          <a href="admin-inventory.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left transition-all duration-200 gradient-deep-blue text-white shadow-lg">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="package" class="lucide lucide-package w-5 h-5"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path><path d="M12 22V12"></path><polyline points="3.29 7 12 12 20.71 7"></polyline><path d="m7.5 4.27 9 5.15"></path></svg>
      <span class="font-medium">Inventory</span>
    </a>
          <a href="admin-users.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left text-gray-600 hover:bg-gray-100 transition-all duration-200">
            <i data-lucide="users" class="w-5 h-5"></i>
            <span class="font-medium">Users</span>
          </a>
          <a href="admin-logs.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left text-gray-600 hover:bg-gray-100 transition-all duration-200">
            <i data-lucide="activity" class="w-5 h-5"></i>
            <span class="font-medium">Logs</span>
          </a>
        </nav>
        <div>
          <a href="logout.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 transition-all duration-200 mt-4">
            <i data-lucide="log-out" class="w-5 h-5"></i>
            <span class="font-medium">Log Out</span>
          </a>
        </div>
      </aside>
<main class="flex-1 p-8 ml-72 h-[calc(100vh-80px)]">
    <div class="w-full max-w-xl mx-auto bg-white rounded-2xl shadow-xl p-8 mt-10">
        <div class="flex items-center gap-3 mb-6">
            <span class="inline-flex items-center justify-center bg-blue-100 rounded-full p-2 text-3xl"><i data-lucide="plus-circle"></i></span>
            <h1 class="text-2xl font-bold tracking-wide m-0">Add Custom Order</h1>
        </div>
        <?php if (!empty($errors)): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded"> <?php echo implode('<br>', $errors); ?> </div>
        <?php endif; ?>
        <form action="add-custom.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <div class="flex flex-col items-center">
                <div id="addImagePreviewContainer" class="relative mb-2 hidden">
                    <img id="addImagePreview" src="" alt="Preview" class="w-32 h-32 object-cover rounded-xl shadow-lg border-2 border-blue-100 bg-white" />
                    <button type="button" id="removeAddImageBtn" class="absolute top-0 right-0 flex items-center justify-center w-8 h-8 bg-white bg-opacity-80 rounded-full text-xl font-bold leading-none text-gray-700 hover:bg-red-100 hover:text-red-600 transition" title="Remove image">×</button>
                </div>
                <label id="addUploadBox" class="w-full flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-lg p-8 bg-gray-50 text-gray-600 cursor-pointer transition hover:bg-gray-100 relative">
                    <span class="text-4xl mb-2">
                        <i data-lucide="upload-cloud" class="w-12 h-12 mx-auto text-gray-400"></i>
                    </span>
                    <span class="font-medium text-base mb-1">Click to upload or drag and drop</span>
                    <span class="text-xs text-gray-400 mb-2">PNG, JPG, GIF up to 10MB</span>
                    <input type="file" name="customImage" id="add_itemImage" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" />
                </label>
                <div style="font-size:11px;color:#64748b;text-align:left;margin-top:4px;">For best results, upload a 1x1 (square) image.</div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-gray-700">Customer Name
                        <input type="text" name="customerName" class="form-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required value="<?php echo htmlspecialchars($_POST['customerName'] ?? ''); ?>" />
                    </label>
                </div>
                <div>
                    <label class="block font-semibold text-gray-700">Email
                        <input type="email" name="customerEmail" class="form-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required value="<?php echo htmlspecialchars($_POST['customerEmail'] ?? ''); ?>" />
                    </label>
                </div>
                <div class="md:col-span-2">
                    <label class="block font-semibold text-gray-700">Product Name
                        <input type="text" name="productName" class="form-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required value="<?php echo htmlspecialchars($_POST['productName'] ?? ''); ?>" />
                    </label>
                </div>
                <div>
                    <label class="block font-semibold text-gray-700">Product Type
                        <select name="productType" class="form-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Type</option>
                            <option value="Ring" <?php echo (isset($_POST['productType']) && $_POST['productType'] === 'Ring') ? 'selected' : ''; ?>>Ring</option>
                            <option value="Earring" <?php echo (isset($_POST['productType']) && $_POST['productType'] === 'Earring') ? 'selected' : ''; ?>>Earring</option>
                            <option value="Charm" <?php echo (isset($_POST['productType']) && $_POST['productType'] === 'Charm') ? 'selected' : ''; ?>>Charm</option>
                            <option value="Bracelet" <?php echo (isset($_POST['productType']) && $_POST['productType'] === 'Bracelet') ? 'selected' : ''; ?>>Bracelet</option>
                        </select>
                    </label>
                </div>
                <div>
                    <label class="block font-semibold text-gray-700">Price
                        <input type="number" name="customPrice" step="0.01" class="form-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required value="<?php echo htmlspecialchars($_POST['customPrice'] ?? ''); ?>" />
                    </label>
                </div>
                <div>
                    <label class="block font-semibold text-gray-700">Stock
                        <input type="number" name="stock" min="1" class="form-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required value="<?php echo htmlspecialchars($_POST['stock'] ?? '1'); ?>" />
                    </label>
                </div>
                <div class="md:col-span-2">
                    <label class="block font-semibold text-gray-700">Description
                        <textarea name="customDescription" class="form-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required><?php echo htmlspecialchars($_POST['customDescription'] ?? ''); ?></textarea>
                    </label>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <a href="admin-inventory.php" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition">Cancel</a>
                <button type="submit" class="px-8 py-2 bg-gradient-to-r from-blue-500 to-blue-700 text-white font-bold rounded-lg shadow-lg hover:from-blue-600 hover:to-blue-800 text-lg transition">Add Custom Order</button>
            </div>
        </form>
    </div>
</main>
<script>
    lucide.createIcons();
    // Image preview and remove logic
    let addImageInput = document.getElementById("add_itemImage");
    const addImagePreview = document.getElementById("addImagePreview");
    const addImagePreviewContainer = document.getElementById("addImagePreviewContainer");
    const removeAddImageBtn = document.getElementById("removeAddImageBtn");
    const addUploadBox = document.getElementById("addUploadBox");
    function attachAddImageInputHandler(input) {
        input.addEventListener("change", function () {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    addImagePreview.src = e.target.result;
                    addImagePreviewContainer.classList.remove("hidden");
                    addUploadBox.classList.add("hidden");
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                addImagePreviewContainer.classList.add("hidden");
                addImagePreview.src = "";
                addUploadBox.classList.remove("hidden");
            }
        });
    }
    attachAddImageInputHandler(addImageInput);
    removeAddImageBtn.addEventListener("click", function () {
        // Remove and recreate the file input to fully reset it
        const newInput = addImageInput.cloneNode();
        addImageInput.parentNode.replaceChild(newInput, addImageInput);
        addImageInput = newInput; // Update the variable
        attachAddImageInputHandler(addImageInput);
        addImagePreviewContainer.classList.add("hidden");
        addImagePreview.src = "";
        addUploadBox.classList.remove("hidden");
    });
</script>
</body>
</html> 