<!DOCTYPE html>
<html>
<head>
    <title>Setup Deleted Column</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .success { color: #059669; }
        .error { color: #dc2626; }
        .info { color: #2563eb; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto p-8">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <span class="text-white text-2xl">🔧</span>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Setup Deleted Column</h1>
                    <p class="text-gray-600">Enable soft deletion for products used in orders</p>
                </div>
            </div>
    
    <?php
    require_once '../database/db.php';
    
    echo '<div class="space-y-6">';
    echo '<h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">📋 Current Status</h2>';
    
    try {
        // Check if deleted column exists
        $checkColumn = $pdo->query("SHOW COLUMNS FROM products LIKE 'deleted'");
        $hasDeletedColumn = $checkColumn->rowCount() > 0;
        
        if ($hasDeletedColumn) {
            echo '<div class="bg-green-50 border border-green-200 rounded-xl p-6">';
            echo '<div class="flex items-center gap-3 mb-4">';
            echo '<div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">';
            echo '<span class="text-white text-sm">✅</span>';
            echo '</div>';
            echo '<p class="success font-semibold text-lg">Deleted column already exists!</p>';
            echo '</div>';
            
            // Count products
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
            $total = $stmt->fetch()['total'];
            
            $stmt = $pdo->query("SELECT COUNT(*) as active FROM products WHERE deleted = 0");
            $active = $stmt->fetch()['active'];
            
            $stmt = $pdo->query("SELECT COUNT(*) as deleted FROM products WHERE deleted = 1");
            $deleted = $stmt->fetch()['deleted'];
            
            echo '<h3 class="text-lg font-semibold text-gray-900 mb-3">📊 Product Counts:</h3>';
            echo '<div class="grid grid-cols-1 md:grid-cols-3 gap-4">';
            echo '<div class="bg-white rounded-lg p-4 border border-gray-200">';
            echo '<div class="text-2xl font-bold text-gray-900">' . $total . '</div>';
            echo '<div class="text-sm text-gray-600">Total Products</div>';
            echo '</div>';
            echo '<div class="bg-white rounded-lg p-4 border border-gray-200">';
            echo '<div class="text-2xl font-bold text-green-600">' . $active . '</div>';
            echo '<div class="text-sm text-gray-600">Active Products</div>';
            echo '</div>';
            echo '<div class="bg-white rounded-lg p-4 border border-gray-200">';
            echo '<div class="text-2xl font-bold text-red-600">' . $deleted . '</div>';
            echo '<div class="text-sm text-gray-600">Deleted Products</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            
        } else {
            echo '<div class="bg-red-50 border border-red-200 rounded-xl p-6">';
            echo '<div class="flex items-center gap-3 mb-4">';
            echo '<div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">';
            echo '<span class="text-white text-sm">❌</span>';
            echo '</div>';
            echo '<p class="error font-semibold text-lg">Deleted column does NOT exist</p>';
            echo '</div>';
            
            if (isset($_POST['add_column'])) {
                echo '<div class="bg-blue-50 border border-blue-200 rounded-xl p-6">';
                echo '<h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">🔧 Adding deleted column...</h3>';
                
                try {
                    // Add deleted column
                    $pdo->exec("ALTER TABLE products ADD COLUMN deleted TINYINT(1) DEFAULT 0");
                    echo '<div class="flex items-center gap-2 mb-3">';
                    echo '<span class="text-green-500">✅</span>';
                    echo '<p class="success">Added deleted column to products table</p>';
                    echo '</div>';
                    
                    // Update existing products
                    $pdo->exec("UPDATE products SET deleted = 0 WHERE deleted IS NULL");
                    echo '<div class="flex items-center gap-2 mb-4">';
                    echo '<span class="text-green-500">✅</span>';
                    echo '<p class="success">Updated existing products to have deleted = 0</p>';
                    echo '</div>';
                    
                    echo '<div class="bg-green-50 border border-green-200 rounded-lg p-4">';
                    echo '<h3 class="success font-bold text-lg mb-2">🎉 Database updated successfully!</h3>';
                    echo '<p class="text-gray-700">You can now delete products that are used in orders. They will be soft deleted (marked as deleted) instead of being permanently removed.</p>';
                    echo '</div>';
                    
                    // Refresh to show new status
                    echo '<script>setTimeout(function(){ window.location.reload(); }, 3000);</script>';
                    
                } catch (PDOException $e) {
                    echo '<div class="bg-red-50 border border-red-200 rounded-lg p-4">';
                    echo '<p class="error font-semibold">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo '<div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">';
                echo '<h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">🔧 Setup Required</h3>';
                echo '<p class="text-gray-700 mb-6">To enable soft deletion of products, we need to add a deleted column to the products table. This will allow you to delete products that are used in existing orders.</p>';
                echo '<form method="post">';
                echo '<button type="submit" name="add_column" class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">';
                echo '<span class="flex items-center gap-2">';
                echo '<span>🔧</span>';
                echo '<span>Add Deleted Column</span>';
                echo '</span>';
                echo '</button>';
                echo '</form>';
                echo '</div>';
            }
        }
        
        // Show table structure
        echo '<div class="bg-gray-50 border border-gray-200 rounded-xl p-6">';
        echo '<h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">📋 Products Table Structure</h3>';
        $stmt = $pdo->query("DESCRIBE products");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo '<div class="bg-white rounded-lg p-4 border border-gray-200 overflow-x-auto">';
        echo '<table class="w-full text-sm">';
        echo '<thead class="bg-gray-50">';
        echo '<tr>';
        echo '<th class="text-left p-2 font-semibold text-gray-700">Column</th>';
        echo '<th class="text-left p-2 font-semibold text-gray-700">Type</th>';
        echo '<th class="text-left p-2 font-semibold text-gray-700">Null</th>';
        echo '<th class="text-left p-2 font-semibold text-gray-700">Default</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($columns as $column) {
            $isDeleted = $column['Field'] === 'deleted';
            echo '<tr class="' . ($isDeleted ? 'bg-green-50' : '') . '">';
            echo '<td class="p-2 font-medium ' . ($isDeleted ? 'text-green-700' : 'text-gray-900') . '">' . htmlspecialchars($column['Field']) . '</td>';
            echo '<td class="p-2 text-gray-700">' . htmlspecialchars($column['Type']) . '</td>';
            echo '<td class="p-2 text-gray-700">' . htmlspecialchars($column['Null']) . '</td>';
            echo '<td class="p-2 text-gray-700">' . htmlspecialchars($column['Default'] ?? 'NULL') . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
        
    } catch (PDOException $e) {
        echo '<div class="bg-red-50 border border-red-200 rounded-xl p-6">';
        echo '<div class="flex items-center gap-3 mb-4">';
        echo '<div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">';
        echo '<span class="text-white text-sm">❌</span>';
        echo '</div>';
        echo '<p class="error font-semibold text-lg">Database Error</p>';
        echo '</div>';
        echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '</div>';
    }
    echo '</div>';
    ?>
    
    <div class="mt-8 pt-6 border-t border-gray-200">
        <a href="../admin/inventory.php" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium">
            <span>←</span>
            <span>Back to Inventory</span>
        </a>
    </div>
        </div>
    </div>
</body>
</html> 