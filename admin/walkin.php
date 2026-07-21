<?php
session_start();
require_once '../database/db.php';
require_once 'check.php';

// Set this to the ID of your 'Walk-in Customer' user from the database (usually 2)
$guest_user_id = 2; 

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_sale'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $pay_method = $_POST['payment_method']; // 'CASH' or 'GCASH'
    $ref_no = !empty($_POST['reference_number']) ? $_POST['reference_number'] : 'CASH-' . time();

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT name, price, stock FROM products WHERE id = ? AND deleted = 0 FOR UPDATE");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if (!$product || $product['stock'] < $quantity) {
            throw new Exception("Insufficient stock!");
        }

        $total_price = $product['price'] * $quantity;

        // A. Insert Payment (Logic for Cash vs GCash)
        $payment_service = ($pay_method === 'GCASH') ? 'GCash' : 'Cash';
        $payment_image = ($pay_method === 'GCASH') ? 'gcash-logo.png' : 'cash_default.png';

        $stmt = $pdo->prepare("INSERT INTO payments (reference_number, service, image, mobile, amount) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$ref_no, $payment_service, $payment_image, 'Walk-in', $total_price]);
        $payment_id = $pdo->lastInsertId();

        // B. Insert Order
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, payment_id, status, date_completed) VALUES (?, ?, 'completed', NOW())");
        $stmt->execute([$guest_user_id, $payment_id]);
        $order_id = $pdo->lastInsertId();

        // C. Insert Order Item
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $product_id, $quantity, $product['price']]);

        // D. Deduct Stock
        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$quantity, $product_id]);

        $pdo->commit();
        header("Location: orders.php?success=walkin");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $message = $e->getMessage();
    }
}

$products = $pdo->query("SELECT id, name, price, stock FROM products WHERE deleted = 0 AND stock > 0 ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Walk-in Order</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
      body { background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 50%, #e8eaf6 100%); }
    </style>
</head>
<body class="min-h-screen">
    <?php include '../components/layout-header.php'; ?>
    <main class="flex-1 p-4 lg:p-8 ml-0 lg:ml-72 h-[calc(100vh-80px)] overflow-y-auto">
        <div class="max-w-2xl mx-auto">
            <div class="mb-8 flex items-center justify-between">
                <h1 class="text-4xl font-bold text-gray-800">Walk-In Order</h1>
                <a href="orders.php" class="p-2 hover:bg-gray-100 rounded-full transition" aria-label="Cancel and go back to orders" title="Cancel and go back to orders"><i data-lucide="x" class="w-8 h-8 text-gray-400"></i></a>
            </div>

            <div class="bg-white p-8 rounded-3xl shadow-2xl border border-blue-100">
                <form method="POST" class="space-y-6">
                    <!-- Product Selection -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase">Select Product</label>
                        <select name="product_id" required class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none">
                            <option value="">-- Choose Jewelry --</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?> (₱<?php echo number_format($p['price'], 2); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase">Quantity</label>
                        <input type="number" name="quantity" value="1" min="1" required class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none">
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase">Payment Method</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="cursor-pointer border-2 p-4 rounded-xl flex items-center gap-2 hover:bg-gray-50 transition" id="cashLabel">
                                <input type="radio" name="payment_method" value="CASH" checked class="hidden" onclick="toggleRef(false)">
                                <i data-lucide="banknote" class="w-5 h-5 text-green-600"></i> Cash
                            </label>
                            <label class="cursor-pointer border-2 p-4 rounded-xl flex items-center gap-2 hover:bg-gray-50 transition" id="gcashLabel">
                                <input type="radio" name="payment_method" value="GCASH" class="hidden" onclick="toggleRef(true)">
                                <i data-lucide="smartphone" class="w-5 h-5 text-blue-600"></i> GCash
                            </label>
                        </div>
                    </div>

                    <!-- Reference Number (Hidden by default) -->
                    <div id="refField" class="hidden">
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase">GCash Reference Number</label>
                        <input type="text" name="reference_number" placeholder="Enter Ref #" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none">
                    </div>

                    <button type="submit" name="complete_sale" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-5 rounded-2xl font-bold text-xl shadow-lg transform transition hover:-translate-y-1">
                        Confirm Sale
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();
        function toggleRef(show) {
            document.getElementById('refField').classList.toggle('hidden', !show);
            // Visual feedback for selection
            document.getElementById('cashLabel').style.borderColor = show ? '#e5e7eb' : '#2563eb';
            document.getElementById('gcashLabel').style.borderColor = show ? '#2563eb' : '#e5e7eb';
        }
        // Initialize border
        toggleRef(false);
    </script>
</body>
</html>