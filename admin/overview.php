<?php // admin-overview.php - Admin Overview Dashboard ?>
<?php
require_once 'check.php';
require '../database/db.php';
// --- Dashboard Stats ---
$totalOrders = 0;
$newOrders = 0;
$onDelivery = 0;
$totalOrdersTrend = 0;

// Check if orders table exists and get stats
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
    if ($stmt->rowCount() > 0) {
        // Check if deleted column exists in orders table
        $checkColumn = $pdo->query("SHOW COLUMNS FROM orders LIKE 'deleted'");
        $hasDeletedColumn = $checkColumn->rowCount() > 0;
        
        if ($hasDeletedColumn) {
            $result = $pdo->query("SELECT COUNT(*) as cnt FROM orders WHERE deleted = 0");
            $row = $result->fetch();
            $totalOrders = $row['cnt'] ?? 0;
            
            $result = $pdo->query("SELECT COUNT(*) as cnt FROM orders WHERE status='pending' AND deleted = 0");
            $row = $result->fetch();
            $newOrders = $row['cnt'] ?? 0;
            
            $result = $pdo->query("SELECT COUNT(*) as cnt FROM orders WHERE status='processing' AND deleted = 0");
            $row = $result->fetch();
            $onDelivery = $row['cnt'] ?? 0;
            
            $result = $pdo->query("SELECT COUNT(*) as cnt FROM orders WHERE WEEK(date_ordered) = WEEK(NOW()) AND deleted = 0");
            $row = $result->fetch();
            $totalOrdersTrend = $row['cnt'] ?? 0;
        } else {
            // Fallback if deleted column doesn't exist
            $result = $pdo->query("SELECT COUNT(*) as cnt FROM orders");
            $row = $result->fetch();
            $totalOrders = $row['cnt'] ?? 0;
            
            $result = $pdo->query("SELECT COUNT(*) as cnt FROM orders WHERE status='pending'");
            $row = $result->fetch();
            $newOrders = $row['cnt'] ?? 0;
            
            $result = $pdo->query("SELECT COUNT(*) as cnt FROM orders WHERE status='processing'");
            $row = $result->fetch();
            $onDelivery = $row['cnt'] ?? 0;
            
            $result = $pdo->query("SELECT COUNT(*) as cnt FROM orders WHERE WEEK(date_ordered) = WEEK(NOW())");
            $row = $result->fetch();
            $totalOrdersTrend = $row['cnt'] ?? 0;
        }
    }
} catch (PDOException $e) {
    // Handle error silently or log it
}

