<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Inventory Dashboard - La Consolacion Jewelry</title>
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
<?php
session_start();
require_once '../database/db.php';

// Function to check if user is admin with required level
function is_admin($currentAdmin, $required_level = 1) {
    if (!$currentAdmin || !is_array($currentAdmin)) {
        return false;
    }
    return isset($currentAdmin['admin_level']) && $currentAdmin['admin_level'] >= $required_level;
}

// Fetch current admin info if logged in
$currentAdmin = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare('SELECT u.*, a.admin_level FROM users u LEFT JOIN admin a ON u.id = a.user_id WHERE u.id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $currentAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle Premade Product Submission
if (isset($_POST['add_premade'])) {
    $name = $_POST['premade_name'];
    $price = $_POST['premade_price'];
    $stock = $_POST['premade_stock'];
    $type = $_POST['premade_type'];
    $material = $_POST['premade_material'];
    $description = $_POST['premade_description'];
    $image = $_POST['premade_image']; // For simplicity, just a filename
    $stmt = $pdo->prepare("INSERT INTO premade_products (name, price, stock, type, material, description, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $price, $stock, $type, $material, $description, $image]);
    $success = 'Premade product added!';
}

// Handle Custom Product Submission
if (isset($_POST['add_custom'])) {
    $customer_name = $_POST['custom_customer_name'];
    $email = $_POST['custom_email'];
    $phone = $_POST['custom_phone'];
    $description = $_POST['custom_description'];
    $admin_notes = $_POST['custom_admin_notes'];
    $stmt = $pdo->prepare("INSERT INTO custom_orders (customer_name, email, phone, description, admin_notes) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$customer_name, $email, $phone, $description, $admin_notes]);
    $custom_id = $pdo->lastInsertId();
    header("Location: ../custom-preview.php?id=$custom_id");
    exit();
}

// Fetch all products from the products table (excluding deleted)
$products = [];
try {
    $stmt = $pdo->query("SELECT * FROM products WHERE deleted = 0 ORDER BY created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Optionally handle error
    $products = [];
}
?>
<?php include '../components/layout-header.php'; ?>
<!-- Main Content: Jewelry Inventory as Cards -->
<main class="flex-1 p-4 lg:p-8 ml-0 lg:ml-72 overflow-y-auto h-[calc(100vh-80px)]">
  <h1 class="text-4xl font-bold text-gray-800 mb-2" style="letter-spacing:-0.01em;">Inventory</h1>
  <p class="text-gray-600 text-lg mb-6">Manage and track all products in your inventory</p>
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div class="flex gap-2 w-full sm:w-auto items-center">
    <input
      id="inventorySearch"
      type="text"
      placeholder="Search products..."
      class="w-full sm:w-96 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 text-lg shadow-sm"
      style="max-width: 100%;"
    />
      <div id="productTypeFilterBtns" class="flex gap-2 ml-2">
          <button type="button" class="product-type-filter-btn px-4 py-2 rounded-lg border border-gray-300 bg-blue-500 text-white font-semibold shadow-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" data-product-type="premade">Premade</button>
          <button type="button" class="product-type-filter-btn px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 font-semibold shadow-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" data-product-type="custom">Custom</button>
      </div>
      <div id="typeFilterBtns" class="flex gap-2 ml-2">
        <button type="button" class="type-filter-btn px-4 py-2 rounded-lg border border-gray-300 bg-blue-500 text-white font-semibold shadow-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" data-type="">All</button>
        <button type="button" class="type-filter-btn px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 font-semibold shadow-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" data-type="ring">Ring</button>
        <button type="button" class="type-filter-btn px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 font-semibold shadow-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" data-type="charm">Charm</button>
        <button type="button" class="type-filter-btn px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 font-semibold shadow-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" data-type="earring">Earring</button>
        <button type="button" class="type-filter-btn px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 font-semibold shadow-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" data-type="bracelet">Bracelet</button>
      </div>
    </div>
    <?php if (is_admin($currentAdmin, 2)): ?>
    <div class="flex gap-4">
      <a
        href="add-product.php"
        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-emerald-600 text-white font-bold shadow-lg hover:bg-emerald-700 transition text-lg"
      >
        <i data-lucide="package" class="w-6 h-6"></i>
        Add Premade
      </a>
      <a
        href="add-custom.php"
        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-blue-600 text-white font-bold shadow-lg hover:bg-blue-700 transition text-lg"
      >
        <i data-lucide="settings" class="w-6 h-6"></i>
        Add Custom
      </a>
    </div>
    <?php endif; ?>
  </div>
  <?php if (isset($_GET['deleted'])): ?>
  <div
    id="notif"
    class="fixed top-6 right-6 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity duration-500"
  >
    Product removed from inventory successfully!
  </div>
  <script>
    setTimeout(() => {
      const notif = document.getElementById("notif");
      if (notif) notif.style.opacity = "0";
    }, 2000);
  </script>
  <?php endif; ?>
  
  <?php if (isset($_GET['error']) && $_GET['error'] === 'insufficient_permissions'): ?>
  <div
    id="errorNotif"
    class="fixed top-6 right-6 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity duration-500"
  >
    Insufficient permissions to delete products. Only SuperAdmin can delete products.
  </div>
  <script>
    setTimeout(() => {
      const notif = document.getElementById("errorNotif");
      if (notif) notif.style.opacity = "0";
    }, 3000);
  </script>
  <?php endif; ?>
  
  <?php if (isset($_GET['error']) && $_GET['error'] === 'delete_failed'): ?>
  <div
    id="deleteErrorNotif"
    class="fixed top-6 right-6 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity duration-500"
  >
    <?php echo htmlspecialchars($_GET['message'] ?? 'Failed to delete product'); ?>
  </div>
  <script>
    setTimeout(() => {
      const notif = document.getElementById("deleteErrorNotif");
      if (notif) notif.style.opacity = "0";
    }, 5000);
  </script>
  <?php endif; ?>
  
  <?php if (isset($_GET['custom_order_sent']) && $_GET['custom_order_sent'] === '1'): ?>
  <div
    id="customOrderNotif"
    class="fixed top-6 right-6 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity duration-500"
  >
    <div class="flex items-center gap-2">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
      </svg>
      <span>Custom order sent successfully to <?php echo htmlspecialchars($_GET['customer_email'] ?? 'customer'); ?>!</span>
    </div>
  </div>
  <script>
    setTimeout(() => {
      const notif = document.getElementById("customOrderNotif");
      if (notif) notif.style.opacity = "0";
    }, 5000);
  </script>
  <?php endif; ?>
  <div class="bg-white p-8 rounded-3xl shadow-2xl border border-blue-100">
    <div id="inventoryGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
      <?php if (empty($products)): ?>
      <div class="col-span-full text-center text-gray-400 text-2xl py-24">
        No products are added.
      </div>
      <?php else: ?>
      <?php foreach ($products as $product): ?>
      <div class="glass bg-white/60 backdrop-blur-md border border-blue-100 rounded-2xl shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 p-6 flex flex-col h-full relative overflow-hidden" data-type="<?php echo htmlspecialchars(strtolower($product['type'])); ?>" data-product-type="<?php echo (in_array(strtolower($product['type']), ['ring','charm','earring','bracelet'])) ? 'premade' : 'custom'; ?>">
        <div class="absolute inset-0 pointer-events-none rounded-2xl" style="background: radial-gradient(circle at 70% 20%, #a5b4fc22 0%, #f0f9ff00 80%);"></div>
        <div class="w-full aspect-square flex items-center justify-center bg-gradient-to-br from-blue-100 to-blue-50 border-b border-blue-200 mb-5 rounded-xl overflow-hidden shadow-lg">
          <img
            src="<?php echo (strtolower($product['type']) === 'custom') ? '../Image/product-add/' : '../Image/Product/'; ?><?php echo htmlspecialchars($product['image']); ?>"
            alt="<?php echo htmlspecialchars($product['name']); ?>"
            class="object-cover w-full h-full border-4 border-white rounded-xl shadow-md transition-transform duration-200 hover:scale-105"
            style="display: block;"
          />
        </div>
        <h3 class="text-xl font-extrabold text-blue-900 mb-1 truncate" title="<?php echo htmlspecialchars($product['name']); ?>">
          <?php echo htmlspecialchars($product['name']); ?>
        </h3>
        <div class="text-lg font-bold text-gray-900 mb-2">₱<?php echo number_format($product['price'], 2); ?></div>
        <div class="flex flex-wrap gap-2 mb-2">
          <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-blue-200 to-blue-100 text-blue-800 shadow-sm">
            <?php echo htmlspecialchars(ucfirst($product['type'])); ?>
          </span>
          <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold <?php echo ($product['stock'] > 0) ? 'bg-gradient-to-r from-emerald-200 to-emerald-100 text-emerald-800' : 'bg-gradient-to-r from-red-200 to-red-100 text-red-800'; ?> shadow-sm">
            <?php echo ($product['stock'] > 0) ? 'In Stock' : 'Out of Stock'; ?>
          </span>
          <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 shadow-sm">
            Stock: <?php echo htmlspecialchars($product['stock']); ?>
          </span>
        </div>
        <div class="flex justify-between items-center mt-auto pt-4 gap-3">
          <button
            type="button"
            class="editProductBtn flex-1 px-5 py-2.5 rounded-lg text-white text-base font-bold bg-blue-600 hover:bg-blue-700 shadow-md hover:shadow-lg transition flex items-center justify-center gap-2"
            onclick="window.location.href='<?php echo (strtolower($product['type']) === 'custom') ? 'edit-custom.php' : 'edit-product.php'; ?>?id=<?php echo $product['id']; ?>'"
          >
            <i data-lucide="edit-3" class="w-5 h-5"></i> Edit
          </button>
                    <?php if (is_admin($currentAdmin, 2)): ?>
          <form method="POST" action="delete-product.php" class="delete-product-form flex-1">
            <input type="hidden" name="id" value="<?php echo $product['id']; ?>" />
            <button
              type="button"
              class="deleteProductBtn w-full px-5 py-2.5 rounded-lg text-base font-bold bg-red-600 hover:bg-red-700 text-white shadow-md hover:shadow-lg transition flex items-center justify-center gap-2"
            >
              <i data-lucide="trash-2" class="w-5 h-5"></i> Delete
            </button>
          </form>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
      <div id="noProductsFound" class="col-span-full flex flex-col items-center justify-center text-center text-gray-400 text-2xl py-24 hidden">
        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <i data-lucide="package" class="w-12 h-12 text-gray-400"></i>
        </div>
        <h3 id="noProductsHeadline" class="text-2xl font-semibold text-gray-600 mb-2">No products found.</h3>
        <p class="text-gray-500 text-base">Products will appear here when you add them.</p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</main>
    <!-- Custom Delete Confirmation Modal -->
    <div
      id="deleteModal"
      class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center"
    >
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Confirm Delete</h3>
        <p class="text-gray-600 mb-6">
          Are you sure you want to delete
          <span id="deleteProductName" class="font-semibold text-red-600"></span
          >?
        </p>
        <div class="flex justify-end gap-3">
          <button
            id="cancelDelete"
            class="px-4 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 transition"
          >
            Cancel
          </button>
          <button
            id="confirmDelete"
            class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition"
          >
            Delete
          </button>
        </div>
      </div>
    </div>
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

      // Custom delete confirmation modal
      const deleteModal = document.getElementById("deleteModal");
      const deleteProductName = document.getElementById("deleteProductName");
      const cancelDelete = document.getElementById("cancelDelete");
      const confirmDelete = document.getElementById("confirmDelete");
      let currentForm = null;

      document.querySelectorAll(".deleteProductBtn").forEach((btn) => {
        btn.addEventListener("click", function (e) {
          e.preventDefault();
          const productName =
            this.closest(".bg-white").querySelector("h3").textContent;
          currentForm = this.closest("form");
          deleteProductName.textContent = productName;
          deleteModal.classList.remove("hidden");
        });
      });

      cancelDelete.addEventListener("click", function () {
        deleteModal.classList.add("hidden");
      });

      confirmDelete.addEventListener("click", function () {
        if (currentForm) {
          currentForm.submit();
        }
      });

      // Close modal when clicking outside
      deleteModal.addEventListener("click", function (e) {
        if (e.target === deleteModal) {
          deleteModal.classList.add("hidden");
        }
      });

      // Profile dropdown functionality
      const profileDropdownBtn = document.getElementById('profileDropdownBtn');
      const profileDropdown = document.getElementById('profileDropdown');
      
      if (profileDropdownBtn && profileDropdown) {
        profileDropdownBtn.addEventListener('click', function(e) {
          e.stopPropagation();
          profileDropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
          if (!profileDropdownBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
            profileDropdown.classList.add('hidden');
          }
        });
      }

      // Inventory search and filter
      const searchInput = document.getElementById('inventorySearch');
      const grid = document.getElementById('inventoryGrid');
      let activeType = '';
      function filterInventory() {
        const q = searchInput.value.trim().toLowerCase();
        const type = activeType;
        let anyVisible = false;
        let typeCount = 0;
        let totalTypeCount = 0;
        grid.querySelectorAll('[data-type]').forEach(card => {
          const cardType = card.getAttribute('data-type');
          if (type && cardType === type) totalTypeCount++;
        });
        grid.querySelectorAll('[data-type]').forEach(card => {
            const text = card.textContent.toLowerCase();
          const cardType = card.getAttribute('data-type');
          const matchesType = !type || cardType === type;
          const matchesSearch = text.includes(q);
          const show = matchesType && matchesSearch;
          card.style.display = show ? '' : 'none';
          if (matchesType) typeCount++;
          if (show) anyVisible = true;
        });
        const noProductsMsg = document.getElementById('noProductsFound');
        const noProductsHeadline = document.getElementById('noProductsHeadline');
        if (noProductsMsg) {
          if (anyVisible) {
            noProductsMsg.style.display = 'none';
            } else {
            let msg = 'No products found.';
            if (type && q === '') {
              let typeLabel = type.charAt(0).toUpperCase() + type.slice(1).toLowerCase();
              if (typeLabel.toLowerCase() === 'ring') typeLabel = 'Rings';
              else if (typeLabel.toLowerCase() === 'charm') typeLabel = 'Charms';
              else if (typeLabel.toLowerCase() === 'earring') typeLabel = 'Earrings';
              else if (typeLabel.toLowerCase() === 'bracelet') typeLabel = 'Bracelets';
              else typeLabel += 's';
              msg = totalTypeCount === 0 ? `No ${typeLabel.toLowerCase()} to display.` : `No ${typeLabel.toLowerCase()} found.`;
            }
            if (noProductsHeadline) noProductsHeadline.textContent = msg;
            noProductsMsg.style.display = 'block';
            console.log('No products message shown:', msg);
            }
        }
      }
      if (searchInput && grid) {
        searchInput.addEventListener('input', filterInventory);
      }
      // Type filter buttons
      document.querySelectorAll('.type-filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          document.querySelectorAll('.type-filter-btn').forEach(b => b.classList.remove('bg-blue-500', 'text-white', 'active'));
          this.classList.add('bg-blue-500', 'text-white', 'active');
          activeType = this.dataset.type;
          filterInventory();
        });
      });

      // Product type filter
      let activeProductType = 'premade';
      function filterByProductType() {
        const productType = activeProductType; // 'premade' or 'custom'
        const type = activeType; // 'ring', 'charm', etc. or ''
        let anyVisible = false;
        grid.querySelectorAll('[data-product-type]').forEach(card => {
          const cardType = card.getAttribute('data-product-type'); // 'premade' or 'custom'
          const prodType = card.getAttribute('data-type'); // 'ring', 'charm', etc.
          let show = false;
          if (productType === 'premade') {
            if (type === '') {
              show = cardType === 'premade'; // Show all premade products only
            } else {
              show = cardType === 'premade' && prodType === type; // Show only premade of selected type
            }
          } else if (productType === 'custom') {
            show = cardType === 'custom';
          }
          card.style.display = show ? '' : 'none';
          if (show) anyVisible = true;
        });
        const noProductsMsg = document.getElementById('noProductsFound');
        if (noProductsMsg) {
          noProductsMsg.style.display = anyVisible ? 'none' : 'block';
        }
      }
      document.querySelectorAll('.product-type-filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          document.querySelectorAll('.product-type-filter-btn').forEach(b => b.classList.remove('bg-blue-500', 'text-white', 'active'));
          this.classList.add('bg-blue-500', 'text-white', 'active');
          activeProductType = this.dataset.productType;
          
          // Enable/disable type filter buttons based on selection
          const typeFilterBtns = document.querySelectorAll('.type-filter-btn');
          if (activeProductType === 'custom') {
            typeFilterBtns.forEach(btn => {
              btn.disabled = true;
              btn.classList.add('opacity-50', 'cursor-not-allowed');
            });
            // Reset type filter to 'All' when switching to custom
            activeType = '';
            document.querySelectorAll('.type-filter-btn').forEach(b => b.classList.remove('bg-blue-500', 'text-white', 'active'));
            document.querySelector('.type-filter-btn[data-type=""]').classList.add('bg-blue-500', 'text-white', 'active');
          } else {
            typeFilterBtns.forEach(btn => {
              btn.disabled = false;
              btn.classList.remove('opacity-50', 'cursor-not-allowed');
            });
          }
          
          filterByProductType();
        });
      });
      
      // Run filter on page load to show only premade products by default
      filterByProductType();
    </script>
  </body>
</html>
