<!DOCTYPE html>
<html>
<head>
    <title>Check Orders Status</title>
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
                    <span class="text-white text-2xl">📊</span>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Orders Status Check</h1>
                    <p class="text-gray-600">Check the current status of orders and deleted column</p>
                </div>
            </div>
            
            <?php
            require_once 'db.php';
            
            echo '<div class="space-y-6">';
            
            try {
                // Check if deleted column exists in orders table
                $checkColumn = $pdo->query("SHOW COLUMNS FROM orders LIKE 'deleted'");
                $hasDeletedColumn = $checkColumn->rowCount() > 0;
                
                echo '<div class="bg-blue-50 border border-blue-200 rounded-xl p-6">';
                echo '<h2 class="text-xl font-bold text-gray-900 mb-4">📋 Orders Table Structure</h2>';
                
                if ($hasDeletedColumn) {
                    echo '<p class="success font-semibold mb-4">✅ Deleted column exists in orders table</p>';
                } else {
                    echo '<p class="error font-semibold mb-4">❌ Deleted column does NOT exist in orders table</p>';
                }
                
                // Show table structure
                $stmt = $pdo->query("DESCRIBE orders");
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
                
                // Check order counts
                echo '<div class="bg-green-50 border border-green-200 rounded-xl p-6">';
                echo '<h2 class="text-xl font-bold text-gray-900 mb-4">📊 Order Counts</h2>';
                
                if ($hasDeletedColumn) {
                    // Count all orders
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
                    $total = $stmt->fetch()['total'];
                    
                    // Count non-deleted orders
                    $stmt = $pdo->query("SELECT COUNT(*) as active FROM orders WHERE deleted = 0");
                    $active = $stmt->fetch()['active'];
                    
                    // Count deleted orders
                    $stmt = $pdo->query("SELECT COUNT(*) as deleted FROM orders WHERE deleted = 1");
                    $deleted = $stmt->fetch()['deleted'];
                    
                    // Count by status (non-deleted only)
                    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders WHERE deleted = 0 GROUP BY status");
                    $statusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo '<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">';
                    echo '<div class="bg-white rounded-lg p-4 border border-gray-200">';
                    echo '<div class="text-2xl font-bold text-gray-900">' . $total . '</div>';
                    echo '<div class="text-sm text-gray-600">Total Orders</div>';
                    echo '</div>';
                    echo '<div class="bg-white rounded-lg p-4 border border-gray-200">';
                    echo '<div class="text-2xl font-bold text-green-600">' . $active . '</div>';
                    echo '<div class="text-sm text-gray-600">Active Orders</div>';
                    echo '</div>';
                    echo '<div class="bg-white rounded-lg p-4 border border-gray-200">';
                    echo '<div class="text-2xl font-bold text-red-600">' . $deleted . '</div>';
                    echo '<div class="text-sm text-gray-600">Deleted Orders</div>';
                    echo '</div>';
                    echo '</div>';
                    
                    echo '<h3 class="text-lg font-semibold text-gray-900 mb-3">Status Breakdown (Active Orders Only):</h3>';
                    echo '<div class="grid grid-cols-1 md:grid-cols-4 gap-4">';
                    foreach ($statusCounts as $status) {
                        $statusColor = 'bg-gray-100 text-gray-700';
                        switch (strtolower($status['status'])) {
                            case 'pending':
                                $statusColor = 'bg-yellow-100 text-yellow-700';
                                break;
                            case 'processing':
                                $statusColor = 'bg-blue-100 text-blue-700';
                                break;
                            case 'completed':
                                $statusColor = 'bg-green-100 text-green-700';
                                break;
                            case 'cancelled':
                                $statusColor = 'bg-red-100 text-red-700';
                                break;
                        }
                        echo '<div class="bg-white rounded-lg p-4 border border-gray-200">';
                        echo '<div class="text-2xl font-bold ' . $statusColor . ' rounded-lg px-3 py-1 inline-block">' . $status['count'] . '</div>';
                        echo '<div class="text-sm text-gray-600 mt-2">' . ucfirst($status['status']) . '</div>';
                        echo '</div>';
                    }
                    echo '</div>';
                    
                } else {
                    // Fallback if deleted column doesn't exist
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
                    $total = $stmt->fetch()['total'];
                    
                    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
                    $statusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo '<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">';
                    echo '<p class="text-yellow-800 font-semibold">⚠️ No deleted column found - showing all orders</p>';
                    echo '</div>';
                    
                    echo '<div class="bg-white rounded-lg p-4 border border-gray-200 mb-6">';
                    echo '<div class="text-2xl font-bold text-gray-900">' . $total . '</div>';
                    echo '<div class="text-sm text-gray-600">Total Orders</div>';
                    echo '</div>';
                    
                    echo '<h3 class="text-lg font-semibold text-gray-900 mb-3">Status Breakdown:</h3>';
                    echo '<div class="grid grid-cols-1 md:grid-cols-4 gap-4">';
                    foreach ($statusCounts as $status) {
                        $statusColor = 'bg-gray-100 text-gray-700';
                        switch (strtolower($status['status'])) {
                            case 'pending':
                                $statusColor = 'bg-yellow-100 text-yellow-700';
                                break;
                            case 'processing':
                                $statusColor = 'bg-blue-100 text-blue-700';
                                break;
                            case 'completed':
                                $statusColor = 'bg-green-100 text-green-700';
                                break;
                            case 'cancelled':
                                $statusColor = 'bg-red-100 text-red-700';
                                break;
                        }
                        echo '<div class="bg-white rounded-lg p-4 border border-gray-200">';
                        echo '<div class="text-2xl font-bold ' . $statusColor . ' rounded-lg px-3 py-1 inline-block">' . $status['count'] . '</div>';
                        echo '<div class="text-sm text-gray-600 mt-2">' . ucfirst($status['status']) . '</div>';
                        echo '</div>';
                    }
                    echo '</div>';
                }
                echo '</div>';
                
            } catch (PDOException $e) {
                echo '<div class="bg-red-50 border border-red-200 rounded-xl p-6">';
                echo '<p class="error font-semibold">❌ Database Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '</div>';
            }
            echo '</div>';
            ?>
            
            <div class="mt-8 pt-6 border-t border-gray-200">
                <a href="admin-overview.php" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium mr-4">
                    <span>←</span>
                    <span>Back to Overview</span>
                </a>
                <a href="admin-orders.php" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium">
                    <span>←</span>
                    <span>Back to Orders</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html> 