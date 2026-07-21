<?php
require_once '../database/db.php';

try {
    // Add deleted column to products table
    $pdo->exec("ALTER TABLE products ADD COLUMN deleted TINYINT(1) DEFAULT 0");
    echo "✅ Added 'deleted' column to products table\n";
    
    // Update existing products to have deleted = 0
    $pdo->exec("UPDATE products SET deleted = 0 WHERE deleted IS NULL");
    echo "✅ Updated existing products to have deleted = 0\n";
    
    echo "\n🎉 Database updated successfully! You can now delete products that are used in orders.\n";
    echo "Products will be soft deleted (marked as deleted) instead of being permanently removed.\n";
    
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "✅ 'deleted' column already exists in products table\n";
    } else {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}
?> 