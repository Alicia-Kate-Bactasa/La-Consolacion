<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $action = $_POST['action'] ?? '';
    
    // Handle quantity change (from JavaScript)
    if (isset($_POST['change'])) {
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $change = isset($_POST['change']) ? intval($_POST['change']) : 0;
        
        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
            exit();
        }
        
        try {
            // Get current cart quantity and product stock
            $stmt = $pdo->prepare("
                SELECT c.quantity, p.stock 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ? AND c.product_id = ? AND p.deleted = 0
            ");
            $stmt->execute([$user_id, $product_id]);
            $current = $stmt->fetch();
            
            if (!$current) {
                echo json_encode(['success' => false, 'error' => 'Item not found in cart']);
                exit();
            }
            
            $newQuantity = $current['quantity'] + $change;
            
            // Validate stock limits
            if ($newQuantity <= 0) {
                // Remove item if quantity becomes 0 or negative
                $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$user_id, $product_id]);
                echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
            } elseif ($current['stock'] <= 0) {
                echo json_encode(['success' => false, 'error' => 'Product is out of stock']);
            } elseif ($newQuantity > $current['stock']) {
                echo json_encode(['success' => false, 'error' => 'Cannot exceed available stock (' . $current['stock'] . ')']);
            } else {
                // Update quantity
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$newQuantity, $user_id, $product_id]);
                echo json_encode(['success' => true, 'message' => 'Quantity updated']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
        
    } elseif ($action === 'update_quantity') {
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
        
        if ($product_id <= 0 || $quantity <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
            exit();
        }
        
        try {
            // Check stock limit
            $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ? AND deleted = 0");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if (!$product) {
                echo json_encode(['success' => false, 'error' => 'Product not found']);
                exit();
            }
            
            if ($product['stock'] <= 0) {
                echo json_encode(['success' => false, 'error' => 'Product is out of stock']);
                exit();
            } elseif ($quantity > $product['stock']) {
                echo json_encode(['success' => false, 'error' => 'Cannot exceed available stock (' . $product['stock'] . ')']);
                exit();
            }
            
            // Update cart item quantity
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND product_id = ?");
            $result = $stmt->execute([$quantity, $user_id, $product_id]);
            
            if ($result && $stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Quantity updated']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Item not found in cart']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
        
    } elseif ($action === 'remove') {
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        
        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
            exit();
        }
        
        try {
            // Remove item from cart
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $result = $stmt->execute([$user_id, $product_id]);
            
            if ($result && $stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Item not found in cart']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
        
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?> 