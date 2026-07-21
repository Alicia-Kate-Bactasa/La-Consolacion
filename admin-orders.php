<?php
require_once 'admin-check.php';
require_once 'db.php';

// After session and db connection, fetch current admin's level:
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT u.role, a.admin_level FROM users u LEFT JOIN admin a ON u.id = a.user_id WHERE u.id = ?');
$stmt->execute([$user_id]);
$currentAdmin = $stmt->fetch();

// Fetch all orders with details
$orders = [];
$order_items = [];
try {
  // Fetch orders with user and payment info (including orders without payments)
  $stmt = $pdo->query('SELECT o.id, o.status, o.user_id, o.payment_id, o.date_ordered, o.date_completed, u.username, u.role, u.first_name, u.profile_image, pay.reference_number, pay.service, pay.uploaded_at
        FROM orders o
        JOIN users u ON o.user_id = u.id
        LEFT JOIN payments pay ON o.payment_id = pay.id
        WHERE o.deleted = 0
        ORDER BY o.date_ordered DESC');
  $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Fetch all order items with product info
  $item_stmt = $pdo->query('SELECT oi.order_id, oi.product_id, oi.quantity, oi.price_at_purchase, p.name AS product_name, p.image AS product_image, p.description AS product_description
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id');
  $order_items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);

  // Group items by order_id
  $items_by_order = [];
  foreach ($order_items as $item) {
    $items_by_order[$item['order_id']][] = $item;
  }
} catch (PDOException $e) {
  // Handle error silently
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Orders - La Consolacion Jewelry</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    body {
      background: linear-gradient(135deg,
          #f8fafc 0%,
          #e0f2fe 50%,
          #e8eaf6 100%);
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

    .card-hover {
      transition: all 0.3s ease;
    }

    .card-hover:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    /* Hide the dropdown arrow for all browsers */
    .status-pill-select::-ms-expand {
      display: none;
    }

    .status-pill-select {
      background-image: none !important;
      pointer-events: none;
    }

    /* Animation for pill and dropdown transitions */
    .status-anim-fade {
      transition: opacity 0.2s, transform 0.2s;
      opacity: 1;
      transform: scale(1);
    }

    .status-anim-fade-out {
      opacity: 0;
      transform: scale(0.95);
    }

    .status-anim-fade-in {
      opacity: 1;
      transform: scale(1);
    }

    /* Custom scrollbar styles */
    .scrollbar-thin::-webkit-scrollbar {
      width: 6px;
    }

    .scrollbar-thin::-webkit-scrollbar-track {
      background: #f1f5f9;
      border-radius: 3px;
    }

    .scrollbar-thin::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 3px;
    }

    .scrollbar-thin::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
    }

    /* Firefox scrollbar */
    .scrollbar-thin {
      scrollbar-width: thin;
      scrollbar-color: #cbd5e1 #f1f5f9;
    }
  </style>
</head>

<body class="min-h-screen overflow-hidden">
  <?php include 'layout-header.php'; ?>
  <div class="flex h-[calc(100vh-80px)] overflow-hidden">
    <!-- Sidebar -->
    <aside
      class="fixed top-[80px] left-0 w-72 h-[calc(100vh-80px)] glass p-6 border-r border-gray-200 overflow-y-auto">
      <nav class="space-y-2">
        <a
          href="admin-overview.php"
          class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left text-gray-600 hover:bg-gray-100 transition-all duration-200">
          <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
          <span class="font-medium">Overview</span>
        </a>
        <a
          href="admin-orders.php"
          class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left gradient-deep-blue text-white shadow-lg">
          <i data-lucide="shopping-cart" class="w-5 h-5"></i>
          <span class="font-medium">Orders</span>
        </a>
        <a
          href="admin-inventory.php"
          class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left text-gray-600 hover:bg-gray-100 transition-all duration-200">
          <i data-lucide="package" class="w-5 h-5"></i>
          <span class="font-medium">Inventory</span>
        </a>
        <a
          href="admin-users.php"
          class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left text-gray-600 hover:bg-gray-100 transition-all duration-200">
          <i data-lucide="users" class="w-5 h-5"></i>
          <span class="font-medium">Users</span>
        </a>
        <a
          href="admin-logs.php"
          class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left text-gray-600 hover:bg-gray-100 transition-all duration-200">
          <i data-lucide="activity" class="w-5 h-5"></i>
          <span class="font-medium">Logs</span>
        </a>
      </nav>
      <div>
        <a
          href="logout.php"
          class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 transition-all duration-200 mt-4">
          <i data-lucide="log-out" class="w-5 h-5"></i>
          <span class="font-medium">Log Out</span>
        </a>
      </div>
    </aside>
    <!-- Main Content: Orders Table -->
    <main class="flex-1 p-8 ml-72 overflow-y-auto h-[calc(100vh-80px)]">
      <!-- Header Section -->
      <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-4xl font-bold text-gray-800 mb-2">Order Management</h1>
          <p class="text-gray-600 text-lg">Manage and track all customer orders</p>
        </div>
        <div>
          <a
            href="admin-walkin.php"
            class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-blue-500 text-white font-bold shadow-lg hover:from-emerald-600 hover:to-blue-600 transition-all transform hover:-translate-y-1 text-lg">
            <i data-lucide="plus-circle" class="w-6 h-6"></i>
            Walk-in Order
          </a>
        </div>
      </div>

      <!-- Walk-in Success Toast Notification -->
      <?php if (isset($_GET['success']) && $_GET['success'] === 'walkin'): ?>
        <div id="walkinToast" class="fixed bottom-6 right-6 z-[9999] transform translate-y-20 opacity-0 transition-all duration-500 ease-out">
          <div class="bg-emerald-600 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-4 border border-emerald-400/20 backdrop-blur-md">
            <div class="bg-white/20 p-2 rounded-lg">
              <i data-lucide="check-circle" class="w-6 h-6 text-white"></i>
            </div>
            <div>
              <p class="font-bold text-lg">Sale Recorded!</p>
              <p class="text-emerald-100 text-sm">Inventory has been updated successfully.</p>
            </div>
            <button onclick="hideToast()" class="ml-4 text-emerald-200 hover:text-white">
              <i data-lucide="x" class="w-5 h-5"></i>
            </button>
          </div>
        </div>

        <script>
          document.addEventListener('DOMContentLoaded', function() {
            const toast = document.getElementById("walkinToast");

            // 1. Slide In
            setTimeout(() => {
              toast.classList.remove("translate-y-20", "opacity-0");
              toast.classList.add("translate-y-0", "opacity-100");
            }, 100);

            // 2. Slide Out and Remove after 4 seconds
            setTimeout(hideToast, 4000);

            // 3. Clean the URL
            if (window.history.replaceState) {
              const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
              window.history.replaceState({
                path: cleanUrl
              }, '', cleanUrl);
            }
          });

          function hideToast() {
            const toast = document.getElementById("walkinToast");
            if (toast) {
              toast.classList.remove("translate-y-0", "opacity-100");
              toast.classList.add("translate-y-20", "opacity-0");
              setTimeout(() => toast.remove(), 500);
            }
          }
        </script>
      <?php endif; ?>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Total Orders -->
        <div class="relative overflow-hidden bg-gradient-to-br from-violet-400 via-violet-500 to-violet-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
          <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -translate-y-16 translate-x-16"></div>
          <div class="absolute bottom-0 left-0 w-24 h-24 bg-white opacity-5 rounded-full translate-y-12 -translate-x-12"></div>
          <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
              <div class="w-14 h-14 bg-white bg-opacity-30 rounded-xl flex items-center justify-center backdrop-blur-sm shadow-lg">
                <i data-lucide="shopping-cart" class="w-7 h-7 text-white drop-shadow-lg"></i>
              </div>
              <div class="text-right">
                <div class="text-4xl font-bold text-white drop-shadow-lg" data-stat="total-orders"><?php echo count($orders); ?></div>
                <div class="text-white text-base font-semibold drop-shadow-md">Total Orders</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pending Orders -->
        <div class="relative overflow-hidden bg-gradient-to-br from-amber-400 via-amber-500 to-amber-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
          <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -translate-y-16 translate-x-16"></div>
          <div class="absolute bottom-0 left-0 w-24 h-24 bg-white opacity-5 rounded-full translate-y-12 -translate-x-12"></div>
          <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
              <div class="w-14 h-14 bg-white bg-opacity-30 rounded-xl flex items-center justify-center backdrop-blur-sm shadow-lg">
                <i data-lucide="clock" class="w-7 h-7 text-white drop-shadow-lg"></i>
              </div>
              <div class="text-right">
                <div class="text-4xl font-bold text-white drop-shadow-lg" data-stat="pending"><?php echo count(array_filter($orders, function ($order) {
                                                                                                return $order['status'] === 'pending';
                                                                                              })); ?></div>
                <div class="text-white text-base font-semibold drop-shadow-md">Pending</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Processing Orders -->
        <div class="relative overflow-hidden bg-gradient-to-br from-sky-400 via-sky-500 to-sky-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
          <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -translate-y-16 translate-x-16"></div>
          <div class="absolute bottom-0 left-0 w-24 h-24 bg-white opacity-5 rounded-full translate-y-12 -translate-x-12"></div>
          <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
              <div class="w-14 h-14 bg-white bg-opacity-30 rounded-xl flex items-center justify-center backdrop-blur-sm shadow-lg">
                <i data-lucide="settings" class="w-7 h-7 text-white drop-shadow-lg"></i>
              </div>
              <div class="text-right">
                <div class="text-4xl font-bold text-white drop-shadow-lg" data-stat="processing"><?php echo count(array_filter($orders, function ($order) {
                                                                                                    return in_array($order['status'], ['processing', 'waiting_for_buyer']);
                                                                                                  })); ?></div>
                <div class="text-white text-base font-semibold drop-shadow-md">Processing</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Completed Orders -->
        <div class="relative overflow-hidden bg-gradient-to-br from-emerald-400 via-emerald-500 to-emerald-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
          <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -translate-y-16 translate-x-16"></div>
          <div class="absolute bottom-0 left-0 w-24 h-24 bg-white opacity-5 rounded-full translate-y-12 -translate-x-12"></div>
          <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
              <div class="w-14 h-14 bg-white bg-opacity-30 rounded-xl flex items-center justify-center backdrop-blur-sm shadow-lg">
                <i data-lucide="check-circle" class="w-7 h-7 text-white drop-shadow-lg"></i>
              </div>
              <div class="text-right">
                <div class="text-4xl font-bold text-white drop-shadow-lg" data-stat="completed"><?php echo count(array_filter($orders, function ($order) {
                                                                                                  return $order['status'] === 'completed';
                                                                                                })); ?></div>
                <div class="text-white text-base font-semibold drop-shadow-md">Completed</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Orders Table -->
      <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <!-- Table Header -->
        <div class="bg-gradient-to-r from-slate-50 to-sky-50 px-6 py-4 border-b border-gray-200">
          <div class="flex justify-between items-center">
            <div>
              <h2 class="text-2xl font-bold text-gray-800">Order Details</h2>
              <p class="text-gray-600">Manage individual order status and details</p>
            </div>
          </div>
        </div>

        <?php if (empty($orders)): ?>
          <div class="text-center py-16">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <i data-lucide="package" class="w-12 h-12 text-gray-400"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">No orders found</h3>
            <p class="text-gray-500">Orders will appear here when customers make purchases.</p>
          </div>
        <?php else: ?>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-64">PRODUCT NAME</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">CUSTOMER NAME</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">ORDER ID</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-32">AMOUNT</th>
                  <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-32">STATUS</th>
                  <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-32">ACTION</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-100">
                <?php foreach ($orders as $order): ?>
                  <tr class="hover:bg-gray-50 transition-all duration-200" data-order-id="<?php echo $order['id']; ?>">
                    <!-- Product Name -->
                    <td class="px-6 py-4 w-64">
                      <?php if (!empty($items_by_order[$order['id']])): ?>
                        <?php $firstItem = $items_by_order[$order['id']][0]; ?>
                        <div class="flex items-center space-x-3">
                          <?php
                          // Get product image from database
                          $productImage = '';
                          try {
                            $productStmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
                            $productStmt->execute([$firstItem['product_id']]);
                            $productData = $productStmt->fetch();
                            $productImage = $productData['image'] ?? '';
                          } catch (PDOException $e) {
                            $productImage = '';
                          }
                          ?>
                          <?php if ($productImage): ?>
                            <div class="w-12 h-12 rounded-lg overflow-hidden shadow-sm bg-gray-100">
                              <img src="<?php echo strpos($productImage, 'custom_') === 0 ? 'Image/product-add/' : 'Image/Product/'; ?><?php echo htmlspecialchars($productImage); ?>"
                                alt="<?php echo htmlspecialchars($firstItem['product_name']); ?>"
                                class="w-full h-full object-cover">
                            </div>
                          <?php else: ?>
                            <div class="w-12 h-12 bg-gradient-to-br from-violet-400 to-violet-500 rounded-lg flex items-center justify-center shadow-sm">
                              <i data-lucide="gem" class="w-6 h-6 text-white"></i>
                            </div>
                          <?php endif; ?>
                          <div>
                            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($firstItem['product_name']); ?></p>
                            <p class="text-sm text-gray-500">Jewelry Product</p>
                          </div>
                        </div>
                      <?php else: ?>
                        <div class="flex items-center space-x-3">
                          <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                            <i data-lucide="package" class="w-6 h-6 text-gray-400"></i>
                          </div>
                          <div>
                            <p class="font-semibold text-gray-900">No Product</p>
                            <p class="text-sm text-gray-500">No items found</p>
                          </div>
                        </div>
                      <?php endif; ?>
                    </td>

                    <!-- Customer Name -->
                    <td class="px-6 py-4 w-40">
                      <div class="flex items-center gap-4">
                        <?php
                        $isWalkin = ($order['username'] === 'walkin_guest');
                        $profilePath = !empty($order['profile_image']) ? 'Image/profile/' . $order['profile_image'] : '';

                        if (!$isWalkin && !empty($order['profile_image']) && file_exists($profilePath)) {
                          echo '<img src="' . htmlspecialchars($profilePath) . '" alt="Profile" class="w-10 h-10 rounded-full object-cover bg-gray-100">';
                        } else {
                          // Logic for Icons: Store icon for Walk-in, User icon for others
                          $icon = $isWalkin ? 'shopping-bag' : 'user';
                          $gradient = $isWalkin ? 'from-blue-600 to-indigo-600' : 'from-slate-400 to-slate-500';
                          echo '<div class="w-10 h-10 rounded-full flex items-center justify-center bg-gradient-to-br ' . $gradient . ' text-white shadow-sm">';
                          echo '<i data-lucide="' . $icon . '" class="w-5 h-5"></i>';
                          echo '</div>';
                        }
                        ?>
                        <div>
                          <div class="font-bold text-gray-900 leading-tight">
                            <?php echo $isWalkin ? 'Walk-In Sale' : htmlspecialchars($order['username']); ?>
                          </div>
                          <div class="text-xs font-semibold uppercase tracking-wider">
                            <?php
                            if ($isWalkin) {
                              echo '<span class="text-blue-600">Walk-In</span>';
                            } else {
                              echo ($order['role'] === 'admin') ? '<span class="text-purple-600">Admin</span>' : '<span class="text-gray-500">Walk-In</span>';
                            }
                            ?>
                          </div>
                        </div>
                      </div>
                    </td>

                    <!-- Order ID -->
                    <td class="px-6 py-4 w-32">
                      <div>
                        <p class="font-semibold text-gray-900">#<?php echo $order['id']; ?></p>
                        <p class="text-sm text-gray-500"><?php echo date('M d, Y', strtotime($order['date_ordered'])); ?></p>
                      </div>
                    </td>

                    <!-- Amount -->
                    <td class="px-6 py-4 w-32 text-right">
                      <div>
                        <p class="font-bold text-lg text-gray-900">
                          ₱<?php
                            $total = 0;
                            if (!empty($items_by_order[$order['id']])) {
                              foreach ($items_by_order[$order['id']] as $item) {
                                $total += $item['price_at_purchase'] * $item['quantity'];
                              }
                            }
                            echo number_format($total, 0);
                            ?>
                        </p>
                        <?php if (!empty($order['service']) && $order['service'] !== 'custom'): ?>
                          <p class="text-xs text-gray-500 uppercase"><?php echo htmlspecialchars($order['service']); ?></p>
                        <?php endif; ?>
                      </div>
                    </td>

                    <!-- Status -->
                    <td class="px-6 py-4 w-32 text-center status-cell">
                      <?php
                      // Check if payment exists
                      if (empty($order['payment_id'])) {
                        // No payment - show "Customer not yet paid"
                        echo '<span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-orange-50 text-orange-700 border border-orange-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                Unpaid
                              </span>';
                      } else {
                        // Payment exists - show order status
                        $status = strtolower($order['status']);
                        $statusMap = [
                          'pending' => ['dot' => 'bg-yellow-500', 'bg' => 'bg-yellow-50', 'text' => 'text-yellow-700', 'border' => 'border-yellow-200', 'label' => 'Pending'],
                          'processing' => ['dot' => 'bg-blue-500', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'label' => 'Processing'],
                          'waiting_for_buyer' => ['dot' => 'bg-purple-500', 'bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-200', 'label' => 'Waiting'],
                          'completed' => ['dot' => 'bg-green-500', 'bg' => 'bg-green-50', 'text' => 'text-green-700', 'border' => 'border-green-200', 'label' => 'Completed'],
                          'cancelled' => ['dot' => 'bg-red-500', 'bg' => 'bg-red-50', 'text' => 'text-red-700', 'border' => 'border-red-200', 'label' => 'Cancelled'],
                        ];
                        $s = $statusMap[$status] ?? $statusMap['pending'];
                        echo '<span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium ' . $s['bg'] . ' ' . $s['text'] . ' ' . $s['border'] . '">
                                <span class="w-1.5 h-1.5 rounded-full ' . $s['dot'] . '"></span>
                                ' . $s['label'] . '
                              </span>';
                      }
                      ?>
                    </td>

                    <!-- Action -->
                    <td class="px-6 py-4 w-32 text-center">
                      <div class="flex items-center justify-center gap-2">
                        <button class="view-order-details-btn bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-lg text-xs font-medium transition-colors duration-200" data-order-id="<?php echo $order['id']; ?>">Details</button>
                        <?php if (!empty($order['payment_id'])): ?>
                          <button class="view-payment-btn bg-emerald-100 hover:bg-emerald-200 text-emerald-700 px-3 py-1 rounded-lg text-xs font-medium transition-colors duration-200" data-payment-id="<?php echo $order['payment_id']; ?>">Payment</button>
                        <?php else: ?>
                          <button class="bg-orange-50 text-orange-700 border border-orange-200 px-3 py-1 rounded-lg text-xs font-medium" disabled title="Customer not yet paid">Unpaid</button>
                        <?php endif; ?>
                        <?php if (is_admin($currentAdmin, 2)): ?>
                          <button class="deleteOrderBtn bg-red-100 hover:bg-red-200 text-red-700 px-3 py-1 rounded-lg text-xs font-medium transition-colors duration-200" data-id="<?php echo $order['id']; ?>">Delete</button>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
  </div>
  </main>
  </div>
  <!-- Payment Modal -->
  <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-6xl w-full relative transform transition-all duration-300 scale-95 opacity-0">
      <!-- Modal Header -->
      <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-6 py-4 border-b border-gray-200 rounded-t-2xl">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-full flex items-center justify-center shadow-lg">
              <i data-lucide="credit-card" class="w-5 h-5 text-white"></i>
            </div>
            <div>
              <h2 class="text-xl font-bold text-gray-900">Payment Details</h2>
              <p class="text-sm text-gray-600">Transaction information and proof of payment</p>
            </div>
          </div>
          <button id="closePaymentModal" class="text-gray-400 hover:text-gray-700 transition-colors p-2 rounded-full hover:bg-gray-100">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>
      </div>

      <!-- Modal Content -->
      <div class="p-6">
        <div id="paymentDetailsContent" class="space-y-6">
          <!-- Content will be loaded here -->
        </div>
      </div>
    </div>
  </div>

  <!-- Order Details Modal -->
  <div id="orderDetailsModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto relative">
      <button id="closeOrderDetailsModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl z-10">&times;</button>

      <!-- Modal Header -->
      <div class="bg-gradient-to-r from-slate-50 to-sky-50 px-8 py-6 border-b border-gray-200 rounded-t-2xl">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-3xl font-bold text-gray-900" id="modalOrderTitle">Order Details</h2>
            <p class="text-gray-600 mt-1" id="modalOrderDate">Order Date: Loading...</p>
          </div>
          <!-- Removed reference and payment service from here -->
        </div>
      </div>

      <!-- Modal Content -->
      <div class="p-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Left Column: Products -->
          <div>
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-xl font-bold text-gray-900">Products</h3>
              <span id="modalProductCount" class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">0 items</span>
            </div>
            <div class="relative">
              <div id="modalProducts" class="space-y-4 max-h-96 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
                <!-- Products will be loaded here -->
              </div>
              <div class="absolute bottom-0 left-0 right-2 h-8 bg-gradient-to-t from-white to-transparent pointer-events-none"></div>
            </div>

            <!-- Order Summary -->
            <div class="mt-6 pt-6 border-t border-gray-200">
              <h4 class="text-lg font-semibold text-gray-900 mb-3">Order Summary</h4>
              <div class="bg-gray-50 rounded-xl p-4">
                <div class="flex justify-between items-center">
                  <span class="font-semibold text-gray-900">Total:</span>
                  <span class="font-bold text-2xl text-gray-900" id="modalTotal">₱0.00</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column: Buyer Profile & Status -->
          <div>
            <!-- Buyer Profile -->
            <div class="mb-8">
              <h3 class="text-xl font-bold text-gray-900 mb-4">Buyer Profile</h3>
              <div class="bg-gray-50 rounded-xl p-6">
                <div class="flex items-center space-x-4 mb-4">
                  <div class="w-12 h-12 rounded-full flex items-center justify-center shadow-md bg-gradient-to-br from-slate-400 to-slate-500 relative overflow-hidden">
                    <img id="modalCustomerProfileImg" src="" alt="Profile" class="w-12 h-12 rounded-full object-cover absolute inset-0" style="display:none;" />
                    <i id="modalCustomerProfileIcon" data-lucide="user" class="w-6 h-6 text-white z-10"></i>
                  </div>
                  <div>
                    <p class="font-semibold text-gray-900 text-lg" id="modalCustomerName">Loading...</p>
                    <p class="text-gray-600" id="modalCustomerRole">Customer</p>
                  </div>
                </div>
                <div class="space-y-3">
                  <div class="flex items-center space-x-3">
                    <i data-lucide="mail" class="w-4 h-4 text-gray-500"></i>
                    <span class="text-gray-700" id="modalCustomerEmail">Loading...</span>
                  </div>
                  <div class="flex items-center space-x-3">
                    <i data-lucide="phone" class="w-4 h-4 text-gray-500"></i>
                    <span class="text-gray-700" id="modalCustomerMobile">Loading...</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Order Status Management -->
            <div>
              <h3 class="text-xl font-bold text-gray-900 mb-4">Order Status</h3>
              <div class="bg-white border border-gray-200 rounded-xl p-6">
                <div class="mb-4">
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Current Status</label>
                  <div class="flex items-center space-x-2 mb-4">
                    <div id="modalStatusDot" class="w-3 h-3 rounded-full"></div>
                    <span id="modalStatusLabel" class="font-semibold text-gray-900">Loading...</span>
                  </div>
                </div>

                <div class="mb-4">
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Change Status</label>
                  <select id="modalStatusSelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="waiting_for_buyer">Waiting for Buyer</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                  </select>
                </div>

                <div class="flex items-center space-x-3">
                  <button id="modalUpdateStatusBtn" class="bg-gradient-to-r from-sky-500 to-sky-600 hover:from-sky-600 hover:to-sky-700 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    Update Status
                  </button>
                  <button id="modalConfirmOrderBtn" class="bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 hidden">
                    Confirm Order
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="bg-gray-50 px-8 py-4 border-t border-gray-200 rounded-b-2xl">
        <div class="flex justify-end">
          <button id="modalCloseBtn" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-semibold transition-colors duration-200">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- Expanded Image Modal -->
  <div id="expandedImageModal" class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden items-center justify-center">
    <img id="expandedImage" src="" alt="Expanded Payment Screenshot" class="max-w-3xl max-h-[90vh] rounded-2xl shadow-2xl border-4 border-white cursor-zoom-out" />
  </div>

  <!-- Centered Alert Modal -->
  <div id="alertModal" class="fixed inset-0 bg-black bg-opacity-50 z-[9999] hidden items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div id="alertIcon" class="w-16 h-16 rounded-full flex items-center justify-center bg-emerald-500">
            <i data-lucide="check-circle" class="w-8 h-8 text-white"></i>
          </div>
        </div>
        <div class="text-center">
          <h3 id="alertTitle" class="text-xl font-bold text-gray-900 mb-2">Success</h3>
          <p id="alertMessage" class="text-gray-600 mb-6">Operation completed successfully</p>
          <button id="alertCloseBtn" class="bg-gradient-to-r from-sky-500 to-sky-600 hover:from-sky-600 hover:to-sky-700 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
            OK
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- Add the confirmation modal HTML after the alert modal: -->
  <div id="confirmPermanentModal" class="fixed inset-0 bg-black bg-opacity-50 z-[9999] hidden items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="w-16 h-16 rounded-full flex items-center justify-center bg-amber-500">
            <i data-lucide="alert-triangle" class="w-8 h-8 text-white"></i>
          </div>
        </div>
        <div class="text-center">
          <h3 class="text-xl font-bold text-gray-900 mb-2">Please confirm it</h3>
          <p id="confirmPermanentMessage" class="text-gray-600 mb-6">Are you sure you want to make this order <span id='confirmPermanentStatus'></span>? This action is permanent and cannot be undone.</p>
          <div class="flex justify-center gap-4">
            <button id="confirmPermanentYesBtn" class="bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 shadow-md hover:shadow-lg">Confirm</button>
            <button id="confirmPermanentCancelBtn" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-semibold transition-colors duration-200">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Add a new confirmation modal for status change: -->
  <div id="statusChangeConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 z-[9999] hidden items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="w-16 h-16 rounded-full flex items-center justify-center bg-amber-500">
            <i data-lucide="alert-triangle" class="w-8 h-8 text-white"></i>
          </div>
        </div>
        <div class="text-center">
          <h3 class="text-xl font-bold text-gray-900 mb-2">Please confirm</h3>
          <p class="text-gray-600 mb-6">Please confirm this action to make it permanent.</p>
          <div class="flex justify-center gap-4">
            <button id="statusChangeConfirmYesBtn" class="bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 shadow-md hover:shadow-lg">Confirm</button>
            <button id="statusChangeConfirmCancelBtn" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-semibold transition-colors duration-200">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Add a new confirmation modal for deleting orders: -->
  <div id="deleteOrderConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 z-[9999] hidden items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="w-16 h-16 rounded-full flex items-center justify-center bg-red-500">
            <i data-lucide="trash-2" class="w-8 h-8 text-white"></i>
          </div>
        </div>
        <div class="text-center">
          <h3 class="text-xl font-bold text-gray-900 mb-2">Delete Order?</h3>
          <p class="text-gray-600 mb-6">Are you sure you want to delete this order? This action can be undone from the database but will hide the order from the admin panel.</p>
          <div class="flex justify-center gap-4">
            <button id="deleteOrderConfirmYesBtn" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 shadow-md hover:shadow-lg">Delete</button>
            <button id="deleteOrderConfirmCancelBtn" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-semibold transition-colors duration-200">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </div>
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
  </script>
  <script>
    const statusColors = {
      pending: {
        bg: "bg-yellow-100",
        text: "text-yellow-800",
        dot: "bg-yellow-400",
        label: "Pending"
      },
      processing: {
        bg: "bg-blue-100",
        text: "text-blue-800",
        dot: "bg-blue-400",
        label: "Processing"
      },
      waiting_for_buyer: {
        bg: "bg-purple-100",
        text: "text-purple-800",
        dot: "bg-purple-400",
        label: "Waiting for Buyer"
      },
      completed: {
        bg: "bg-green-100",
        text: "text-green-800",
        dot: "bg-green-400",
        label: "Completed"
      },
      cancelled: {
        bg: "bg-red-100",
        text: "text-red-800",
        dot: "bg-red-400",
        label: "Cancelled"
      }
    };

    let currentOrderId = null;

    // Centered Alert function
    function showAlert(type, title, message) {
      console.log('Showing alert:', type, title, message); // Debug log
      const alertModal = document.getElementById('alertModal');
      const alertIcon = document.getElementById('alertIcon');
      const titleElement = document.getElementById('alertTitle');
      const messageElement = document.getElementById('alertMessage');

      // Check if elements exist
      if (!alertModal || !alertIcon || !titleElement || !messageElement) {
        console.error('Alert modal elements not found');
        return;
      }

      // Set alert content
      titleElement.textContent = title;
      messageElement.textContent = message;

      // Set icon and colors based on type
      const configs = {
        success: {
          icon: 'check-circle',
          bgColor: 'bg-emerald-500'
        },
        error: {
          icon: 'x-circle',
          bgColor: 'bg-red-500'
        },
        warning: {
          icon: 'alert-triangle',
          bgColor: 'bg-amber-500'
        },
        info: {
          icon: 'info',
          bgColor: 'bg-sky-500'
        }
      };

      const config = configs[type] || configs.info;
      alertIcon.className = `w-16 h-16 rounded-full flex items-center justify-center ${config.bgColor}`;

      // Clear and recreate the icon element
      alertIcon.innerHTML = `<i data-lucide="${config.icon}" class="w-8 h-8 text-white"></i>`;

      // Show alert modal
      alertModal.classList.remove('hidden');
      alertModal.classList.add('flex');

      // Animate in
      setTimeout(() => {
        const modalContent = alertModal.querySelector('.bg-white');
        if (modalContent) {
          modalContent.classList.remove('scale-95', 'opacity-0');
          modalContent.classList.add('scale-100', 'opacity-100');
        }
      }, 10);

      // Recreate icons
      lucide.createIcons();
    }

    function hideAlert() {
      const alertModal = document.getElementById('alertModal');
      if (!alertModal) {
        console.error('Alert modal not found');
        return;
      }

      const modalContent = alertModal.querySelector('.bg-white');
      if (!modalContent) {
        console.error('Modal content not found');
        return;
      }

      // Animate out
      modalContent.classList.remove('scale-100', 'opacity-100');
      modalContent.classList.add('scale-95', 'opacity-0');

      setTimeout(() => {
        alertModal.classList.add('hidden');
        alertModal.classList.remove('flex');
      }, 300);
    }

    function updateOrderTableStatus(orderId, status) {
      const statusConfig = statusColors[status] || statusColors.pending;
      const tableRow = document.querySelector(`tr[data-order-id="${orderId}"]`);
      if (tableRow) {
        const statusCell = tableRow.querySelector('.status-cell');
        if (statusCell) {
          statusCell.innerHTML = `
              <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold ${statusConfig.bg} ${statusConfig.text}">
                <span class="w-2 h-2 rounded-full ${statusConfig.dot}"></span>
                ${statusConfig.label}
              </span>
            `;
          // Ensure the cell keeps its centering and padding
          statusCell.classList.add('px-6', 'py-4', 'w-32', 'text-center');
        }
      }
    }

    function updateStatCards(newStatus, oldStatus) {
      // Get stat card elements
      const totalOrdersEl = document.querySelector('[data-stat="total-orders"]');
      const pendingEl = document.querySelector('[data-stat="pending"]');
      const processingEl = document.querySelector('[data-stat="processing"]');
      const completedEl = document.querySelector('[data-stat="completed"]');

      // Parse current numbers
      const getCount = el => el ? parseInt(el.textContent, 10) : 0;
      let totalOrders = getCount(totalOrdersEl);
      let pending = getCount(pendingEl);
      let processing = getCount(processingEl);
      let completed = getCount(completedEl);

      // Decrement old status
      if (oldStatus === 'pending') pending--;
      if (oldStatus === 'processing' || oldStatus === 'waiting_for_buyer') processing--;
      if (oldStatus === 'completed') completed--;

      // Increment new status
      if (newStatus === 'pending') pending++;
      if (newStatus === 'processing' || newStatus === 'waiting_for_buyer') processing++;
      if (newStatus === 'completed') completed++;

      // Update DOM
      if (totalOrdersEl) totalOrdersEl.textContent = totalOrders;
      if (pendingEl) pendingEl.textContent = pending;
      if (processingEl) processingEl.textContent = processing;
      if (completedEl) completedEl.textContent = completed;
    }

    document.addEventListener('DOMContentLoaded', function() {
      // Order Details Modal Logic
      const orderDetailsModal = document.getElementById('orderDetailsModal');
      const modalOrderTitle = document.getElementById('modalOrderTitle');
      const modalOrderDate = document.getElementById('modalOrderDate');
      const modalProducts = document.getElementById('modalProducts');
      const modalProductCount = document.getElementById('modalProductCount');
      const modalTotal = document.getElementById('modalTotal');
      const modalCustomerName = document.getElementById('modalCustomerName');
      const modalCustomerEmail = document.getElementById('modalCustomerEmail');
      const modalCustomerMobile = document.getElementById('modalCustomerMobile');
      const modalStatusDot = document.getElementById('modalStatusDot');
      const modalStatusLabel = document.getElementById('modalStatusLabel');
      const modalStatusSelect = document.getElementById('modalStatusSelect');
      const modalUpdateStatusBtn = document.getElementById('modalUpdateStatusBtn');
      const modalConfirmOrderBtn = document.getElementById('modalConfirmOrderBtn');
      const modalCustomerProfileImg = document.getElementById('modalCustomerProfileImg');
      const modalCustomerProfileIcon = document.getElementById('modalCustomerProfileIcon');

      // Add this utility function near the top of the main script section:
      function closeAllModals() {
        document.querySelectorAll('.fixed.inset-0').forEach(modal => {
          modal.classList.add('hidden');
          modal.classList.remove('flex');
        });
      }

      // Open Order Details Modal
      document.querySelectorAll('.view-order-details-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
          closeAllModals();
          const orderId = this.getAttribute('data-order-id');
          let currentStatus = null;
          currentOrderId = orderId;
          // Show loading state
          modalOrderTitle.textContent = 'Loading...';
          modalProducts.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-sky-500 mx-auto"></div></div>';

          // Fetch order details
          fetch('get-order-details.php?order_id=' + orderId)
            .then(res => res.ok ? res.json() : Promise.reject(res.status))
            .then(data => {
              // Set currentStatus from backend
              currentStatus = (data.order.status || 'pending').toLowerCase();
              // Update modal content
              modalOrderTitle.textContent = `Order #${data.order.id}`;
              modalOrderDate.textContent = `Order Date: ${new Date(data.order.date_ordered).toLocaleDateString('en-US', { 
                  year: 'numeric', 
                  month: 'long', 
                  day: 'numeric',
                  hour: '2-digit',
                  minute: '2-digit'
                })}`;
              modalTotal.textContent = `₱${parseFloat(data.total).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
              modalProductCount.textContent = `${data.products.length} item${data.products.length !== 1 ? 's' : ''}`;
              // Check if it is a walk-in sale based on the email or the payment service
              if (data.customer.email.includes('walkin') || data.order.service === 'Cash' || data.order.service === 'CASH') {
                modalCustomerName.textContent = 'Walk-in Customer';
                modalCustomerEmail.textContent = 'Walk-in (Physical Sale)';
              } else {
                modalCustomerName.textContent = data.customer.name;
                modalCustomerEmail.textContent = data.customer.email;
              }
              modalCustomerMobile.textContent = data.customer.mobile;
              // Set profile image or fallback to icon
              if (data.customer.profile_image) {
                modalCustomerProfileImg.src = 'Image/profile/' + data.customer.profile_image;
                modalCustomerProfileImg.style.display = '';
                modalCustomerProfileIcon.style.display = 'none';
              } else {
                modalCustomerProfileImg.style.display = 'none';
                modalCustomerProfileIcon.style.display = '';
              }
              // Update status
              let isUnpaid = !data.order.reference_number && !data.order.service;
              if (isUnpaid) {
                modalStatusDot.className = 'w-3 h-3 rounded-full bg-orange-500';
                modalStatusLabel.textContent = 'Unpaid';
                modalStatusSelect.value = 'pending';
                // Disable status change for unpaid orders
                modalStatusSelect.disabled = true;
                modalUpdateStatusBtn.disabled = true;
                modalUpdateStatusBtn.classList.add('opacity-50', 'cursor-not-allowed');
                modalUpdateStatusBtn.textContent = 'Payment Required';
              } else {
                const statusConfig = statusColors[currentStatus] || statusColors.pending;
                modalStatusDot.className = `w-3 h-3 rounded-full ${statusConfig.dot}`;
                modalStatusLabel.textContent = statusConfig.label;
                modalStatusSelect.value = currentStatus;

                // Set initial button state for paid orders
                modalStatusSelect.disabled = false;
                modalUpdateStatusBtn.disabled = false;
                modalUpdateStatusBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                modalUpdateStatusBtn.textContent = 'Update Status';
              }

              // Check if order is already completed or cancelled AND confirmed
              if (data.order.date_completed && (currentStatus === 'completed' || currentStatus === 'cancelled')) {
                modalStatusSelect.disabled = true;
                modalUpdateStatusBtn.disabled = true;
                modalUpdateStatusBtn.classList.add('opacity-50', 'cursor-not-allowed');
                modalUpdateStatusBtn.textContent = 'Order Completed';
              } else {
                // Check if current status matches selected status
                const selectedStatus = modalStatusSelect.value;
                if (selectedStatus === currentStatus) {
                  modalUpdateStatusBtn.disabled = true;
                  modalUpdateStatusBtn.classList.add('opacity-50', 'cursor-not-allowed');
                  modalUpdateStatusBtn.textContent = 'No Change Needed';
                }
              }

              // Show/hide confirm button
              if ((currentStatus === 'completed' || currentStatus === 'cancelled') && !data.order.date_completed) {
                modalConfirmOrderBtn.classList.remove('hidden');
              } else {
                modalConfirmOrderBtn.classList.add('hidden');
              }

              // Render products
              modalProducts.innerHTML = data.products.map(product => {
                const imagePath = product.image && product.image.startsWith('custom_') ? 'Image/product-add/' : 'Image/Product/';
                return `
                  <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <div class="flex items-center space-x-4">
                      <div class="w-16 h-16 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                        <img src="${imagePath}${product.image}" 
                             alt="${product.name}" 
                             class="w-full h-full object-cover"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="w-full h-full bg-gradient-to-br from-violet-400 to-violet-500 flex items-center justify-center" style="display:none;">
                          <i data-lucide="gem" class="w-8 h-8 text-white"></i>
                        </div>
                      </div>
                      <div class="flex-1">
                        <h4 class="font-semibold text-gray-900 text-lg">${product.name}</h4>
                        <p class="text-gray-600 text-sm mb-2">${product.description || 'No description available'}</p>
                        <div class="flex items-center justify-between">
                          <div class="text-sm text-gray-500">
                            <span class="font-medium">Qty: ${product.quantity}</span>
                            <span class="mx-2">•</span>
                            <span class="font-medium">₱${parseFloat(product.price).toLocaleString('en-US', {minimumFractionDigits: 2})} each</span>
                          </div>
                          <div class="text-right">
                            <p class="font-bold text-gray-900">₱${parseFloat(product.total).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                `;
              }).join('');

              // Show modal
              orderDetailsModal.classList.remove('hidden');
              orderDetailsModal.classList.add('flex');
              lucide.createIcons();
            })
            .catch(error => {
              console.error('Error fetching order details:', error);
              modalProducts.innerHTML = '<div class="text-center py-8 text-red-500">Error loading order details</div>';
            });
        });
      });

      // Status dropdown change handler
      modalStatusSelect.addEventListener('change', function() {
        const newStatus = this.value;
        const currentStatus = modalStatusLabel.textContent.toLowerCase();
        // If completed/cancelled, disable update button, show and enable confirm button
        if (newStatus === 'completed' || newStatus === 'cancelled') {
          if (typeof modalUpdateStatusBtn !== 'undefined' && modalUpdateStatusBtn) {
            modalUpdateStatusBtn.disabled = true;
            modalUpdateStatusBtn.classList.add('opacity-50', 'cursor-not-allowed');
            modalUpdateStatusBtn.textContent = 'Use Confirm Order';
          }
          if (typeof modalConfirmOrderBtn !== 'undefined' && modalConfirmOrderBtn) {
            modalConfirmOrderBtn.classList.remove('hidden');
            modalConfirmOrderBtn.disabled = false;
            modalConfirmOrderBtn.classList.remove('opacity-50', 'cursor-not-allowed');
          }
          return;
        }
        // Enable/disable update button based on whether status changed
        if (newStatus === currentStatus) {
          if (typeof modalUpdateStatusBtn !== 'undefined' && modalUpdateStatusBtn) {
            modalUpdateStatusBtn.disabled = true;
            modalUpdateStatusBtn.classList.add('opacity-50', 'cursor-not-allowed');
            modalUpdateStatusBtn.textContent = 'No Change Needed';
          }
          if (typeof modalConfirmOrderBtn !== 'undefined' && modalConfirmOrderBtn) {
            modalConfirmOrderBtn.classList.add('hidden');
            modalConfirmOrderBtn.disabled = true;
            modalConfirmOrderBtn.classList.add('opacity-50', 'cursor-not-allowed');
          }
        } else {
          if (typeof modalUpdateStatusBtn !== 'undefined' && modalUpdateStatusBtn) {
            modalUpdateStatusBtn.disabled = false;
            modalUpdateStatusBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            modalUpdateStatusBtn.textContent = 'Update Status';
          }
          if (typeof modalConfirmOrderBtn !== 'undefined' && modalConfirmOrderBtn) {
            modalConfirmOrderBtn.classList.add('hidden');
            modalConfirmOrderBtn.disabled = true;
            modalConfirmOrderBtn.classList.add('opacity-50', 'cursor-not-allowed');
          }
        }
      });

      // Update Status Button
      modalUpdateStatusBtn.addEventListener('click', function() {
        if (!currentOrderId) return;
        if (this.disabled) return;
        const selectedStatus = modalStatusSelect.value;
        const currentStatus = modalStatusLabel.textContent.toLowerCase();
        // Check if status is already the same
        if (selectedStatus === currentStatus) {
          showAlert('info', 'No Change', 'Order is already in this status');
          return;
        }
        // If completed/cancelled, reset dropdown to current status and do nothing
        if (selectedStatus === 'completed' || selectedStatus === 'cancelled') {
          if (window.modalStatusSelect && window.modalStatusLabel) {
            const label = window.modalStatusLabel.textContent.trim().toLowerCase();
            let value = 'pending';
            if (label === 'pending') value = 'pending';
            else if (label === 'processing') value = 'processing';
            else if (label === 'completed') value = 'completed';
            else if (label === 'cancelled') value = 'cancelled';
            window.modalStatusSelect.value = value;
          }
          return;
        }
        // For other statuses, proceed as normal
        window._doStatusUpdateFromConfirm && window._doStatusUpdateFromConfirm();
      });

      // Confirm Order Button
      modalConfirmOrderBtn.addEventListener('click', function() {
        if (!currentOrderId) return;

        // Prevent multiple clicks
        if (this.disabled) return;

        // Store the current status for stat update after confirmation
        window._currentStatusForConfirm = modalStatusLabel.textContent.toLowerCase();
        // Show confirmation modal
        const confirmModal = document.getElementById('confirmPermanentModal');
        const confirmMsg = document.getElementById('confirmPermanentMessage');
        const modalStatus = modalStatusLabel.textContent.trim();
        confirmMsg.textContent = `Are you sure you want to mark this order as ${modalStatus.toLowerCase()}? This action is permanent and cannot be undone.`;
        confirmModal.classList.remove('hidden');
        confirmModal.classList.add('flex');
        setTimeout(() => {
          const modalContent = confirmModal.querySelector('.bg-white');
          modalContent.classList.remove('scale-95', 'opacity-0');
          modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
        document.getElementById('confirmPermanentStatus').textContent = modalStatus.toLowerCase();
      });

      // Close Order Details Modal
      document.getElementById('closeOrderDetailsModal').onclick = function() {
        orderDetailsModal.classList.add('hidden');
        orderDetailsModal.classList.remove('flex');
      };

      // Close alert
      document.getElementById('alertCloseBtn').onclick = function() {
        hideAlert();
      };

      // Close alert when clicking outside
      document.getElementById('alertModal').onclick = function(e) {
        if (e.target === this) {
          hideAlert();
        }
      };

      // Test alert removed - system is working
      document.getElementById('modalCloseBtn').onclick = function() {
        orderDetailsModal.classList.add('hidden');
        orderDetailsModal.classList.remove('flex');
      };
      orderDetailsModal.onclick = function(e) {
        if (e.target === orderDetailsModal) {
          orderDetailsModal.classList.add('hidden');
          orderDetailsModal.classList.remove('flex');
        }
      };

      // Payment modal logic
      const paymentModal = document.getElementById('paymentModal');
      const paymentDetailsContent = document.getElementById('paymentDetailsContent');
      document.querySelectorAll('.view-payment-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
          closeAllModals();
          const paymentId = this.getAttribute('data-payment-id');
          console.log('Fetching payment details for ID:', paymentId); // Debug log

          // Show loading state
          paymentDetailsContent.innerHTML = `
              <div class="text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-500 mx-auto mb-4"></div>
                <p class="text-gray-600">Loading payment details...</p>
              </div>
            `;

          // Show modal immediately
          paymentModal.classList.remove('hidden');
          paymentModal.classList.add('flex');

          // Animate in
          setTimeout(() => {
            const modalContent = paymentModal.querySelector('.bg-white');
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
          }, 10);
          fetch('get-payment-details.php?id=' + paymentId)
            .then(res => res.ok ? res.json() : Promise.reject(res.status))
            .then(data => {
              console.log('Payment data received:', data); // Debug log

              // Check if data has required fields
              if (!data || data.error) {
                paymentDetailsContent.innerHTML = `
                    <div class="text-center py-8">
                      <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="alert-circle" class="w-8 h-8 text-red-500"></i>
                    </div>
                      <h3 class="text-lg font-semibold text-gray-900 mb-2">Payment Not Found</h3>
                      <p class="text-gray-600">${data.error || 'Payment details could not be loaded.'}</p>
                    </div>
                  `;
                lucide.createIcons();
                return;
              }

              // Check if payment image exists
              const hasPaymentImage = data.image_url && data.image_url !== '' && data.image_url !== 'null' && data.image_url !== 'undefined';

              paymentDetailsContent.innerHTML = `
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Left Column: Payment Screenshot -->
    <div class="space-y-4">
      <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-4">
        <div class="text-center mb-4">
          <h3 class="text-lg font-semibold text-gray-900 mb-1">Payment Screenshot</h3>
          ${hasPaymentImage ? '<p class="text-sm text-gray-600">Click to enlarge</p>' : ''}
        </div>
        ${hasPaymentImage ? `
        <div class="relative group">
          <img src="${data.image_url}" alt="Payment Screenshot" class="w-full rounded-lg shadow-lg border border-gray-200 cursor-zoom-in payment-modal-image transition-transform duration-200 group-hover:scale-105" style="background:#f8fafc;" />
          <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 rounded-lg transition-all duration-200 flex items-center justify-center">
            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
              <i data-lucide="zoom-in" class="w-8 h-8 text-white drop-shadow-lg"></i>
            </div>
          </div>
        </div>
        ` : `
        <div class="text-center py-12">
          <div class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i data-lucide="alert-triangle" class="w-10 h-10 text-orange-500"></i>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Customer Not Yet Paid</h3>
          <p class="text-gray-600 text-sm">No payment screenshot has been uploaded yet.</p>
          <div class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-orange-100 text-orange-800 rounded-full text-sm font-semibold">
            <span class="w-2 h-2 rounded-full bg-orange-400"></span>
            Awaiting Payment
          </div>
        </div>
        `}
      </div>
    </div>

    <!-- Right Column: Payment Details -->
    <div class="space-y-4">
      <!-- Payment Information Cards -->
      <div class="space-y-3">
        <!-- Reference Number -->
        <div class="bg-white border border-gray-200 rounded-xl p-4">
          <div class="flex items-center space-x-3 mb-2">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
              <i data-lucide="hash" class="w-4 h-4 text-blue-600"></i>
            </div>
            <div>
              <h4 class="font-semibold text-gray-900">Reference Number</h4>
              <p class="text-sm text-gray-600">Transaction ID</p>
            </div>
          </div>
          <p class="font-mono text-lg text-blue-700 bg-blue-50 px-3 py-2 rounded-lg">${data.reference_number && data.reference_number !== 'custom_' + data.order_id ? data.reference_number : 'Waiting'}</p>
        </div>

        <!-- Payment Service -->
        <div class="bg-white border border-gray-200 rounded-xl p-4">
          <div class="flex items-center space-x-3 mb-2">
            <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
              <i data-lucide="smartphone" class="w-4 h-4 text-emerald-600"></i>
            </div>
            <div>
              <h4 class="font-semibold text-gray-900">Payment Service</h4>
              <p class="text-sm text-gray-600">Method used</p>
            </div>
          </div>
          <div class="flex items-center space-x-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-emerald-100 text-emerald-800">
              ${data.service && data.service !== 'CUSTOM' ? data.service.toUpperCase() : 'Waiting'}
            </span>
          </div>
        </div>

        <!-- Upload Date -->
        <div class="bg-white border border-gray-200 rounded-xl p-4">
          <div class="flex items-center space-x-3 mb-2">
            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
              <i data-lucide="calendar" class="w-4 h-4 text-amber-600"></i>
            </div>
            <div>
              <h4 class="font-semibold text-gray-900">Upload Date</h4>
              <p class="text-sm text-gray-600">When received</p>
            </div>
          </div>
          <p class="text-gray-900 font-medium">${data.uploaded_at && data.uploaded_at !== data.date_ordered ? data.uploaded_at : 'Waiting'}</p>
        </div>

        <!-- Mobile Number -->
        <div class="bg-white border border-gray-200 rounded-xl p-4">
          <div class="flex items-center space-x-3 mb-2">
            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
              <i data-lucide="phone" class="w-4 h-4 text-purple-600"></i>
            </div>
            <div>
              <h4 class="font-semibold text-gray-900">Mobile Number</h4>
              <p class="text-sm text-gray-600">Contact info</p>
            </div>
          </div>
          <p class="font-mono text-gray-900">${data.mobile || 'Waiting'}</p>
        </div>
      </div>

      <!-- Remaining Balance (main), Downpayment and Order Total (reference) -->
      <div class="space-y-3">
        <div class="bg-gradient-to-r from-emerald-500 to-teal-500 rounded-xl p-6 text-white">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold mb-1">Remaining Balance</h3>
              <p class="text-emerald-100 text-sm">This is the amount to be collected upon delivery/pickup.</p>
            </div>
            <div class="text-right">
              <p class="text-3xl font-bold">₱${(data.order_total - data.amount >= 0 ? (data.order_total - data.amount) : 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
            </div>
          </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center justify-between">
          <div class="font-semibold text-gray-900">Downpayment</div>
          <div class="font-bold text-lg text-gray-900">₱${data.amount !== undefined && data.amount !== null ? parseFloat(data.amount).toLocaleString('en-US', {minimumFractionDigits: 2}) : '0.00'}</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center justify-between">
          <div class="font-semibold text-gray-900">Order Total</div>
          <div class="font-bold text-lg text-gray-900">₱${data.order_total !== undefined && data.order_total !== null ? parseFloat(data.order_total).toLocaleString('en-US', {minimumFractionDigits: 2}) : '0.00'}</div>
        </div>
                    </div>
                    </div>
                  </div>
                `;

              // Modal is already shown, just update content

              // Add click handler for image expansion (only if image exists)
              if (hasPaymentImage) {
                setTimeout(() => {
                  const img = document.querySelector('.payment-modal-image');
                  if (img) {
                    img.onclick = function() {
                      const expandedModal = document.getElementById('expandedImageModal');
                      const expandedImg = document.getElementById('expandedImage');
                      expandedImg.src = img.src;
                      expandedModal.classList.remove('hidden');
                      expandedModal.classList.add('flex');
                    };
                  }
                }, 0);
              }

              // Recreate icons
              lucide.createIcons();
            })
            .catch(error => {
              console.error('Error fetching payment details:', error);
              paymentDetailsContent.innerHTML = `
                  <div class="text-center py-8">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                      <i data-lucide="alert-circle" class="w-8 h-8 text-red-500"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Error Loading Payment</h3>
                    <p class="text-gray-600">Failed to load payment details. Please try again.</p>
                  </div>
                `;
              lucide.createIcons();
            });
        });
      });
      document.getElementById('closePaymentModal').onclick = function() {
        const modalContent = paymentModal.querySelector('.bg-white');

        // Animate out
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
          paymentModal.classList.add('hidden');
          paymentModal.classList.remove('flex');
        }, 300);
      };
      paymentModal.onclick = function(e) {
        if (e.target === paymentModal) {
          paymentModal.classList.add('hidden');
          paymentModal.classList.remove('flex');
        }
      };
      // Expanded image modal close logic
      const expandedImageModal = document.getElementById('expandedImageModal');
      if (expandedImageModal) {
        expandedImageModal.onclick = function() {
          this.classList.add('hidden');
          this.classList.remove('flex');
          document.getElementById('expandedImage').src = '';
        };
      }

      // Confirm Permanent Modal Logic
      const confirmPermanentModal = document.getElementById('confirmPermanentModal');
      const confirmPermanentYesBtn = document.getElementById('confirmPermanentYesBtn');
      const confirmPermanentCancelBtn = document.getElementById('confirmPermanentCancelBtn');
      const confirmPermanentMessage = document.getElementById('confirmPermanentMessage');

      confirmPermanentYesBtn.onclick = function() {
        const confirmModal = document.getElementById('confirmPermanentModal');
        const modalContent = confirmModal.querySelector('.bg-white');
        // Animate out
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
          confirmModal.classList.add('hidden');
          confirmModal.classList.remove('flex');
          // Actually confirm the order (run the original confirm logic)
          // Find the original confirm button and trigger its logic
          if (window._doConfirmOrder) window._doConfirmOrder();
        }, 300);
      };
      confirmPermanentCancelBtn.onclick = function() {
        const confirmModal = document.getElementById('confirmPermanentModal');
        const modalContent = confirmModal.querySelector('.bg-white');
        // Animate out
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
          confirmModal.classList.add('hidden');
          confirmModal.classList.remove('flex');
        }, 300);
      };

      // Move the original confirm logic into a function:
      window._doConfirmOrder = function() {
        modalConfirmOrderBtn.disabled = true;
        const originalText = modalConfirmOrderBtn.textContent;
        modalConfirmOrderBtn.innerHTML = `
            <div class="flex items-center space-x-2">
              <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
              <span>Confirming...</span>
            </div>
          `;
        modalConfirmOrderBtn.classList.add('opacity-75', 'cursor-not-allowed');
        // Get the intended status from the dropdown
        const confirmedStatus = modalStatusSelect.value;
        fetch('update-order-status.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `order_id=${currentOrderId}&new_status=${confirmedStatus}`
          })
          .then(res => res.ok ? res.text() : Promise.reject(res.status))
          .then(() => {
            // Update modal status display and dropdown
            const statusConfig = statusColors[confirmedStatus] || statusColors.completed;
            if (modalStatusLabel) modalStatusLabel.textContent = statusConfig.label;
            if (modalStatusSelect) modalStatusSelect.value = confirmedStatus;
            // Hide the confirm button
            modalConfirmOrderBtn.classList.add('hidden');
            // Update table badge and stat cards
            updateOrderTableStatus(currentOrderId, confirmedStatus);
            updateStatCards(confirmedStatus, window._currentStatusForConfirm);
            showAlert('success', 'Success', 'Order confirmed successfully!');
          })
          .catch(error => {
            console.error('Error confirming order:', error);
            showAlert('error', 'Error', 'Failed to confirm order');
          })
          .finally(() => {
            modalConfirmOrderBtn.disabled = false;
            modalConfirmOrderBtn.textContent = originalText;
            modalConfirmOrderBtn.classList.remove('opacity-75', 'cursor-not-allowed');
          });
      };

      // Status change confirmation modal logic
      const statusChangeConfirmModal = document.getElementById('statusChangeConfirmModal');
      const statusChangeConfirmYesBtn = document.getElementById('statusChangeConfirmYesBtn');
      const statusChangeConfirmCancelBtn = document.getElementById('statusChangeConfirmCancelBtn');
      const statusChangeConfirmMessage = document.getElementById('confirmPermanentMessage'); // Reusing the same message element

      statusChangeConfirmYesBtn.onclick = function() {
        const statusChangeModal = document.getElementById('statusChangeConfirmModal');
        const modalContent = statusChangeModal.querySelector('.bg-white');
        // Animate out
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
          statusChangeModal.classList.add('hidden');
          statusChangeModal.classList.remove('flex');
          // Actually trigger the update status button click
          if (window._pendingStatusChange) {
            modalStatusSelect.value = window._pendingStatusChange;
            modalUpdateStatusBtn.click();
            window._pendingStatusChange = null;
          }
        }, 300);
      };
      statusChangeConfirmCancelBtn.onclick = function() {
        const statusChangeModal = document.getElementById('statusChangeConfirmModal');
        const modalContent = statusChangeModal.querySelector('.bg-white');
        // Animate out
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
          statusChangeModal.classList.add('hidden');
          statusChangeModal.classList.remove('flex');
          // Reset dropdown to previous status
          modalStatusSelect.value = modalStatusLabel.textContent.toLowerCase();
        }, 300);
      };

      // Refactor the update logic into a function so it can be called from both places:
      window._doStatusUpdateFromConfirm = function() {
        // Disable button and show loading state
        modalUpdateStatusBtn.disabled = true;
        const originalText = modalUpdateStatusBtn.textContent;
        modalUpdateStatusBtn.innerHTML = `
            <div class="flex items-center space-x-2">
              <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
              <span>Updating...</span>
            </div>
          `;
        modalUpdateStatusBtn.classList.add('opacity-75', 'cursor-not-allowed');

        const newStatus = modalStatusSelect.value;
        const currentStatus = modalStatusLabel.textContent.toLowerCase();

        // Check if status is already the same
        if (newStatus === currentStatus) {
          showAlert('info', 'No Change', 'Order is already in this status');
          modalUpdateStatusBtn.disabled = false;
          modalUpdateStatusBtn.textContent = originalText;
          modalUpdateStatusBtn.classList.remove('opacity-75', 'cursor-not-allowed');
          return;
        }

        // If completed/cancelled, show confirmation modal
        if (newStatus === 'completed' || newStatus === 'cancelled') {
          window._pendingStatusChange = newStatus;
          const statusChangeModal = document.getElementById('statusChangeConfirmModal');
          statusChangeModal.classList.remove('hidden');
          statusChangeModal.classList.add('flex');
          setTimeout(() => {
            const modalContent = statusChangeModal.querySelector('.bg-white');
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
          }, 10);
          return;
        }

        // For other statuses, proceed as normal
        fetch('update-order-status.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `order_id=${currentOrderId}&new_status=${newStatus}`
          })
          .then(res => res.ok ? res.text() : Promise.reject(res.status))
          .then(() => {
            // Update modal status display
            const statusConfig = statusColors[newStatus];
            modalStatusDot.className = `w-3 h-3 rounded-full ${statusConfig.dot}`;
            modalStatusLabel.textContent = statusConfig.label;

            // Show/hide confirm button
            if ((newStatus === 'completed' || newStatus === 'cancelled')) {
              modalConfirmOrderBtn.classList.remove('hidden');
            } else {
              modalConfirmOrderBtn.classList.add('hidden');
            }

            // Update button state after successful update
            const currentSelectedStatus = modalStatusSelect.value;
            if (currentSelectedStatus === newStatus) {
              modalUpdateStatusBtn.disabled = true;
              modalUpdateStatusBtn.classList.add('opacity-50', 'cursor-not-allowed');
              modalUpdateStatusBtn.textContent = 'No Change Needed';
            }

            // Show success alert
            showAlert('success', 'Status Updated', `Order status changed to ${statusConfig.label}`);
            updateOrderTableStatus(currentOrderId, newStatus);
            updateStatCards(newStatus, currentStatus); // Update stat cards
          })
          .catch(error => {
            console.error('Error updating status:', error);
            showAlert('error', 'Error', 'Failed to update order status');
          })
          .finally(() => {
            // Re-enable button and restore original state
            modalUpdateStatusBtn.disabled = false;
            modalUpdateStatusBtn.textContent = originalText;
            modalUpdateStatusBtn.classList.remove('opacity-75', 'cursor-not-allowed');
          });
      };

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
    });
  </script>
  <script>
    // Delete Order Modal Logic
    let orderIdToDelete = null;
    const deleteOrderConfirmModal = document.getElementById('deleteOrderConfirmModal');
    const deleteOrderConfirmYesBtn = document.getElementById('deleteOrderConfirmYesBtn');
    const deleteOrderConfirmCancelBtn = document.getElementById('deleteOrderConfirmCancelBtn');
    document.querySelectorAll('.deleteOrderBtn').forEach(btn => {
      btn.addEventListener('click', function() {
        orderIdToDelete = this.dataset.id;
        // Show modal
        deleteOrderConfirmModal.classList.remove('hidden');
        deleteOrderConfirmModal.classList.add('flex');
        setTimeout(() => {
          const modalContent = deleteOrderConfirmModal.querySelector('.bg-white');
          modalContent.classList.remove('scale-95', 'opacity-0');
          modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
      });
    });
    deleteOrderConfirmYesBtn.onclick = function() {
      if (!orderIdToDelete) return;

      // Show loading state
      const originalText = deleteOrderConfirmYesBtn.textContent;
      deleteOrderConfirmYesBtn.disabled = true;
      deleteOrderConfirmYesBtn.innerHTML = `
          <div class="flex items-center space-x-2">
            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
            <span>Deleting...</span>
          </div>
        `;

      fetch('delete-order.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `id=${encodeURIComponent(orderIdToDelete)}`
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            // Show success message
            showAlert('success', 'Order Deleted', 'Order has been successfully deleted.');

            // Hide modal
            const modalContent = deleteOrderConfirmModal.querySelector('.bg-white');
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
              deleteOrderConfirmModal.classList.add('hidden');
              deleteOrderConfirmModal.classList.remove('flex');

              // Reload page after a short delay to show the success message
              setTimeout(() => {
                location.reload();
              }, 1500);
            }, 300);
          } else {
            showAlert('error', 'Delete Failed', data.error || 'Failed to delete order.');

            // Reset button state
            deleteOrderConfirmYesBtn.disabled = false;
            deleteOrderConfirmYesBtn.textContent = originalText;
          }
        })
        .catch(error => {
          console.error('Error deleting order:', error);
          showAlert('error', 'Delete Failed', 'Network error occurred while deleting order.');

          // Reset button state
          deleteOrderConfirmYesBtn.disabled = false;
          deleteOrderConfirmYesBtn.textContent = originalText;
        });
    };
    deleteOrderConfirmCancelBtn.onclick = function() {
      // Hide modal
      const modalContent = deleteOrderConfirmModal.querySelector('.bg-white');
      modalContent.classList.remove('scale-100', 'opacity-100');
      modalContent.classList.add('scale-95', 'opacity-0');
      setTimeout(() => {
        deleteOrderConfirmModal.classList.add('hidden');
        deleteOrderConfirmModal.classList.remove('flex');
      }, 300);
    };
  </script>
</body>

</html>