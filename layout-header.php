<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';
// layout-header.php: Shared header and sidebar for admin pages

// Fetch profile info for the logged-in user (admin or user)
$profileImg = '';
$initial = 'A';
$profileName = 'Admin';
$userId = null;
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
} elseif (isset($_SESSION['admin_id'])) {
    $userId = $_SESSION['admin_id'];
}
if ($userId) {
    $stmt = $pdo->prepare('SELECT username, first_name, profile_image FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if ($user) {
        $profileName = $user['username'];
        // Use first two letters of first name for initials
        $initial = substr(strtoupper($user['first_name']), 0, 2);
        if (!empty($user['profile_image'])) {
            $profileImg = 'Image/profile/' . $user['profile_image'];
        }
    }
}
// Determine current page for sidebar highlighting
$currentPage = basename($_SERVER['SCRIPT_NAME']);
$inventoryActive = in_array($currentPage, ['admin-inventory.php', 'add-product.php', 'edit-product.php', 'edit.php']);
?>
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
        Dashboard | Welcome <?php echo htmlspecialchars($profileName); ?>
      </span>
      <div class="relative" style="z-index: 9999;">
        <a
          href="profile.php"
          class="w-9 h-9 rounded-full flex items-center justify-center hover:scale-105 transition-transform cursor-pointer border-2 border-white shadow focus:outline-none focus:ring-2 focus:ring-blue-400"
          style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"
        >
          <?php if ($profileImg): ?>
            <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Profile" class="w-9 h-9 rounded-full object-cover" />
          <?php else: ?>
            <span class="flex items-center justify-center w-9 h-9 rounded-full bg-white text-indigo-600 font-bold text-base">
              <?php echo $initial; ?>
            </span>
          <?php endif; ?>
        </a>

      </div>
    </div>
  </div>
</header>
<aside class="fixed top-[80px] left-0 w-72 h-[calc(100vh-80px)] glass p-6 border-r border-gray-200 overflow-y-auto">
  <nav class="space-y-2">
    <a href="admin-overview.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left transition-all duration-200 <?php echo $currentPage === 'admin-overview.php' ? 'gradient-deep-blue text-white shadow-lg' : 'text-gray-600 hover:bg-gray-100'; ?>">
      <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
      <span class="font-medium">Overview</span>
    </a>
    <a href="admin-orders.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left transition-all duration-200 <?php echo $currentPage === 'admin-orders.php' ? 'gradient-deep-blue text-white shadow-lg' : 'text-gray-600 hover:bg-gray-100'; ?>">
      <i data-lucide="shopping-cart" class="w-5 h-5"></i>
      <span class="font-medium">Orders</span>
    </a>
    <a href="admin-inventory.php"
       class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left transition-all duration-200 <?php echo $inventoryActive ? 'gradient-deep-blue text-white shadow-lg' : 'text-gray-600 hover:bg-gray-100'; ?>">
      <i data-lucide="package" class="w-5 h-5"></i>
      <span class="font-medium">Inventory</span>
    </a>
    <a href="admin-users.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left transition-all duration-200 <?php echo $currentPage === 'admin-users.php' ? 'gradient-deep-blue text-white shadow-lg' : 'text-gray-600 hover:bg-gray-100'; ?>">
      <i data-lucide="users" class="w-5 h-5"></i>
      <span class="font-medium">Users</span>
    </a>
    <a href="admin-logs.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left transition-all duration-200 <?php echo $currentPage === 'admin-logs.php' ? 'gradient-deep-blue text-white shadow-lg' : 'text-gray-600 hover:bg-gray-100'; ?>">
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
<script>
document.addEventListener('DOMContentLoaded', function() {
  lucide.createIcons();
});
</script> 