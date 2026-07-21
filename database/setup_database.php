<?php
session_start();
include '../database/db.php';

echo "<h2>Database Setup & Debug</h2>";

try {
    // Check database connection
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Check if products table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "<p style='color: red;'>❌ Products table does not exist. Creating it now...</p>";
        
        // Create products table
        $sql = "
        CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            type VARCHAR(100) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            image VARCHAR(255),
            stock INT DEFAULT 0,
            deleted TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Products table created successfully</p>";
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
    
    // If no products, add sample products
    if ($activeProducts == 0) {
        echo "<p style='color: orange;'>⚠️ No products found. Adding sample products...</p>";
        
        $sampleProducts = [
            ['Crown Diamond Ring', 'ring', 25000.00, '494324954_1183859846336540_5343954234173565708_n.jpg'],
            ['Gold Teddy Bear Ring', 'ring', 18000.00, '494325447_1231333955173886_6890510470019316369_n.jpg'],
            ['Silver and Gold Pyramid Ring', 'ring', 22000.00, '494325961_638023342401034_6252364838369281331_n.jpg'],
            ['Elegant Pearl Necklace', 'necklace', 15000.00, '494325142_544255902069492_8661555517179686959_n.jpg'],
            ['Diamond Stud Earrings', 'earring', 12000.00, '494325148_1842487459659992_5693493788136324930_n.jpg'],
            ['Gold Chain Bracelet', 'bracelet', 8000.00, '494325250_681972694322251_1425333947113573545_n.jpg']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO products (name, type, price, image) VALUES (?, ?, ?, ?)");
        
        foreach ($sampleProducts as $product) {
            $stmt->execute($product);
        }
        
        echo "<p style='color: green;'>✅ Added " . count($sampleProducts) . " sample products</p>";
        
        // Show the added products
        $stmt = $pdo->query("SELECT id, name, type, price, image FROM products WHERE deleted = 0 ORDER BY id DESC");
        $products = $stmt->fetchAll();
        
        echo "<h3>Added Products:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 20px;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Type</th><th>Price</th><th>Image</th></tr>";
        
        foreach ($products as $product) {
            echo "<tr>";
            echo "<td>{$product['id']}</td>";
            echo "<td>{$product['name']}</td>";
            echo "<td>{$product['type']}</td>";
            echo "<td>₱{$product['price']}</td>";
            echo "<td>{$product['image']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        // Show existing products
        echo "<h3>Existing Products:</h3>";
        $stmt = $pdo->query("SELECT id, name, type, price, image FROM products WHERE deleted = 0 ORDER BY id DESC");
        $products = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 20px;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Type</th><th>Price</th><th>Image</th></tr>";
        
        foreach ($products as $product) {
            echo "<tr>";
            echo "<td>{$product['id']}</td>";
            echo "<td>{$product['name']}</td>";
            echo "<td>{$product['type']}</td>";
            echo "<td>₱{$product['price']}</td>";
            echo "<td>{$product['image']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check if user is logged in
    if (isset($_SESSION['user_id'])) {
        echo "<p style='color: green;'>✅ User is logged in (ID: {$_SESSION['user_id']})</p>";
    } else {
        echo "<p style='color: red;'>❌ User is not logged in</p>";
        echo "<p><a href='../login.php' style='color: blue;'>Click here to login</a></p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
}
?>

<div style="margin-top: 30px; padding: 20px; background: #f0f8ff; border-radius: 10px;">
    <h3>Next Steps:</h3>
    <ol>
        <li><a href="../shop.php" style="color: blue;">Go to Shop Page</a></li>
        <li><a href="../login.php" style="color: blue;">Login if not logged in</a></li>
        <li><a href="debug_products.php" style="color: blue;">Debug Products</a></li>
    </ol>
</div> 