<?php
require_once '../database/db.php';

echo "<h2>Database Reset Script</h2>";
echo "<p>This will reset all data and auto-increment counters to start from 1.</p>";

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

try {
    // Start transaction
    $pdo->beginTransaction();
    $transactionStarted = true;
    
    echo "<h3>Starting database reset...</h3>";
    
    // Disable foreign key checks temporarily
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    
    foreach ($tables as $table) {
        echo "<p>Resetting table: <strong>$table</strong></p>";
        
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
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ul>";
    echo "<li>Create a new admin user</li>";
    echo "<li>Add products to the catalog</li>";
    echo "<li>Test the system with fresh data</li>";
    echo "</ul>";
    
    echo "<p><a href='create_test_admin.php' style='background: #10b981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Create Test Admin</a></p>";
    
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
}

echo "<hr>";
echo "<p><small>Reset completed at: " . date('Y-m-d H:i:s') . "</small></p>";
?> 