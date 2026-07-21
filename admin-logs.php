<?php
require_once 'admin-check.php';
require 'db.php';

// Pagination logic
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$logsPerPage = 8; // Reduced to fit in viewport without scrolling
$offset = ($page - 1) * $logsPerPage;

// Get total log count for pagination
$totalLogs = $pdo->query('SELECT COUNT(*) FROM logs')->fetchColumn();
$totalPages = ceil($totalLogs / $logsPerPage);

// Fetch logs for this page with current user information and profile images
try {
    $stmt = $pdo->prepare('
        SELECT logs.*, 
               users.username as current_username, 
               users.first_name, 
               users.last_name, 
               users.profile_image,
               users.id as user_id
        FROM logs 
        LEFT JOIN users ON logs.user = users.username 
        ORDER BY logs.created_at DESC 
        LIMIT ? OFFSET ?
    ');
    $stmt->bindValue(1, $logsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll();
    
    // For all logs, try to find the current user by username to show current username and profile picture
    foreach ($logs as &$log) {
        $currentUsername = $log['current_username'] ?? $log['user'];
        
        // If we have a current username, try to get the latest user data
        if ($currentUsername) {
            $stmt = $pdo->prepare('SELECT username, profile_image, first_name, last_name FROM users WHERE username = ? AND deleted = 0');
            $stmt->execute([$currentUsername]);
            $currentUser = $stmt->fetch();
            
            if ($currentUser) {
                // Update with current user data
                $log['current_username'] = $currentUser['username'];
                $log['profile_image'] = $currentUser['profile_image'];
                $log['first_name'] = $currentUser['first_name'];
                $log['last_name'] = $currentUser['last_name'];
            } else {
                // If current username not found, try to find by old username
                $stmt = $pdo->prepare('SELECT username, profile_image, first_name, last_name FROM users WHERE username = ? AND deleted = 0');
                $stmt->execute([$log['user']]);
                $oldUser = $stmt->fetch();
                
                if ($oldUser) {
                    $log['current_username'] = $oldUser['username'];
                    $log['profile_image'] = $oldUser['profile_image'];
                    $log['first_name'] = $oldUser['first_name'];
                    $log['last_name'] = $oldUser['last_name'];
                }
            }
        }
    }
    

} catch (PDOException $e) {
    $logs = [];
    error_log("Logs query error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Logs Dashboard- La Consolacion Jewelry</title>
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
          <a href="admin-inventory.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left text-gray-600 hover:bg-gray-100 transition-all duration-200">
            <i data-lucide="package" class="w-5 h-5"></i>
            <span class="font-medium">Inventory</span>
          </a>
          <a href="admin-users.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left text-gray-600 hover:bg-gray-100 transition-all duration-200">
            <i data-lucide="users" class="w-5 h-5"></i>
            <span class="font-medium">Users</span>
          </a>
          <a href="admin-logs.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left gradient-deep-blue text-white shadow-lg">
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
      <!-- Main Content -->
      <main class="flex-1 p-8 ml-72 overflow-y-auto h-[calc(100vh-80px)]">
        <h1 class="text-4xl font-bold mb-6" style="color: #629aea">Logs</h1>
        
        <div class="glass p-6 rounded-2xl mb-8">
          <!-- Search and Filter Bar -->
          <div class="flex flex-wrap gap-4 mb-6 items-center">
            <div class="relative flex-1 min-w-[200px]">
              <input type="text" placeholder="Search event..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:outline-none" id="searchInput" />
              <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
            <div class="relative">
              <button id="statusBtn" class="flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-100">
                <i data-lucide="calendar"></i>
                <span id="statusText">Status</span>
                <i data-lucide="chevron-down"></i>
              </button>
              <!-- Dropdown (hidden by default, implement JS if needed) -->
            </div>
            <div class="relative">
              <button id="typeBtn" class="flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-100">
                <i data-lucide="filter"></i>
                <span id="typeText">Type</span>
                <i data-lucide="chevron-down"></i>
              </button>
              <!-- Dropdown (hidden by default, implement JS if needed) -->
            </div>
          </div>
          <!-- Logs Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left rounded-2xl overflow-hidden shadow-lg border border-blue-100">
              <thead class="bg-blue-50 sticky top-0 z-10">
                <tr>
                  <th class="px-6 py-2 font-bold text-gray-700 w-48">User</th>
                  <th class="px-6 py-2 font-bold text-gray-700 w-30 pl-20">Action</th>
                  <th class="px-6 py-2 font-bold text-gray-700 flex-1 pl-12">Details</th>
                  <th class="px-6 py-2 font-bold text-gray-700 w-30">Time</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-blue-50">
                <?php if (!empty($logs)): ?>
                <?php foreach ($logs as $index => $log): ?>
                <tr class="<?php echo $index % 2 === 0 ? 'bg-white' : 'bg-blue-50'; ?> hover:bg-blue-100 transition-all">
                                    <td class="px-6 py-2 text-gray-900 font-semibold align-middle">
                    <div class="flex items-center gap-3">
                      <?php
                        // Show the admin who performed the action (stored in logs.user field)
                        $displayUser = $log['current_username'] ?? $log['user'];
                        $profileImg = !empty($log['profile_image']) ? 'Image/profile/' . $log['profile_image'] : '';
                        // Use first two letters of first name for initials
                        $initial = !empty($log['first_name']) ? substr(strtoupper($log['first_name']), 0, 2) : strtoupper(substr($displayUser, 0, 2));
                      ?>
                      <?php if ($profileImg): ?>
                        <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Profile" class="w-8 h-8 rounded-full object-cover border-2 border-white shadow" />
                      <?php else: ?>
                        <span class="w-8 h-8 rounded-full flex items-center justify-center bg-gradient-to-br from-indigo-400 to-blue-400 text-white shadow border-2 border-white font-bold text-sm">
                          <?php echo $initial; ?>
                        </span>
                      <?php endif; ?>
                      <span class="truncate font-medium" title="<?php echo htmlspecialchars($displayUser); ?>">
                        <?php echo htmlspecialchars($displayUser); ?>
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-2 text-blue-700 font-semibold align-middle pl-20">
                    <?php echo htmlspecialchars($log['action'] ?? ''); ?>
                  </td>
                  <td class="px-6 py-2 text-gray-700 align-middle pl-12" style="word-break:break-word; white-space:normal;">
                    <?php 
                    $details = htmlspecialchars($log['details'] ?? '');
                    $isLong = strlen($details) > 120;
                    if ($isLong) {
                      echo '<div class="details-container" style="word-break:break-word; white-space:normal;">';
                      echo '<div class="details-short" style="word-break:break-word; white-space:normal;">' . substr($details, 0, 120) . '...</div>';
                      echo '<div class="details-full hidden" style="word-break:break-word; white-space:normal;">' . $details . '</div>';
                      echo '<button class="show-more-btn text-blue-600 hover:text-blue-800 text-xs font-medium mt-1">Show More</button>';
                      echo '</div>';
                    } else {
                      echo '<div class="whitespace-normal break-words" style="word-break:break-word; white-space:normal;">' . $details . '</div>';
                    }
                    ?>
                  </td>
                  <td class="px-6 py-2 text-gray-500 align-middle">
                    <?php echo htmlspecialchars($log['created_at'] ?? ''); ?>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                  <td colspan="5" class="text-center text-gray-400 py-8">No logs found.</td>
                </tr>
                <?php endif; ?>
              </tbody>
            </table>
            
            <!-- Pagination Controls Inside Table Container -->
            <?php if ($totalPages > 1): ?>
            <div class="bg-white border-t border-blue-100 px-6 py-4">
              <div class="flex items-center justify-between">
                <!-- Page Info -->
                <div class="text-sm text-gray-600">
                  Showing <?php echo ($offset + 1); ?> to <?php echo min($offset + $logsPerPage, $totalLogs); ?> of <?php echo $totalLogs; ?> logs
                </div>
                
                <!-- Pagination Buttons -->
                <div class="flex items-center gap-2">
                  <!-- Previous Button -->
                  <?php if ($page > 1): ?>
                    <a href="admin-logs.php?page=<?php echo ($page - 1); ?>" class="flex items-center gap-1 px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700 transition-colors">
                      <i data-lucide="chevron-left" class="w-4 h-4"></i>
                      Previous
                    </a>
                  <?php endif; ?>
                  
                  <!-- Page Numbers -->
                  <div class="flex items-center gap-1">
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    // Show first page if not in range
                    if ($startPage > 1) {
                      echo '<a href="admin-logs.php?page=1" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700 transition-colors">1</a>';
                      if ($startPage > 2) {
                        echo '<span class="px-2 text-gray-400">...</span>';
                      }
                    }
                    
                    // Show page numbers in range
                    for ($i = $startPage; $i <= $endPage; $i++) {
                      if ($i == $page) {
                        echo '<span class="px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-blue-600 rounded-lg">' . $i . '</span>';
                      } else {
                        echo '<a href="admin-logs.php?page=' . $i . '" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700 transition-colors">' . $i . '</a>';
                      }
                    }
                    
                    // Show last page if not in range
                    if ($endPage < $totalPages) {
                      if ($endPage < $totalPages - 1) {
                        echo '<span class="px-2 text-gray-400">...</span>';
                      }
                      echo '<a href="admin-logs.php?page=' . $totalPages . '" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700 transition-colors">' . $totalPages . '</a>';
                    }
                    ?>
                  </div>
                  
                  <!-- Next Button -->
                  <?php if ($page < $totalPages): ?>
                    <a href="admin-logs.php?page=<?php echo ($page + 1); ?>" class="flex items-center gap-1 px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700 transition-colors">
                      Next
                      <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <?php endif; ?>
          </div>
                </div>
        </main>
      </div>
    <!-- Expanded Image Modal -->
    <div id="expandedImageModal" class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden items-center justify-center">
      <img id="expandedImage" src="" alt="Expanded Screenshot" class="max-w-3xl max-h-[90vh] rounded-2xl shadow-2xl border-4 border-white cursor-zoom-out" />
    </div>
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
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Show more/less functionality for details
      document.querySelectorAll('.show-more-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
          const container = this.closest('.details-container');
          const short = container.querySelector('.details-short');
          const full = container.querySelector('.details-full');
          
          if (full.classList.contains('hidden')) {
            short.classList.add('hidden');
            full.classList.remove('hidden');
            this.textContent = 'Show Less';
          } else {
            short.classList.remove('hidden');
            full.classList.add('hidden');
            this.textContent = 'Show More';
          }
        });
      });

      // Search functionality
      const searchInput = document.getElementById('searchInput');
      const tableRows = document.querySelectorAll('tbody tr');

      function filterLogs() {
        const searchTerm = searchInput.value.toLowerCase().trim();

        tableRows.forEach(row => {
          const user = row.children[1].textContent.toLowerCase();
          const action = row.children[2].textContent.toLowerCase();
          const details = row.children[3].textContent.toLowerCase();

          const matchesSearch = user.includes(searchTerm) || 
                               action.includes(searchTerm) || 
                               details.includes(searchTerm);

          if (matchesSearch) {
            row.style.display = '';
            row.classList.add('hover:bg-blue-100');
          } else {
            row.style.display = 'none';
            row.classList.remove('hover:bg-blue-100');
          }
        });

        // Show/hide "no results" message
        const visibleRows = Array.from(tableRows).filter(row => row.style.display !== 'none');
        let noResultsMsg = document.getElementById('noResultsMsg');
        
        if (visibleRows.length === 0) {
          if (!noResultsMsg) {
            noResultsMsg = document.createElement('tr');
            noResultsMsg.id = 'noResultsMsg';
            noResultsMsg.innerHTML = `
              <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                <div class="flex flex-col items-center gap-2">
                  <i data-lucide="search-x" class="w-8 h-8 text-gray-400"></i>
                  <span class="text-lg font-medium">No logs found</span>
                  <span class="text-sm">Try adjusting your search criteria</span>
                </div>
              </td>
            `;
            document.querySelector('tbody').appendChild(noResultsMsg);
            lucide.createIcons();
          }
        } else {
          if (noResultsMsg) {
            noResultsMsg.remove();
          }
        }
      }

      // Add event listener for search
      if (searchInput) {
        searchInput.addEventListener('input', filterLogs);
      }

      // Existing screenshot functionality
      document.querySelectorAll('.log-screenshot-thumb').forEach(function(img) {
        img.onclick = function() {
          document.getElementById('expandedImage').src = img.src;
          document.getElementById('expandedImageModal').classList.remove('hidden');
          document.getElementById('expandedImageModal').classList.add('flex');
        };
      });
      document.getElementById('expandedImageModal').onclick = function() {
        this.classList.add('hidden');
        this.classList.remove('flex');
        document.getElementById('expandedImage').src = '';
      };
    });
    </script>
  </body>
</html>
