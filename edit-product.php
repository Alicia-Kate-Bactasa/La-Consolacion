<?php
session_start();
require_once 'admin-check.php';
require_once 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch current admin's level
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT u.role, a.admin_level FROM users u LEFT JOIN admin a ON u.id = a.user_id WHERE u.id = ?');
$stmt->execute([$user_id]);
$currentAdmin = $stmt->fetch();

// Check if user has admin access
if (!is_admin($currentAdmin)) {
    header('Location: login.php');
    exit();
}

$errors = [];
$product = null;
if (!isset($_GET['id']) && !isset($_POST['id'])) {
    header('Location: admin-inventory.php');
    exit();
}
$id = isset($_GET['id']) ? intval($_GET['id']) : intval($_POST['id']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stock = intval($_POST['itemStock']);
    
    // Admin-1 can only update stock, SuperAdmin can update everything
    if (is_admin($currentAdmin, 2)) {
        $name = trim($_POST['itemName']);
        $price = floatval($_POST['itemPrice']);
        $type = trim($_POST['itemType']);
        $material = trim($_POST['itemMaterial']);
        $description = trim($_POST['itemDescription']);
        $imagePath = $_POST['currentImage'];
        
        if (isset($_FILES['itemImage']) && $_FILES['itemImage']['error'] === UPLOAD_ERR_OK) {
            $imgTmp = $_FILES['itemImage']['tmp_name'];
            $imgName = basename($_FILES['itemImage']['name']);
            $imgExt = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($imgExt, $allowed)) {
                $newName = uniqid('prod_', true) . '.' . $imgExt;
                $targetDir = 'Image/Product/';
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
    }
    
    if (empty($errors)) {
        try {
            // Fetch the original product before updating
            $stmt = $pdo->prepare('SELECT * FROM products WHERE id=?');
            $stmt->execute([$id]);
            $original = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (is_admin($currentAdmin, 2)) {
                // SuperAdmin can update all fields
                $stmt = $pdo->prepare('UPDATE products SET image=?, name=?, price=?, stock=?, type=?, material=?, description=? WHERE id=?');
                $success = $stmt->execute([$imagePath, $name, $price, $stock, $type, $material, $description, $id]);
            } else {
                // Admin-1 can only update stock
                $stmt = $pdo->prepare('UPDATE products SET stock=? WHERE id=?');
                $success = $stmt->execute([$stock, $id]);
            }
            
            if ($success) {
                $changes = [];
                if (is_admin($currentAdmin, 2)) {
                    // Log all changes for SuperAdmin
                    if ($original['name'] !== $name) {
                        $changes[] = "Name changed from '{$original['name']}' to '{$name}'";
                    }
                    if ($original['price'] != $price) {
                        $changes[] = "Price changed from Php {$original['price']} to Php {$price}";
                    }
                    if ($original['stock'] != $stock) {
                        $changes[] = "Stock changed from {$original['stock']} to {$stock}";
                    }
                    if ($original['type'] !== $type) {
                        $changes[] = "Type changed from '{$original['type']}' to '{$type}'";
                    }
                    if ($original['material'] !== $material) {
                        $changes[] = "Material changed from '{$original['material']}' to '{$material}'";
                    }
                    if ($original['description'] !== $description) {
                        $changes[] = "Description changed";
                    }
                    if ($original['image'] !== $imagePath) {
                        $changes[] = "Image changed";
                    }
                } else {
                    // Log only stock changes for Admin-1
                    if ($original['stock'] != $stock) {
                        $changes[] = "Stock changed from {$original['stock']} to {$stock}";
                    }
                }
                
                $details = !empty($changes) ? implode('; ', $changes) : "Edited product: {$original['name']} (no changes)";
                log_action($pdo, $_SESSION['username'], 'Edit Product', $details);
                header('Location: admin-inventory.php');
                exit();
            } else {
                $errors[] = 'Update failed.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
} else {
    try {
        $stmt = $pdo->prepare('SELECT * FROM products WHERE id=?');
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        
        if (!$product) {
            header('Location: admin-inventory.php');
            exit();
        }
    } catch (PDOException $e) {
        header('Location: admin-inventory.php');
        exit();
    }
} ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Product - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
      body {
        background: linear-gradient(
          135deg,
          #f8fafc 0%,
          #e0f2fe 50%,
          #e8eaf6 100%
        );
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
    <?php include 'layout-header.php'; ?>
    <main class="flex-1 p-8 ml-72 h-[calc(100vh-80px)]">
      <div
        class="w-full max-w-xl mx-auto bg-white rounded-2xl shadow-xl p-8 mt-10"
      >
        <div class="flex items-center gap-3 mb-6">
          <span
            class="inline-flex items-center justify-center bg-blue-100 rounded-full p-2 text-3xl"
            ><i data-lucide="edit-3"></i
          ></span>
          <h1 class="text-2xl font-bold tracking-wide m-0">Edit Product</h1>
        </div>
        <?php if (!empty($errors)): ?>
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
          <?php echo implode('<br>', $errors); ?>
        </div>
        <?php endif; ?>
        <form
          action="edit-product.php"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-4"
        >
          <input
            type="hidden"
            name="id"
            value="<?php echo htmlspecialchars($id); ?>"
          />
          <input
            type="hidden"
            name="currentImage"
            value="<?php echo htmlspecialchars($product['image']); ?>"
          />
          <div class="flex flex-col items-center">
            <?php if (!is_admin($currentAdmin, 2)): ?>
            <div class="w-full mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
              <div class="flex items-center gap-2">
                <i data-lucide="info" class="w-5 h-5 text-blue-600"></i>
                <span class="text-blue-800 font-medium">Admin-1 Mode: You can only edit stock quantity</span>
              </div>
            </div>
            <?php endif; ?>
            <div
              id="editImagePreviewContainer"
              class="relative mb-2"
              style="<?php echo !empty($product['image']) ? '' : 'display: none;'; ?>"
            >
              <img
                id="editImagePreview"
                src="<?php echo !empty($product['image']) ? 'Image/Product/' . htmlspecialchars($product['image']) : ''; ?>"
                alt="Current Image"
                class="w-32 h-32 object-cover rounded-xl mb-2 shadow"
              />
              <?php if (is_admin($currentAdmin, 2)): ?>
              <button
                type="button"
                id="removeEditImageBtn"
                class="absolute -top-3 -right-3 w-7 h-7 flex items-center justify-center bg-red-500 text-white rounded-full shadow hover:bg-red-600 transition border-2 border-white z-10"
                title="Remove image"
              >
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" class="block" xmlns="http://www.w3.org/2000/svg">
                  <line x1="3" y1="3" x2="9" y2="9" stroke="white" stroke-width="2" stroke-linecap="round"/>
                  <line x1="9" y1="3" x2="3" y2="9" stroke="white" stroke-width="2" stroke-linecap="round"/>
                </svg>
              </button>
              <?php endif; ?>
            </div>
            <?php if (is_admin($currentAdmin, 2)): ?>
            <label
              id="editUploadBox"
              class="w-full flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-lg p-8 bg-gray-50 text-gray-600 cursor-pointer transition hover:bg-gray-100 relative"
              style="<?php echo !empty($product['image']) ? 'display: none;' : ''; ?>"
            >
              <span class="text-4xl mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-10 h-10 mx-auto text-gray-400">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16v-8m0 0l-4 4m4-4l4 4M20 16.58A5.978 5.978 0 0018 16c-1.306 0-2.417.835-2.83 2H8.83C8.417 16.835 7.306 16 6 16a5.978 5.978 0 00-2 .58M16 16.58V16a4 4 0 00-8 0v.58" />
                </svg>
              </span>
              <span class="font-medium text-base mb-1">Click to upload or drag and drop</span>
              <span class="text-xs text-gray-400 mb-2">PNG, JPG, GIF up to 10MB</span>
              <input
                type="file"
                name="itemImage"
                id="edit_itemImage"
                accept="image/*"
                class="absolute inset-0 opacity-0 cursor-pointer"
              />
            </label>
            <?php endif; ?>
            <div style="font-size:11px;color:#64748b;text-align:left;margin-top:4px;">For best results, upload a 1x1 (square) image.</div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block font-semibold text-gray-700"
                >Product Name
                <input
                  type="text"
                  name="itemName"
                  value="<?php echo htmlspecialchars($product['name']); ?>"
                  class="form-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 <?php echo !is_admin($currentAdmin, 2) ? 'bg-gray-100 cursor-not-allowed' : ''; ?>"
                  <?php echo !is_admin($currentAdmin, 2) ? 'disabled' : ''; ?>
                  required
                />
              </label>
            </div>
            <div>
              <label class="block font-semibold text-gray-700"
                >Price
                <input
                  type="number"
                  name="itemPrice"
                  step="0.01"
                  value="<?php echo htmlspecialchars($product['price']); ?>"
                  class="form-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 <?php echo !is_admin($currentAdmin, 2) ? 'bg-gray-100 cursor-not-allowed' : ''; ?>"
                  <?php echo !is_admin($currentAdmin, 2) ? 'disabled' : ''; ?>
                  required
                />
              </label>
            </div>
            <div>
              <label class="block font-semibold text-gray-700"
                >Stock
                <input
                  type="number"
                  name="itemStock"
                  min="0"
                  value="<?php echo htmlspecialchars($product['stock']); ?>"
                  class="form-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  required
                />
              </label>
            </div>
            <div>
              <label class="block font-semibold text-gray-700">Product Type
                <select name="itemType" class="form-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 <?php echo !is_admin($currentAdmin, 2) ? 'bg-gray-100 cursor-not-allowed' : ''; ?>" <?php echo !is_admin($currentAdmin, 2) ? 'disabled' : ''; ?> required>
                    <option value="">Select type</option>
                    <option value="ring" <?php if($product['type']==='ring') echo 'selected'; ?>>Ring</option>
                    <option value="charm" <?php if($product['type']==='charm') echo 'selected'; ?>>Charm</option>
                    <option value="earring" <?php if($product['type']==='earring') echo 'selected'; ?>>Earring</option>
                    <option value="bracelet" <?php if($product['type']==='bracelet') echo 'selected'; ?>>Bracelet</option>
                </select>
              </label>
            </div>
            <div class="md:col-span-2">
              <label class="block font-semibold text-gray-700"
                >Material
                <input
                  type="text"
                  name="itemMaterial"
                  value="<?php echo htmlspecialchars($product['material']); ?>"
                  class="form-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 <?php echo !is_admin($currentAdmin, 2) ? 'bg-gray-100 cursor-not-allowed' : ''; ?>"
                  <?php echo !is_admin($currentAdmin, 2) ? 'disabled' : ''; ?>
                />
              </label>
            </div>
            <div class="md:col-span-2">
              <label class="block font-semibold text-gray-700"
                >Description
                <textarea
                  name="itemDescription"
                  class="form-input mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 <?php echo !is_admin($currentAdmin, 2) ? 'bg-gray-100 cursor-not-allowed' : ''; ?>"
                  <?php echo !is_admin($currentAdmin, 2) ? 'disabled' : ''; ?>
                >
<?php echo htmlspecialchars($product['description']); ?></textarea
                >
              </input>
            </div>
          </div>
          

          <div class="flex justify-end gap-3 mt-4">
            <a
              href="admin-inventory.php"
              class="px-6 py-2 border border-gray-300 text-gray-700 bg-white rounded-md font-semibold hover:bg-gray-100 transition"
              >Cancel</a
            >
            <button
              type="submit"
              class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-400 text-white rounded-md font-semibold shadow hover:from-blue-700 hover:to-blue-500 transition"
            >
              Save Changes
            </button>
          </div>
        </form>
      </div>
    </main>
    <script>
      lucide.createIcons();
      
      // Real-time role verification - check every 30 seconds
      function checkRoleStatus() {
        fetch('check-role-status.php')
          .then(response => response.json())
          .then(data => {
            if (!data.isAdmin) {
              // Role has changed - redirect to login
              alert('Your role has been changed. You will be redirected to login.');
              window.location.href = 'login.php?error=role_changed';
            }
          })
          .catch(error => {
            console.error('Role check failed:', error);
          });
      }
      
      // Start periodic role checking
      setInterval(checkRoleStatus, 30000); // Check every 30 seconds
      
      // Edit Product image preview logic
      const editImagePreview = document.getElementById("editImagePreview");
      const editImagePreviewContainer = document.getElementById(
        "editImagePreviewContainer"
      );
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
        newInput.id = "edit_itemImage";
        editImageInput = newInput;
        attachEditImageInputHandler(editImageInput);
        editImagePreviewContainer.style.display = "none";
        editImagePreview.src = "";
        editUploadBox.style.display = "";
      });
    </script>
  </body>
</html>
