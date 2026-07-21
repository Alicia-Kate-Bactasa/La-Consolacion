<?php
require_once 'admin-check.php';
require_once 'db.php';

// After session and db connection, fetch current admin's level:
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT u.role, a.admin_level FROM users u LEFT JOIN admin a ON u.id = a.user_id WHERE u.id = ?');
$stmt->execute([$user_id]);
$currentAdmin = $stmt->fetch();

// Fetch all non-deleted users
$users = [];
try {
    // Check if deleted column exists in users table
    $checkColumn = $pdo->query("SHOW COLUMNS FROM users LIKE 'deleted'");
    $hasDeletedColumn = $checkColumn->rowCount() > 0;
    
    if ($hasDeletedColumn) {
        $stmt = $pdo->query('SELECT u.id, u.username, u.email, u.role, u.created_at, u.profile_image, u.first_name, u.last_name, a.admin_level FROM users u LEFT JOIN admin a ON u.id = a.user_id WHERE u.deleted = 0 ORDER BY u.created_at DESC');
    } else {
        $stmt = $pdo->query('SELECT u.id, u.username, u.email, u.role, u.created_at, u.profile_image, u.first_name, u.last_name, a.admin_level FROM users u LEFT JOIN admin a ON u.id = a.user_id ORDER BY u.created_at DESC');
    }
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Users Dashboard- La Consolacion Jewelry</title>
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
          <a href="admin-users.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-left gradient-deep-blue text-white shadow-lg">
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
      <!-- Main Content: User Management -->
      <main class="flex-1 p-8 ml-72 overflow-y-auto h-[calc(100vh-80px)]">
        <div class="mb-8">
          <h1 class="text-4xl font-bold text-gray-800 mb-2">Users</h1>
          <p class="text-gray-600 text-lg">Manage and view all users and admins</p>
        </div>
        <div class="glass p-6 rounded-2xl mb-8 shadow-lg">
          <!-- Search and Filter -->
          <div class="flex flex-wrap gap-4 mb-6 items-center">
            <div class="relative flex-1 min-w-[200px]">
              <input type="text" placeholder="Search users..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:outline-none" id="userSearch" />
              <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
            <div>
              <select id="roleFilter" class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:outline-none">
                <option value="">All Roles</option>
                <option value="Customer">Customer</option>
                <option value="Admin">Admin</option>
                <option value="SuperAdmin">SuperAdmin</option>
              </select>
            </div>
          </div>
          <!-- User Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left rounded-2xl overflow-hidden shadow border border-blue-100">
              <thead class="bg-blue-50 border-b border-blue-100">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Full Name</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Email</th>
                  <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Role</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Joined</th>
                  <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-blue-50">
                <?php foreach ($users as $user): ?>
                <?php
                  $profileName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                  $profileName = trim($profileName) ?: $user['username'];
                  // Use first two letters of first name for initials
                  $profileInitial = $user['first_name'] ? substr(strtoupper($user['first_name']), 0, 2) : strtoupper(substr($user['username'], 0, 2));
                ?>
                <tr class="hover:bg-blue-50 transition-all duration-200">
                  <!-- User -->
                  <td class="px-6 py-4 font-medium text-gray-900">
                    <div class="flex items-center gap-3">
                      <?php
                        $profilePath = !empty($user['profile_image']) ? 'Image/profile/' . $user['profile_image'] : '';
                        if (!empty($user['profile_image']) && file_exists($profilePath)) {
                          echo '<img src="' . htmlspecialchars($profilePath) . '" alt="Profile" class="w-10 h-10 rounded-full object-cover bg-gray-100">';
                        } else {
                          echo '<div class="w-10 h-10 rounded-full flex items-center justify-center bg-gradient-to-br from-slate-400 to-slate-500 text-white font-bold text-lg">' . $profileInitial . '</div>';
                        }
                      ?>
                      <span><?php echo htmlspecialchars($profileName); ?></span>
                    </div>
                  </td>
                  <!-- Email -->
                  <td class="px-6 py-4 text-gray-700"> <?php echo htmlspecialchars($user['email']); ?> </td>
                  <!-- Role -->
                  <td class="px-6 py-4 text-center align-middle">
                    <?php
                      $roleLabel = 'Customer';
                      $roleClass = 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white';
                      if (strtolower($user['role']) === 'admin') {
                        if (isset($user['admin_level']) && $user['admin_level'] == 2) {
                          $roleLabel = 'SuperAdmin';
                          $roleClass = 'bg-gradient-to-r from-red-600 to-red-800 text-white';
                        } else {
                          $roleLabel = 'Admin';
                          $roleClass = 'bg-gradient-to-r from-blue-500 to-blue-600 text-white';
                        }
                      }
                    ?>
                    <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-semibold <?php echo $roleClass; ?> shadow-sm mx-auto">
                      <?php echo $roleLabel; ?>
                    </span>
                  </td>
                  <!-- Joined -->
                  <td class="px-6 py-4 text-gray-700"> <?php echo htmlspecialchars($user['created_at']); ?> </td>
                  <!-- Actions -->
                  <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-2">
                      <?php if (is_admin($currentAdmin, 1)): ?>
                        <button class="editUserBtn bg-blue-100 text-blue-700 px-3 py-1 rounded-full font-semibold text-xs hover:bg-blue-200 transition flex items-center gap-1" data-id="<?php echo $user['id']; ?>" data-username="<?php echo htmlspecialchars($user['username']); ?>">
                          <i data-lucide="edit-3" class="w-4 h-4"></i> Edit
                        </button>
                      <?php endif; ?>
                      <?php if (is_admin($currentAdmin, 2)): ?>
                        <button class="deleteUserBtn bg-red-100 text-red-700 px-3 py-1 rounded-full font-semibold text-xs hover:bg-red-200 transition flex items-center gap-1" data-id="<?php echo $user['id']; ?>">
                          <i data-lucide="trash-2" class="w-4 h-4"></i> Delete
                        </button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>
    <!-- Edit User Modal -->
    <div id="editUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" style="display:none;">
      <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-lg">
        <h2 class="text-xl font-bold mb-4">Edit User</h2>
        <form id="editUserForm" class="space-y-4">
          <input type="hidden" id="editUserId" />
          <div>
            <label for="editFirstName" class="block text-sm font-medium text-gray-700">First Name</label>
            <input type="text" id="editFirstName" required class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-200 focus:outline-none" />
          </div>
          <div>
            <label for="editLastName" class="block text-sm font-medium text-gray-700">Last Name</label>
            <input type="text" id="editLastName" required class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-200 focus:outline-none" />
          </div>
          <div>
            <label for="editUsername" class="block text-sm font-medium text-gray-700">Username</label>
            <input type="text" id="editUsername" required class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-200 focus:outline-none" />
          </div>
          <div>
            <label for="editEmail" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" id="editEmail" required class="mt-1 w-fuall px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-200 focus:outline-none" />
          </div>
          <?php if (is_admin($currentAdmin, 2)): ?>
          <div>
            <label for="editRole" class="block text-sm font-medium text-gray-700">Role</label>
            <select id="editRole" required class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-200 focus:outline-none">
              <option value="user">Customer</option>
              <option value="admin-1">Admin</option>
              <option value="admin-2">SuperAdmin</option>
            </select>
          </div>
          <?php endif; ?>
          <div class="flex justify-end gap-2 mt-6">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Save</button>
            <button type="button" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300" id="editUserCancel">Cancel</button>
          </div>
        </form>
      </div>
    </div>
    <!-- Delete User Modal -->
    <div id="deleteUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" style="display:none;">
      <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-lg">
        <h2 class="text-xl font-bold mb-4">Delete User</h2>
        <p class="mb-6">Are you sure you want to delete this account?</p>
        <div class="flex justify-end gap-2">
          <button class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700" id="confirmDeleteUser">Yes, Delete</button>
          <button class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300" id="cancelDeleteUser">Cancel</button>
        </div>
      </div>
    </div>
    <script>
      lucide.createIcons();
      
      // Real-time role verification - check every 30 seconds
      function checkRoleStatus() {
        // Temporarily disabled to isolate issues
        return;
        
        fetch('check-role-status.php')
          .then(response => {
            if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text(); // Get response as text first
          })
          .then(text => {
            try {
              const data = JSON.parse(text);
              if (!data.isAdmin) {
                // Role has changed - redirect to login
                alert('Your role has been changed. You will be redirected to login.');
                window.location.href = 'login.php?error=role_changed';
              }
            } catch (e) {
              console.error('JSON parse error:', e);
              console.error('Response text:', text);
              // Don't redirect on JSON error, just log it
            }
          })
          .catch(error => {
            console.error('Role check failed:', error);
            // Don't redirect on network error, just log it
          });
      }
      
      // Start periodic role checking
      // setInterval(checkRoleStatus, 30000); // Check every 30 seconds - temporarily disabled
      
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
  // Edit User
  document.querySelectorAll('.editUserBtn').forEach(btn => {
    btn.addEventListener('click', function() {
      const row = this.closest('tr');
      const id = this.dataset.id;
      const username = this.dataset.username; // Use the actual username from data attribute
      const roleText = row.children[2].textContent.trim(); // Get the role text from the table
      let role = 'user';
      if (roleText === 'Admin') role = 'admin-1';
      else if (roleText === 'SuperAdmin') role = 'admin-2';
      const email = row.children[1].textContent.trim(); // Changed to index 1 for Email
      
      // Extract name from the profile display (first column)
      const profileCell = row.children[0];
      const nameSpan = profileCell.querySelector('span');
      const fullName = nameSpan ? nameSpan.textContent.trim() : username;
      
      // Split name into first and last name
      const nameParts = fullName.split(' ');
      const firstName = nameParts[0] || '';
      const lastName = nameParts.slice(1).join(' ') || '';

      document.getElementById('editUserId').value = id;
      document.getElementById('editFirstName').value = firstName;
      document.getElementById('editLastName').value = lastName;
      document.getElementById('editUsername').value = username;
      document.getElementById('editEmail').value = email;
      
      // Only set role if the dropdown exists (SuperAdmin only)
      const roleDropdown = document.getElementById('editRole');
      if (roleDropdown) {
        roleDropdown.value = role;
      }
      
      document.getElementById('editUserModal').style.display = 'flex';
    });
  });

  document.getElementById('editUserCancel').onclick = function() {
    document.getElementById('editUserModal').style.display = 'none';
  };

  document.getElementById('editUserForm').onsubmit = function(e) {
    e.preventDefault();
    const id = document.getElementById('editUserId').value;
    const firstName = document.getElementById('editFirstName').value;
    const lastName = document.getElementById('editLastName').value;
    const username = document.getElementById('editUsername').value;
    const email = document.getElementById('editEmail').value;
    
    // Only include role if the dropdown is visible (SuperAdmin only)
    const roleDropdown = document.getElementById('editRole');
    const role = roleDropdown && roleDropdown.style.display !== 'none' ? roleDropdown.value : null;
    
    let formData = `id=${encodeURIComponent(id)}&first_name=${encodeURIComponent(firstName)}&last_name=${encodeURIComponent(lastName)}&username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}`;
    if (role) {
      formData += `&role=${encodeURIComponent(role)}`;
    }
    
    console.log('Sending form data:', formData); // Debug log
    
    fetch('edit-user.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: formData
    })
    .then(response => {
      console.log('Response status:', response.status); // Debug log
      return response.text(); // Get response as text first
    })
    .then(text => {
      console.log('Response text:', text); // Debug log
      try {
        const data = JSON.parse(text);
        console.log('Parsed data:', data); // Debug log
        if (data.success) {
          location.reload();
        } else {
          alert('Failed to update user: ' + (data.error || 'Unknown error'));
        }
      } catch (e) {
        console.error('JSON parse error:', e);
        console.error('Response text:', text);
        alert('Server returned invalid response. Check console for details.');
      }
    })
    .catch(error => {
      console.error('Fetch error:', error);
      alert('Network error: ' + error.message);
    });
  };

  // Delete User
  let deleteUserId = null;
  document.querySelectorAll('.deleteUserBtn').forEach(btn => {
    btn.addEventListener('click', function() {
      deleteUserId = this.dataset.id;
      document.getElementById('deleteUserModal').style.display = 'flex';
    });
  });

  document.getElementById('cancelDeleteUser').onclick = function() {
    document.getElementById('deleteUserModal').style.display = 'none';
    deleteUserId = null;
  };

  document.getElementById('confirmDeleteUser').onclick = function() {
    if (!deleteUserId) return;
    fetch('delete-user.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `id=${encodeURIComponent(deleteUserId)}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        location.reload();
      } else {
        alert('Failed to delete user.');
      }
    });
  };

  // Search and Filter Functionality
  const userSearch = document.getElementById('userSearch');
  const roleFilter = document.getElementById('roleFilter');
  const tableRows = document.querySelectorAll('tbody tr');

  function filterUsers() {
    const searchTerm = userSearch.value.toLowerCase().trim();
    const selectedRole = roleFilter.value;

    tableRows.forEach(row => {
      const name = row.children[0].textContent.toLowerCase();
      const email = row.children[1].textContent.toLowerCase();
      const role = row.children[2].textContent.trim();

      const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
      const matchesRole = !selectedRole || role === selectedRole;

      if (matchesSearch && matchesRole) {
        row.style.display = '';
        row.classList.add('hover:bg-blue-50');
      } else {
        row.style.display = 'none';
        row.classList.remove('hover:bg-blue-50');
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
              <span class="text-lg font-medium">No users found</span>
              <span class="text-sm">Try adjusting your search or filter criteria</span>
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

  // Add event listeners for search and filter
  userSearch.addEventListener('input', filterUsers);
  roleFilter.addEventListener('change', filterUsers);

  // Clear search and filter
  function clearFilters() {
    userSearch.value = '';
    roleFilter.value = '';
    filterUsers();
  }

  // Add clear button functionality (optional)
  const clearBtn = document.createElement('button');
  clearBtn.textContent = 'Clear';
  clearBtn.className = 'px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 transition-colors';
  clearBtn.onclick = clearFilters;
  
  // Insert clear button after the filter dropdown
  roleFilter.parentNode.appendChild(clearBtn);
});
</script>
  </body>
</html>
