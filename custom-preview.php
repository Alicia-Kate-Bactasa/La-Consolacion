<?php
require_once 'database/db.php';
if (!isset($_GET['id'])) {
    die('No custom order ID provided.');
}
$id = (int)$_GET['id'];

// Fetch product data (custom orders are stored in products table with type='custom')
$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? AND type = "custom"');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    die('Custom order not found.');
}

// Fetch custom order details
$stmt = $pdo->prepare('SELECT * FROM custom_orders WHERE id = ?');
$stmt->execute([$id]);
$customOrder = $stmt->fetch();

if (!$customOrder) {
    die('Custom order details not found.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Custom Order Preview</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 50%, #e8eaf6 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .preview-box { 
            max-width: 600px; 
            margin: 40px auto; 
            padding: 40px; 
            border: 1px solid #e2e8f0; 
            border-radius: 16px; 
            background: white;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .preview-box h2 { 
            margin-top: 0; 
            color: #1e40af;
            font-size: 28px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 30px;
        }
        .preview-label { 
            font-weight: 600; 
            color: #374151; 
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .preview-value { 
            margin-bottom: 20px; 
            color: #1f2937;
            font-size: 16px;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .btn { 
            display: inline-block; 
            padding: 15px 30px; 
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff; 
            border-radius: 8px; 
            text-decoration: none; 
            font-weight: 600;
            font-size: 16px;
            margin-top: 30px;
            text-align: center;
            width: 100%;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }
        .btn:hover { 
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        }
        .preview-img { 
            max-width: 200px; 
            max-height: 200px; 
            border-radius: 12px; 
            border: 3px solid #e5e7eb; 
            margin: 0 auto 20px auto;
            display: block;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .status-processing {
            background: #dbeafe;
            color: #1e40af;
        }
        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }
        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }
        .price-display {
            font-size: 24px;
            font-weight: 700;
            color: #059669;
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #f0fdf4;
            border-radius: 8px;
            border: 2px solid #bbf7d0;
        }
    </style>
</head>
<body>
    <div class="preview-box">
        <h2>Custom Product Preview</h2>
        
        <?php if (!empty($product['image'])): ?>
            <img src="Image/product-add/<?php echo htmlspecialchars($product['image']); ?>" class="preview-img" alt="Custom Product Image">
        <?php endif; ?>
        
        <div class="preview-label">Product Name:</div>
        <div class="preview-value"><?php echo htmlspecialchars($product['name']); ?></div>
        
        <div class="preview-label">Customer Name:</div>
        <div class="preview-value"><?php echo htmlspecialchars($customOrder['customer_name']); ?></div>
        
        <div class="preview-label">Email:</div>
        <div class="preview-value"><?php echo htmlspecialchars($customOrder['email']); ?></div>
        
        <div class="preview-label">Custom Description:</div>
        <div class="preview-value"><?php echo nl2br(htmlspecialchars($customOrder['description'])); ?></div>
        
        <div class="price-display">
            ₱<?php echo number_format($product['price'], 2); ?>
        </div>
        
        <div class="preview-label">Status:</div>
        <div class="preview-value">
            <span class="status-badge status-<?php echo htmlspecialchars($customOrder['status']); ?>">
                <?php echo htmlspecialchars(ucfirst($customOrder['status'])); ?>
            </span>
        </div>
        
        <a href="payment-form.php?order_id=<?php echo $id; ?>" class="btn">Proceed to Purchase</a>
    </div>
</body>
</html> 