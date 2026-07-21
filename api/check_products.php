<?php
require_once '../database/db.php';

echo "🔍 Checking products table structure...\n\n";

try {
    // Check if deleted column exists
    $stmt = $pdo->query("DESCRIBE products");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📋 Products table columns:\n";
    $hasDeletedColumn = false;
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})\n";
        if ($column['Field'] === 'deleted') {
            $hasDeletedColumn = true;
        }
    }
    
    echo "\n";
    
    if ($hasDeletedColumn) {
        echo "✅ 'deleted' column exists!\n";
        
        // Count products
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
        $total = $stmt->fetch()['total'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as active FROM products WHERE deleted = 0");
        $active = $stmt->fetch()['active'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as deleted FROM products WHERE deleted = 1");
        $deleted = $stmt->fetch()['deleted'];
        
        echo "📊 Product counts:\n";
        echo "- Total products: {$total}\n";
        echo "- Active products: {$active}\n";
        echo "- Deleted products: {$deleted}\n";
        
    } else {
        echo "❌ 'deleted' column does NOT exist!\n";
        echo "Running database update...\n\n";
        
        // Add deleted column
        $pdo->exec("ALTER TABLE products ADD COLUMN deleted TINYINT(1) DEFAULT 0");
        echo "✅ Added 'deleted' column\n";
        
        // Update existing products
        $pdo->exec("UPDATE products SET deleted = 0 WHERE deleted IS NULL");
        echo "✅ Updated existing products\n";
        
        echo "\n🎉 Database updated successfully!\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 