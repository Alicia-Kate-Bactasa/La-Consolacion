<?php
session_start();
require_once 'check.php';
require_once '../database/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Fetch current admin's level
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT u.role, a.admin_level FROM users u LEFT JOIN admin a ON u.id = a.user_id WHERE u.id = ?');
$stmt->execute([$user_id]);
$currentAdmin = $stmt->fetch();

// Check if user has admin access
if (!is_admin($currentAdmin)) {
    header('Location: ../login.php');
    exit();
}

$errors = [];
$product = null;
$customOrder = null;

if (!isset($_GET['id']) && !isset($_POST['id'])) {
    header('Location: inventory.php');
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : intval($_POST['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customerName']);
    $email = trim($_POST['customerEmail']);
    $description = trim($_POST['customDescription']);
    $product_name = trim($_POST['productName']);
    $price = isset($_POST['customPrice']) ? floatval($_POST['customPrice']) : null;
    $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 1;
    $custom_status = trim($_POST['customStatus']);
    $imagePath = $_POST['currentImage'];
    
    if (isset($_FILES['customImage']) && $_FILES['customImage']['error'] === UPLOAD_ERR_OK) {
        $imgTmp = $_FILES['customImage']['tmp_name'];
        $imgName = basename($_FILES['customImage']['name']);
        $imgExt = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($imgExt, $allowed)) {
            $prefixName = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($customer_name));
            $newName = 'custom_' . $prefixName . '_' . uniqid() . '.' . $imgExt;
            $targetDir = '../Image/product-add/';
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
    if ($customer_name === '' || $email === '' || $description === '' || $product_name === '' || $price === null) {
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
            
            // Update products table
            $stmt = $pdo->prepare('UPDATE products SET name=?, price=?, stock=?, description=?, image=? WHERE id=?');
            $success = $stmt->execute([$product_name, $price, $stock, $description, $imagePath, $id]);
            
            if ($success) {
                // Update custom_orders table
                $stmt = $pdo->prepare('UPDATE custom_orders SET customer_name=?, email=?, description=?, status=? WHERE id=?');
                $customSuccess = $stmt->execute([$customer_name, $email, $description, $custom_status, $id]);
                
                if ($customSuccess) {
                    $pdo->commit();
                    $details = "Updated custom order for: $customer_name | Description: $description";
                    if (function_exists('log_action')) log_action($pdo, $_SESSION['username'], 'Edit Custom Order', $details);
                    header('Location: inventory.php');
                    exit();
                } else {
                    $pdo->rollBack();
                    $errors[] = 'Failed to update custom order details.';
                }
            } else {
                $pdo->rollBack();
                $errors[] = 'Failed to update product details.';
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
} else {
    try {
        // Fetch product and custom order data
        $stmt = $pdo->prepare('SELECT * FROM products WHERE id=? AND type="custom"');
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        
        if ($product) {
            $stmt = $pdo->prepare('SELECT * FROM custom_orders WHERE id=?');
            $stmt->execute([$id]);
            $customOrder = $stmt->fetch();
        }
        
        if (!$product || !$customOrder) {
            header('Location: inventory.php');
            exit();
        }
    } catch (PDOException $e) {
        header('Location: inventory.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Custom Order - Admin</title>
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
    <?php include '../components/layout-header.php'; ?>
<main class="flex-1 p-4 lg:p-8 ml-0 lg:ml-72 h-[calc(100vh-80px)]">
    <div class="w-full max-w-xl mx-auto bg-white rounded-2xl shadow-xl p-8 mt-10">
        <div class="flex items-center gap-3 mb-6">
            <span class="inline-flex items-center justify-center bg-purple-100 rounded-full p-2 text-3xl"><i data-lucide="edit-3"></i></span>
            <h1 class="text-2xl font-bold tracking-wide m-0">Edit Custom Order</h1>
        </div>
        <?php if (!empty($errors)): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded"> <?php echo implode('<br>', $errors); ?> </div>
        <?php endif; ?>
        <form action="edit-custom.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>" />
            <input type="hidden" name="currentImage" value="<?php echo htmlspecialchars($product['image']); ?>" />
            
            <div class="flex flex-col items-center">
                <div id="editImagePreviewContainer" class="relative mb-2" style="<?php echo !empty($product['image']) ? '' : 'display: none;'; ?>">
                    <img id="editImagePreview" src="<?php echo !empty($product['image']) ? '../Image/product-add/' . htmlspecialchars($product['image']) : ''; ?>" alt="Current Image" class="w-32 h-32 object-cover rounded-xl mb-2 shadow" />
                    <button type="button" id="removeEditImageBtn" class="absolute -top-3 -right-3 w-7 h-7 flex items-center justify-center bg-red-500 text-white rounded-full shadow hover:bg-red-600 transition border-2 border-white z-10" title="Remove image" aria-label="Remove image">
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none" class="block" xmlns="http://www.w3.org/2000/svg">
                          <line x1="3" y1="3" x2="9" y2="9" stroke="white" stroke-width="2" stroke-linecap="round"/>
                          <line x1="9" y1="3" x2="3" y2="9" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <label id="editUploadBox" class="w-full flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-lg p-8 bg-gray-50 text-gray-600 cursor-pointer transition hover:bg-gray-100 relative" style="<?php echo !empty($product['image']) ? 'display: none;' : ''; ?>">
                    <span class="text-4xl mb-2">
                        <i data-lucide="upload-cloud" class="w-12 h-12 mx-auto text-gray-400"></i>
                    </span>
                    <span class="font-medium text-base mb-1">Click to upload or drag and drop</span>
                    <span class="text-xs text-gray-400 mb-2">PNG, JPG, GIF up to 10MB</span>
                    <input type="file" name="customImage" id="edit_itemImage" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" />
                </label>
                <div style="font-size:11px;color:#64748b;text-align:left;margin-top:4px;">For best results, upload a 1x1 (square) image.</div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-gray-700">Customer Name
                        <input type="text" name="customerName" class="form-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required value="<?php echo htmlspecialchars($customOrder['customer_name']); ?>" />
                    </label>
                </div>
                <div>
                    <label class="block font-semibold text-gray-700">Email
                        <input type="email" name="customerEmail" class="form-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required value="<?php echo htmlspecialchars($customOrder['email']); ?>" />
                    </label>
                </div>
                <div class="md:col-span-2">
                    <label class="block font-semibold text-gray-700">Product Name
                        <input type="text" name="productName" class="form-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required value="<?php echo htmlspecialchars($product['name']); ?>" />
                    </label>
                </div>
                <div>
                    <label class="block font-semibold text-gray-700">Price
                        <input type="number" name="customPrice" step="0.01" class="form-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required value="<?php echo htmlspecialchars($product['price']); ?>" />
                    </label>
                </div>
                <div>
                    <label class="block font-semibold text-gray-700">Stock
                        <input type="number" name="stock" min="1" class="form-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required value="<?php echo htmlspecialchars($product['stock']); ?>" />
                    </label>
                </div>
                <div class="md:col-span-2">
                    <label class="block font-semibold text-gray-700">Custom Order Status
                        <select name="customStatus" class="form-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="pending" <?php if($customOrder['status']==='pending') echo 'selected'; ?>>Pending</option>
                            <option value="processing" <?php if($customOrder['status']==='processing') echo 'selected'; ?>>Processing</option>
                            <option value="completed" <?php if($customOrder['status']==='completed') echo 'selected'; ?>>Completed</option>
                            <option value="cancelled" <?php if($customOrder['status']==='cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                    </label>
                </div>
                <div class="md:col-span-2">
                    <label class="block font-semibold text-gray-700">Description
                        <textarea name="customDescription" class="form-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required><?php echo htmlspecialchars($customOrder['description']); ?></textarea>
                    </label>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <a href="inventory.php" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition">Cancel</a>
                <button type="submit" class="px-8 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-lg text-lg transition">Update Custom Order</button>
            </div>
        </form>
    </div>
</main>
<script>
    lucide.createIcons();
    
    // Real-time role verification - check every 30 seconds
    function checkRoleStatus() {
        fetch('../api/check-role-status.php')
          .then(response => response.json())
          .then(data => {
            if (!data.isAdmin) {
              // Role has changed - redirect to login
              alert('Your role has been changed. You will be redirected to login.');
              window.location.href = '../login.php?error=role_changed';
            }
          })
          .catch(error => {
            console.error('Role check failed:', error);
          });
    }
    
    // Start periodic role checking
    setInterval(checkRoleStatus, 30000); // Check every 30 seconds
    
    // Edit Custom Order image preview logic
    const editImagePreview = document.getElementById("editImagePreview");
    const editImagePreviewContainer = document.getElementById("editImagePreviewContainer");
    const removeEditImageBtn = document.getElementById("removeEditImageBtn");
    const editUploadBox = document.getElementById("editUploadBox");
    let editImageInput = document.getElementById("edit_itemImage");
    
    function attachEditImageInputHandler(input) {
        input.addEventListener("change", function () {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    editImagePreview.src = e.target.result;
                    editImagePreviewContainer.style.display = "";
                    editUploadBox.style.display = "none";
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                if (editImagePreview.src && !editImagePreview.src.endsWith("/")) {
                    editImagePreviewContainer.style.display = "";
                    editUploadBox.style.display = "none";
                } else {
                    editImagePreviewContainer.style.display = "none";
                    editImagePreview.src = "";
                    editUploadBox.style.display = "";
                }
            }
        });
    }
    
    attachEditImageInputHandler(editImageInput);
    
    removeEditImageBtn.addEventListener("click", function () {
        // Remove and recreate the file input to fully reset it
        const newInput = editImageInput.cloneNode();
        editImageInput.parentNode.replaceChild(newInput, editImageInput);
        editImageInput = newInput; // Update the variable
        attachEditImageInputHandler(editImageInput);
        editImagePreviewContainer.style.display = "none";
        editImagePreview.src = "";
        editUploadBox.style.display = "";
    });
</script>
</body>
</html> 