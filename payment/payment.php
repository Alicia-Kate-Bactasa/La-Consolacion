<?php
// DEBUG: Add debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../database/db.php';

// DEBUG: Log payment processing
error_log("🔍 PAYMENT DEBUG: Payment processing started");
error_log("🔍 PAYMENT DEBUG: POST data: " . print_r($_POST, true));
error_log("🔍 PAYMENT DEBUG: FILES data: " . print_r($_FILES, true));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'] ?? '';
    $mobile = $_POST['mobile'] ?? '';
    $reference = $_POST['reference'] ?? '';
    $product_id = $_POST['product_id'] ?? null; // Make sure your form sends this
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    if (!$user_id) {
        header('Location: ../login.php?error=login_required');
        exit();
    }

    // Validate amount format: up to 10 digits before decimal, and up to 2 after
    if (!preg_match('/^\d{1,10}(\.\d{1,2})?$/', $amount)) {
        die("Invalid amount format. Up to 10 digits with 2 decimals allowed.");
    }

    // Validate mobile: must be exactly 11 digits
    if (!preg_match('/^\d{11}$/', $mobile)) {
        die("Mobile number must be exactly 11 digits.");
    }

    // Check if this is a cart checkout
    $from_cart = isset($_POST['from_cart']) && $_POST['from_cart'] == '1';
    
    if ($from_cart) {
        // Get cart items for this user
        
        $stmt = $pdo->prepare("
            SELECT c.product_id, c.quantity, p.price, p.name
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ? AND p.deleted = 0
        ");
        $stmt->execute([$user_id]);
        $cart_items = $stmt->fetchAll();
        
        if (empty($cart_items)) {
            die("No items in cart to checkout.");
        }
        
        // Extract product IDs and quantities from cart
        $product_id = [];
        $quantities = [];
        foreach ($cart_items as $item) {
            $product_id[] = $item['product_id'];
            $quantities[] = $item['quantity'];
        }
    } else {
        // Regular single product order
        if (empty($product_id)) {
            die("No product selected for order.");
        }

        // If product_id is not an array, make it an array
        if (!is_array($product_id)) {
            $product_id = [$product_id];
        }
        
        // Default quantities for single product orders
        $quantities = $_POST['quantity'] ?? [];
    }

    $uploadedFile = null;
    if (isset($_FILES['payment_screenshot']) && $_FILES['payment_screenshot']['error'] === UPLOAD_ERR_OK) {
        $uploadedFile = $_FILES['payment_screenshot'];

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 10 * 1024 * 1024; // 10MB

        if (in_array($uploadedFile['type'], $allowedTypes) && $uploadedFile['size'] <= $maxSize) {
            // 1. Prepare upload directory
            $targetDir = __DIR__ . '/../Image/payment-upload/';
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            // 2. Create a unique filename to avoid collisions
            $extension = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
            $newFilename = uniqid('payment_', true) . '.' . $extension;
            $targetPath = $targetDir . $newFilename;

            // 3. Move the uploaded file
            if (move_uploaded_file($uploadedFile['tmp_name'], $targetPath)) {
                // ✅ 4. Save metadata to the database
                try {
                    $service = $_POST['service'] ?? '';
                    $reference_number = $reference;
                    // Insert into the payments table (store filename only)
                    $stmt = $pdo->prepare("INSERT INTO payments (reference_number, service, image, mobile, amount, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([$reference_number, $service, $newFilename, $mobile, $amount]);
                    $payment_id = $pdo->lastInsertId();

                    // Insert into orders table (no product_id)
                    $order_stmt = $pdo->prepare("INSERT INTO orders (payment_id, user_id, status) VALUES (?, ?, 'pending')");
                    $order_stmt->execute([$payment_id, $user_id]);
                    $order_id = $pdo->lastInsertId();

                    // Prepare statement to fetch product price
                    $price_stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
                    // Prepare statement to insert into order_items
                    $item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
                    // Prepare statement to update stock
                    $update_stock_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

                    foreach ($product_id as $index => $pid) {
                        // Get quantity for this product
                        $qty = 1;
                        if (is_array($quantities) && isset($quantities[$index]) && is_numeric($quantities[$index])) {
                            $qty = max(1, intval($quantities[$index]));
                        }
                        // Fetch product price
                        $price_stmt->execute([$pid]);
                        $product = $price_stmt->fetch(PDO::FETCH_ASSOC);
                        $price = $product ? $product['price'] : 0;
                        // Insert into order_items
                        $item_stmt->execute([$order_id, $pid, $qty, $price]);
                        // Decrement stock by quantity
                        $update_stock_stmt->execute([$qty, $pid]);
                    }
                    
                    // Clear cart if this was a cart checkout
                    if ($from_cart) {
                        $clear_cart_stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
                        $clear_cart_stmt->execute([$user_id]);
                    }
                } catch (PDOException $e) {
                    die("Database error: " . $e->getMessage());
                }

                // DEBUG: Log successful payment
                error_log("🔍 PAYMENT DEBUG: Payment successful, redirecting to success page");
                error_log("🔍 PAYMENT DEBUG: Redirect URL: payment_success.php?amount=$amount&mobile=$mobile&reference=$reference");
                
                // ✅ 5. Redirect with amount for success display
                header("Location: payment_success.php?amount=$amount&mobile=$mobile&reference=$reference");

                exit;
            } else {
                $success = false;
                $message = "Failed to save uploaded file.";
            }
        } else {
            $success = false;
            $message = "Invalid file type or size. Use PNG, JPG, or GIF under 10MB.";
        }
    } else {
        $success = false;
        $message = "Please upload a payment screenshot.";
    }
}
?>
