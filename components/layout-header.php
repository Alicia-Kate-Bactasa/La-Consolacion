<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../database/db.php';
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
            $profileImg = '../Image/profile/' . $user['profile_image'];
        }
    }
}
// Determine current page for sidebar highlighting
$currentPage = basename($_SERVER['SCRIPT_NAME']);
$inventoryActive = in_array($currentPage, ['inventory.php', 'add-product.php', 'edit-product.php', 'edit.php']);
?>
<script>
(function() {
  const isLoggedIn = <?php echo (isset($_SESSION['user_id']) || isset($_SESSION['admin_id']) || isset($_SESSION['admin_logged_in'])) ? 'true' : 'false'; ?>;
  if (isLoggedIn) {
    if (!sessionStorage.getItem('session_active')) {
      const pathname = window.location.pathname;
      let logoutUrl = 'logout.php';
      if (pathname.includes('/admin/')) {
        logoutUrl = '../logout.php';
      } else if (pathname.includes('/payment/')) {
        logoutUrl = '../logout.php';
      }
      window.location.href = logoutUrl;
    } else {
      sessionStorage.setItem('session_active', 'true');
    }
  } else {
    sessionStorage.setItem('session_active', 'true');
  }
})();
</script>
<style>
  .gradient-deep-blue {
    background: linear-gradient(135deg, #1f488a 0%, #123366 100%);
  }
</style>
<header class="glass sticky top-0 z-50 px-6 py-4 border-b border-gray-200">
  <div class="flex items-center justify-between">
    <div class="flex items-center space-x-4">
      <button id="mobile-sidebar-toggle" class="lg:hidden p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-400 rounded-lg" aria-label="Toggle Sidebar" title="Toggle Sidebar">
        <i data-lucide="menu" class="w-6 h-6"></i>
      </button>
      <div class="flex items-center space-x-3">
        <a href="../index.php">
          <img src="../Image/LCJ.png" alt="LA Consolacion Logo" class="h-12 w-auto" />
        </a>
      </div>
    </div>
    <div class="flex items-center space-x-4">
      <span class="text-sm text-gray-600 hidden sm:inline">
        Dashboard | Welcome <?php echo htmlspecialchars($profileName); ?>
      </span>
      <div class="relative" style="z-index: 9999;">
        <a
          href="../profile.php"
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
<aside id="admin-sidebar" class="fixed top-[80px] left-0 w-72 h-[calc(100vh-80px)] glass p-6 border-r border-gray-200 overflow-y-auto transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-40">
  <nav class="space-y-2">
    <a href="overview.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left transition-all duration-200 <?php echo $currentPage === 'overview.php' ? 'gradient-deep-blue text-white shadow-lg' : 'text-gray-600 hover:bg-gray-100'; ?>">
      <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
      <span class="font-medium">Overview</span>
    </a>
    <a href="orders.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left transition-all duration-200 <?php echo $currentPage === 'orders.php' ? 'gradient-deep-blue text-white shadow-lg' : 'text-gray-600 hover:bg-gray-100'; ?>">
      <i data-lucide="shopping-cart" class="w-5 h-5"></i>
      <span class="font-medium">Orders</span>
    </a>
    <a href="inventory.php"
       class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left transition-all duration-200 <?php echo $inventoryActive ? 'gradient-deep-blue text-white shadow-lg' : 'text-gray-600 hover:bg-gray-100'; ?>">
      <i data-lucide="package" class="w-5 h-5"></i>
      <span class="font-medium">Inventory</span>
    </a>
    <a href="users.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left transition-all duration-200 <?php echo $currentPage === 'users.php' ? 'gradient-deep-blue text-white shadow-lg' : 'text-gray-600 hover:bg-gray-100'; ?>">
      <i data-lucide="users" class="w-5 h-5"></i>
      <span class="font-medium">Users</span>
    </a>
    <a href="logs.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left transition-all duration-200 <?php echo $currentPage === 'logs.php' ? 'gradient-deep-blue text-white shadow-lg' : 'text-gray-600 hover:bg-gray-100'; ?>">
      <i data-lucide="activity" class="w-5 h-5"></i>
      <span class="font-medium">Logs</span>
    </a>
  </nav>
  <div>
    <a href="../logout.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 transition-all duration-200 mt-4">
      <i data-lucide="log-out" class="w-5 h-5"></i>
      <span class="font-medium">Log Out</span>
    </a>
  </div>
</aside>

<!-- Mobile Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-40 z-30 hidden lg:hidden"></div>

<script>
(function() {
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }
  
  const sidebar = document.getElementById('admin-sidebar');
  const toggleBtn = document.getElementById('mobile-sidebar-toggle');
  const overlay = document.getElementById('sidebar-overlay');
  
  if (toggleBtn && sidebar && overlay) {
    function toggleSidebar() {
      sidebar.classList.toggle('-translate-x-full');
      overlay.classList.toggle('hidden');
      document.body.classList.toggle('overflow-hidden');
    }
    
    toggleBtn.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', toggleSidebar);
  }
})();
</script>