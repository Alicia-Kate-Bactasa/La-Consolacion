<?php
session_start();
require_once '../database/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
        exit();
    }
    
    if ($quantity <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid quantity']);
        exit();
    }
    
    try {
        // Check if product exists and is not deleted
        $stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ? AND deleted = 0");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if (!$product) {
            echo json_encode(['success' => false, 'error' => 'Product not found']);
            exit();
        }
        
        // Check if product is out of stock
        if ($product['stock'] <= 0) {
            echo json_encode(['success' => false, 'error' => 'Product is out of stock']);
            exit();
        }
        
        // Check if product is already in cart
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $existingItem = $stmt->fetch();
        
        if ($existingItem) {
            // Update existing cart item
            $newQuantity = $existingItem['quantity'] + $quantity;
            
            // Check if new quantity exceeds stock
            if ($newQuantity > $product['stock']) {
                echo json_encode(['success' => false, 'error' => 'Cannot exceed available stock (' . $product['stock'] . ')']);
                exit();
            }
            
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$newQuantity, $existingItem['id']]);
        } else {
            // Check if quantity exceeds stock for new item
            if ($quantity > $product['stock']) {
                echo json_encode(['success' => false, 'error' => 'Cannot exceed available stock (' . $product['stock'] . ')']);
                exit();
            }
            
            // Add new cart item
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $product_id, $quantity]);
        }
        
        // Get updated cart count
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $cartCount = $stmt->fetch()['count'];
        
        echo json_encode([
            'success' => true, 
            'message' => 'Product added to cart',
            'cart_count' => $cartCount
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?> 