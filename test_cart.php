<?php
session_start();
require_once 'db.php';

echo "<h2>Cart System Test</h2>";

try {
    // Check if cart table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'cart'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "<p style='color: red;'>Cart table does not exist. Creating it now...</p>";
        
        // Create cart table
        $sql = "
        CREATE TABLE IF NOT EXISTS cart (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_product (user_id, product_id)
        )";
        
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Cart table created successfully!</p>";
    } else {
        echo "<p style='color: green;'>✅ Cart table already exists!</p>";
    }
    
    // Check if user is logged in
    if (isset($_SESSION['user_id'])) {
        echo "<p style='color: green;'>✅ User is logged in (ID: " . $_SESSION['user_id'] . ")</p>";
        
        // Test cart functions
        echo "<h3>Testing Cart Functions:</h3>";
        
        // Test get-cart-items.php
        echo "<p>Testing get-cart-items.php...</p>";
        include 'get-cart-items.php';
        
    } else {
        echo "<p style='color: orange;'>⚠️ User is not logged in. Please log in to test cart functionality.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<p><a href="shop.php">Go back to shop</a></p> 