// --- Recent Activity ---
$recentActivities = [];
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'logs'");
    if ($stmt->rowCount() > 0) {
        $result = $pdo->query("SELECT logs.*, users.profile_image FROM logs LEFT JOIN users ON logs.user = users.username ORDER BY logs.created_at DESC LIMIT 3");
        $recentActivities = $result->fetchAll();
    }
} catch (PDOException $e) {
    // Handle error silently or log it
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Overview Dashboard- La Consolacion Jewelry</title>
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
      .gradient-orange {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
      }
      .card-hover {
        transition: all 0.3s ease;
      }
      .card-hover:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
      }
      .stat-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      }
    </style>
  </head>
  <body class="min-h-screen overflow-hidden">
    <?php include '../components/layout-header.php'; ?>
      <!-- Main Content -->
      <main class="flex-1 p-4 lg:p-8 ml-0 lg:ml-72 overflow-y-auto h-[calc(100vh-80px)]">
        <!-- Welcome Section -->
        <div class="mb-8">
          <h1 class="text-4xl font-bold text-gray-800 mb-2">Welcome back, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?>!</h1>
          <p class="text-gray-600 text-lg">Here's what's happening with your jewelry business today.</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
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
                  <div class="text-4xl font-bold text-white drop-shadow-lg"><?php echo $totalOrders; ?></div>
                  <div class="text-white text-base font-semibold drop-shadow-md">Total Orders</div>
                </div>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-white text-base font-medium drop-shadow-md">This week: <?php echo $totalOrdersTrend; ?></span>
                <div class="flex items-center text-white">
                  <i data-lucide="trending-up" class="w-4 h-4 drop-shadow-lg"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- New Orders -->
          <div class="relative overflow-hidden bg-gradient-to-br from-amber-400 via-amber-500 to-amber-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -translate-y-16 translate-x-16"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-white opacity-5 rounded-full translate-y-12 -translate-x-12"></div>
            <div class="relative z-10">
              <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-white bg-opacity-30 rounded-xl flex items-center justify-center backdrop-blur-sm shadow-lg">
                  <i data-lucide="package" class="w-7 h-7 text-white drop-shadow-lg"></i>
                </div>
                <div class="text-right">
                  <div class="text-4xl font-bold text-white drop-shadow-lg"><?php echo $newOrders; ?></div>
                  <div class="text-white text-base font-semibold drop-shadow-md">Pending Orders</div>
                </div>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-white text-base font-medium drop-shadow-md">Awaiting processing</span>
                <div class="flex items-center text-white">
                  <i data-lucide="clock" class="w-4 h-4 drop-shadow-lg"></i>
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
                  <i data-lucide="truck" class="w-7 h-7 text-white drop-shadow-lg"></i>
                </div>
                <div class="text-right">
                  <div class="text-4xl font-bold text-white drop-shadow-lg"><?php echo $onDelivery; ?></div>
                  <div class="text-white text-base font-semibold drop-shadow-md">Processing</div>
                </div>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-white text-base font-medium drop-shadow-md">In production</span>
                <div class="flex items-center text-white">
                  <i data-lucide="settings" class="w-4 h-4 drop-shadow-lg"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="relative overflow-hidden bg-gradient-to-br from-rose-400 via-rose-500 to-rose-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -translate-y-16 translate-x-16"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-white opacity-5 rounded-full translate-y-12 -translate-x-12"></div>
            <div class="relative z-10">
              <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-white bg-opacity-30 rounded-xl flex items-center justify-center backdrop-blur-sm shadow-lg">
                  <i data-lucide="zap" class="w-7 h-7 text-white drop-shadow-lg"></i>
                </div>
                <div class="text-right">
                  <div class="text-4xl font-bold text-white drop-shadow-lg">Quick</div>
                  <div class="text-white text-base font-semibold drop-shadow-md">Actions</div>
                </div>
              </div>
              <div class="space-y-2">
                <a href="orders.php" class="block text-base text-white hover:text-rose-100 transition-colors duration-200 font-semibold drop-shadow-md">View Orders</a>
                <a href="inventory.php" class="block text-base text-white hover:text-rose-100 transition-colors duration-200 font-semibold drop-shadow-md">Manage Products</a>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity Section -->
        <div class="bg-white/80 rounded-2xl shadow-lg p-8 mb-8">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Recent Activity</h2>
            <a href="logs.php" class="text-blue-600 hover:underline font-medium">View All Logs</a>
          </div>
          <div class="space-y-4">
            <?php if (!empty($recentActivities)): ?>
              <?php foreach ($recentActivities as $activity): ?>
                <div class="flex items-center gap-4 bg-white/60 rounded-xl px-6 py-4 shadow-sm">
                  <?php
                    $profileImg = !empty($activity['profile_image']) ? '../Image/profile/' . $activity['profile_image'] : '';
                    // Use first two letters of first name for initials
                    $initial = !empty($activity['first_name']) ? substr(strtoupper($activity['first_name']), 0, 2) : strtoupper(substr($activity['user'], 0, 2));
                  ?>
                  <?php if ($profileImg): ?>
                    <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Profile" class="w-10 h-10 rounded-full object-cover border-2 border-white shadow" />
                  <?php else: ?>
                    <span class="w-10 h-10 rounded-full flex items-center justify-center bg-gradient-to-br from-indigo-400 to-blue-400 text-white shadow border-2 border-white font-bold text-lg">
                      <?php echo $initial; ?>
                    </span>
                  <?php endif; ?>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                      <span class="inline-block px-2 py-1 rounded bg-blue-100 text-blue-700 text-xs font-bold">Admin</span>
                      <span class="font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($activity['user']); ?></span>
                    </div>
                    <div class="text-sm text-gray-700 font-semibold"><?php echo htmlspecialchars($activity['action']); ?></div>
                    <div class="text-xs text-gray-500 truncate"><?php echo htmlspecialchars($activity['details']); ?></div>
                  </div>
                  <div class="text-xs text-gray-400 ml-4 whitespace-nowrap"><?php echo htmlspecialchars($activity['created_at']); ?></div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-gray-400 text-center py-8">No recent activity found.</div>
            <?php endif; ?>
          </div>
        </div>
      </main>
    <script>
      lucide.createIcons();
      
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
  </body>
</html>
