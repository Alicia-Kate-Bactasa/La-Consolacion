<?php
session_start();
include 'db.php';

echo "<h2>Debug Products</h2>";

try {
    // Check if products table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "<p style='color: red;'>❌ Products table does not exist!</p>";
        exit();
    } else {
        echo "<p style='color: green;'>✅ Products table exists</p>";
    }
    
    // Check total products
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $totalProducts = $stmt->fetch()['total'];
    echo "<p>Total products in database: <strong>$totalProducts</strong></p>";
    
    // Check non-deleted products
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE deleted = 0");
    $activeProducts = $stmt->fetch()['total'];
    echo "<p>Active products (not deleted): <strong>$activeProducts</strong></p>";
    
    // Show sample products
    if ($activeProducts > 0) {
        echo "<h3>Sample Products:</h3>";
        $stmt = $pdo->query("SELECT id, name, type, price, image, deleted FROM products WHERE deleted = 0 LIMIT 5");
        $products = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Type</th><th>Price</th><th>Image</th><th>Deleted</th></tr>";
        
        foreach ($products as $product) {
            echo "<tr>";
            echo "<td>{$product['id']}</td>";
            echo "<td>{$product['name']}</td>";
            echo "<td>{$product['type']}</td>";
            echo "<td>₱{$product['price']}</td>";
            echo "<td>{$product['image']}</td>";
            echo "<td>{$product['deleted']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No active products found. All products might be marked as deleted or the table is empty.</p>";
    }
    
    // Check if user is logged in
    if (isset($_SESSION['user_id'])) {
        echo "<p style='color: green;'>✅ User is logged in (ID: {$_SESSION['user_id']})</p>";
    } else {
        echo "<p style='color: red;'>❌ User is not logged in</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
}
?>

<p><a href="shop.php">Go back to shop</a></p> 