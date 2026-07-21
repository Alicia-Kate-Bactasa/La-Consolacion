<!DOCTYPE html>
<html>
<head>
    <title>Remove Data</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .success { color: #059669; }
        .error { color: #dc2626; }
        .warning { color: #d97706; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto p-8">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl flex items-center justify-center">
                    <span class="text-white text-2xl">🗑️</span>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Remove Data</h1>
                    <p class="text-gray-600">Safely remove data from your database tables</p>
                </div>
            </div>
            
            <?php
            require_once 'db.php';
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = $_POST['action'] ?? '';
                $table = $_POST['table'] ?? '';
                $id = $_POST['id'] ?? '';
                $delete_type = $_POST['delete_type'] ?? 'soft';
                
                try {
                    if ($action === 'delete_record' && $table && $id) {
                        if ($delete_type === 'soft') {
                            // Soft delete
                            $stmt = $pdo->prepare("UPDATE $table SET deleted = 1 WHERE id = ?");
                            $stmt->execute([$id]);
                            echo '<div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-6">';
                            echo '<p class="success font-semibold">✅ Record soft deleted successfully!</p>';
                            echo '<p class="text-gray-700">The record is now hidden but preserved in the database.</p>';
                            echo '</div>';
                        } else {
                            // Hard delete
                            $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
                            $stmt->execute([$id]);
                            echo '<div class="bg-red-50 border border-red-200 rounded-xl p-6 mb-6">';
                            echo '<p class="error font-semibold">🗑️ Record permanently deleted!</p>';
                            echo '<p class="text-gray-700">The record has been completely removed from the database.</p>';
                            echo '</div>';
                        }
                    } elseif ($action === 'clear_table' && $table) {
                        if ($delete_type === 'soft') {
                            // Soft delete all
                            $stmt = $pdo->prepare("UPDATE $table SET deleted = 1");
                            $stmt->execute();
                            echo '<div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-6">';
                            echo '<p class="success font-semibold">✅ All records soft deleted!</p>';
                            echo '<p class="text-gray-700">All records are now hidden but preserved in the database.</p>';
                            echo '</div>';
                        } else {
                            // Hard delete all
                            $stmt = $pdo->prepare("DELETE FROM $table");
                            $stmt->execute();
                            echo '<div class="bg-red-50 border border-red-200 rounded-xl p-6 mb-6">';
                            echo '<p class="error font-semibold">🗑️ All records permanently deleted!</p>';
                            echo '<p class="text-gray-700">All records have been completely removed from the database.</p>';
                            echo '</div>';
                        }
                    }
                } catch (PDOException $e) {
                    echo '<div class="bg-red-50 border border-red-200 rounded-xl p-6 mb-6">';
                    echo '<p class="error font-semibold">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    echo '</div>';
                }
            }
            
            // Get table data
            $tables = ['orders', 'products', 'users'];
            $tableData = [];
            
            foreach ($tables as $table) {
                try {
                    // Check if deleted column exists
                    $checkColumn = $pdo->query("SHOW COLUMNS FROM $table LIKE 'deleted'");
                    $hasDeletedColumn = $checkColumn->rowCount() > 0;
                    
                    if ($hasDeletedColumn) {
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM $table");
                        $total = $stmt->fetch()['total'];
                        
                        $stmt = $pdo->query("SELECT COUNT(*) as active FROM $table WHERE deleted = 0");
                        $active = $stmt->fetch()['active'];
                        
                        $stmt = $pdo->query("SELECT COUNT(*) as deleted FROM $table WHERE deleted = 1");
                        $deleted = $stmt->fetch()['deleted'];
                    } else {
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM $table");
                        $total = $stmt->fetch()['total'];
                        $active = $total;
                        $deleted = 0;
                    }
                    
                    $tableData[$table] = [
                        'total' => $total,
                        'active' => $active,
                        'deleted' => $deleted,
                        'hasDeletedColumn' => $hasDeletedColumn
                    ];
                } catch (PDOException $e) {
                    $tableData[$table] = ['error' => $e->getMessage()];
                }
            }
            ?>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Orders -->
                <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📦 Orders</h3>
                    <?php if (isset($tableData['orders']['error'])): ?>
                        <p class="error">Error: <?php echo htmlspecialchars($tableData['orders']['error']); ?></p>
                    <?php else: ?>
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total:</span>
                                <span class="font-semibold"><?php echo $tableData['orders']['total']; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-green-600">Active:</span>
                                <span class="font-semibold text-green-600"><?php echo $tableData['orders']['active']; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-red-600">Deleted:</span>
                                <span class="font-semibold text-red-600"><?php echo $tableData['orders']['deleted']; ?></span>
                            </div>
                        </div>
                        
                        <form method="post" class="space-y-3">
                            <input type="hidden" name="action" value="clear_table">
                            <input type="hidden" name="table" value="orders">
                            <select name="delete_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="soft">Soft Delete (Hide)</option>
                                <option value="hard">Hard Delete (Remove)</option>
                            </select>
                            <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition">
                                Clear All Orders
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                
                <!-- Products -->
                <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">🛍️ Products</h3>
                    <?php if (isset($tableData['products']['error'])): ?>
                        <p class="error">Error: <?php echo htmlspecialchars($tableData['products']['error']); ?></p>
                    <?php else: ?>
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total:</span>
                                <span class="font-semibold"><?php echo $tableData['products']['total']; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-green-600">Active:</span>
                                <span class="font-semibold text-green-600"><?php echo $tableData['products']['active']; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-red-600">Deleted:</span>
                                <span class="font-semibold text-red-600"><?php echo $tableData['products']['deleted']; ?></span>
                            </div>
                        </div>
                        
                        <form method="post" class="space-y-3">
                            <input type="hidden" name="action" value="clear_table">
                            <input type="hidden" name="table" value="products">
                            <select name="delete_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="soft">Soft Delete (Hide)</option>
                                <option value="hard">Hard Delete (Remove)</option>
                            </select>
                            <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition">
                                Clear All Products
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                
                <!-- Users -->
                <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">👥 Users</h3>
                    <?php if (isset($tableData['users']['error'])): ?>
                        <p class="error">Error: <?php echo htmlspecialchars($tableData['users']['error']); ?></p>
                    <?php else: ?>
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total:</span>
                                <span class="font-semibold"><?php echo $tableData['users']['total']; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-green-600">Active:</span>
                                <span class="font-semibold text-green-600"><?php echo $tableData['users']['active']; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-red-600">Deleted:</span>
                                <span class="font-semibold text-red-600"><?php echo $tableData['users']['deleted']; ?></span>
                            </div>
                        </div>
                        
                        <form method="post" class="space-y-3">
                            <input type="hidden" name="action" value="clear_table">
                            <input type="hidden" name="table" value="users">
                            <select name="delete_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="soft">Soft Delete (Hide)</option>
                                <option value="hard">Hard Delete (Remove)</option>
                            </select>
                            <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition">
                                Clear All Users
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">⚠️ Important Notes:</h3>
                <ul class="space-y-2 text-gray-700">
                    <li>• <strong>Soft Delete:</strong> Hides data but preserves it in the database (safer)</li>
                    <li>• <strong>Hard Delete:</strong> Permanently removes data (cannot be recovered)</li>
                    <li>• <strong>Orders:</strong> Deleting orders will preserve order history</li>
                    <li>• <strong>Products:</strong> Products used in orders will be soft deleted</li>
                    <li>• <strong>Users:</strong> Users with orders will be soft deleted</li>
                </ul>
            </div>
            
            <div class="mt-8 pt-6 border-t border-gray-200">
                <a href="admin-overview.php" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium">
                    <span>←</span>
                    <span>Back to Overview</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html> 