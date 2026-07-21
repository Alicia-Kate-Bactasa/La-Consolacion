<?php
require_once 'db.php';

// Check if user confirmed the reset
if (isset($_POST['confirm_reset']) && $_POST['confirm_reset'] === 'yes') {
    // User confirmed, proceed with reset
    performReset();
} else {
    // Show confirmation page with current data counts
    showConfirmation();
}

function showConfirmation() {
    global $pdo;
    
    echo "<h2>🔒 Safe Database Reset</h2>";
    echo "<p>This will completely reset your database. All data will be lost!</p>";
    
    // Get current data counts
    $tables = ['users', 'products', 'orders', 'order_items', 'payments', 'cart', 'admin', 'logs'];
    $counts = [];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
            $counts[$table] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (PDOException $e) {
            $counts[$table] = 'Error';
        }
    }
    
    echo "<h3>📊 Current Data Counts:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    echo "<tr style='background: #f3f4f6;'><th>Table</th><th>Records</th></tr>";
    foreach ($counts as $table => $count) {
        echo "<tr><td style='padding: 10px;'><strong>$table</strong></td><td style='padding: 10px; text-align: center;'>$count</td></tr>";
    }
    echo "</table>";
    
    echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4 style='color: #92400e; margin: 0 0 10px 0;'>⚠️ Warning:</h4>";
    echo "<ul style='color: #92400e; margin: 0;'>";
    echo "<li>All data will be permanently deleted</li>";
    echo "<li>Auto-increment counters will reset to 1</li>";
    echo "<li>This action cannot be undone</li>";
    echo "<li>You will need to recreate admin users and products</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<form method='POST' style='margin: 20px 0;'>";
    echo "<input type='hidden' name='confirm_reset' value='yes'>";
    echo "<button type='submit' style='background: #dc2626; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; margin-right: 10px;'>";
    echo "🚨 YES, Reset Database";
    echo "</button>";
    echo "<a href='index.php' style='background: #6b7280; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px;'>";
    echo "❌ Cancel";
    echo "</a>";
    echo "</form>";
}

function performReset() {
    global $pdo;
    
    echo "<h2>🔄 Database Reset in Progress...</h2>";
    
    // List of tables to reset (in order to avoid foreign key constraints)
    $tables = [
        'order_items',
        'payments', 
        'orders',
        'cart',
        'products',
        'admin',
        'users',
        'logs'
    ];
    
    $transactionStarted = false;
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        $transactionStarted = true;
        
        echo "<h3>Starting database reset...</h3>";
        
        // Disable foreign key checks temporarily
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        
        foreach ($tables as $table) {
            echo "<p>🔄 Resetting table: <strong>$table</strong></p>";
            
            // Truncate table (removes all data and resets auto-increment)
            $pdo->exec("TRUNCATE TABLE `$table`");
            
            echo "<p>✅ Table <strong>$table</strong> reset successfully</p>";
        }
        
        // Re-enable foreign key checks
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        
        // Commit transaction
        $pdo->commit();
        $transactionStarted = false;
        
        echo "<h3>🎉 Database reset completed successfully!</h3>";
        echo "<p>All tables have been cleared and auto-increment counters reset to 1.</p>";
        
        echo "<div style='background: #d1fae5; border: 1px solid #10b981; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4 style='color: #065f46; margin: 0 0 10px 0;'>✅ Reset Summary:</h4>";
        echo "<ul style='color: #065f46; margin: 0;'>";
        echo "<li>All data deleted</li>";
        echo "<li>Auto-increment counters reset to 1</li>";
        echo "<li>Database is now fresh and clean</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<h4>📋 Next Steps:</h4>";
        echo "<ol>";
        echo "<li>Create a new admin user</li>";
        echo "<li>Add products to the catalog</li>";
        echo "<li>Test the system with fresh data</li>";
        echo "</ol>";
        
        echo "<div style='margin: 20px 0;'>";
        echo "<a href='create_test_admin.php' style='background: #10b981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>";
        echo "👤 Create Test Admin";
        echo "</a>";
        echo "<a href='index.php' style='background: #3b82f6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;'>";
        echo "🏠 Go to Homepage";
        echo "</a>";
        echo "</div>";
        
    } catch (PDOException $e) {
        // Rollback transaction on error only if transaction was started
        if ($transactionStarted) {
            try {
                $pdo->rollBack();
            } catch (PDOException $rollbackError) {
                // Ignore rollback errors
            }
        }
        
        echo "<h3>❌ Error occurred during reset:</h3>";
        echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
        echo "<p>Database may have been partially reset. Please check your data.</p>";
        
        echo "<a href='index.php' style='background: #6b7280; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;'>";
        echo "🏠 Go to Homepage";
        echo "</a>";
    }
    
    echo "<hr>";
    echo "<p><small>Reset completed at: " . date('Y-m-d H:i:s') . "</small></p>";
}
?> 