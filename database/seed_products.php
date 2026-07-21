<?php
// database/seed_products.php - Automatically populates the database with 16 gorgeous dummy jewelry products

require_once 'db.php';

echo "<h2>Populating Dummy Jewelry Catalog</h2>";

$dummyProducts = [
    // Rings (type: ring)
    [
        'name' => 'Classic Crown Diamond Ring',
        'type' => 'ring',
        'price' => 28500.00,
        'image' => '494324954_1183859846336540_5343954234173565708_n.jpg',
        'stock' => 10
    ],
    [
        'name' => '18K Gold Teddy Bear Ring',
        'type' => 'ring',
        'price' => 19500.00,
        'image' => '494325447_1231333955173886_6890510470019316369_n.jpg',
        'stock' => 5
    ],
    [
        'name' => 'Silver Pyramid Statement Ring',
        'type' => 'ring',
        'price' => 14800.00,
        'image' => '494325961_638023342401034_6252364838369281331_n.jpg',
        'stock' => 8
    ],
    [
        'name' => 'Lumina Solitaire Diamond Ring',
        'type' => 'ring',
        'price' => 32000.00,
        'image' => 'prod_luminaring_ring_69f6c61a99850.jpg',
        'stock' => 4
    ],
    [
        'name' => 'Gold Interlocking Hearts Ring',
        'type' => 'ring',
        'price' => 21000.00,
        'image' => '494325402_3695525340743355_4980656315398364942_n.jpg',
        'stock' => 6
    ],
    
    // Charms / Necklaces (type: charm)
    [
        'name' => 'Baroque Pearl Chain Necklace',
        'type' => 'charm',
        'price' => 24500.00,
        'image' => '494325142_544255902069492_8661555517179686959_n.jpg',
        'stock' => 12
    ],
    [
        'name' => 'Handcrafted Heart Pendant Necklace',
        'type' => 'charm',
        'price' => 18900.00,
        'image' => '494327494_692783249965027_2405195819497837038_n.jpg',
        'stock' => 15
    ],
    [
        'name' => 'Vintage Gold Medallion Charm',
        'type' => 'charm',
        'price' => 16500.00,
        'image' => '494327563_1222211736155036_4950370532675623754_n.jpg',
        'stock' => 7
    ],
    
    // Earrings (type: earring)
    [
        'name' => 'Elegant Diamond Drop Earrings',
        'type' => 'earring',
        'price' => 17500.00,
        'image' => '494325148_1842487459659992_5693493788136324930_n.jpg',
        'stock' => 9
    ],
    [
        'name' => '18K Gold Floral Stud Earrings',
        'type' => 'earring',
        'price' => 13200.00,
        'image' => '494325469_706984378569264_5343356117210898483_n.jpg',
        'stock' => 11
    ],
    [
        'name' => 'Ornate Gold Dangle Earrings',
        'type' => 'earring',
        'price' => 15600.00,
        'image' => '494325477_1217376576769319_5120504738643254060_n.jpg',
        'stock' => 6
    ],
    
    // Bracelets (type: bracelet)
    [
        'name' => 'Premium Gold Link Chain Bracelet',
        'type' => 'bracelet',
        'price' => 22000.00,
        'image' => '494325250_681972694322251_1425333947113573545_n.jpg',
        'stock' => 8
    ],
    [
        'name' => 'Engraved Gold Bangle Bracelet',
        'type' => 'bracelet',
        'price' => 26500.00,
        'image' => 'prod_aulusbago_bracelet_69eb20628594e.png',
        'stock' => 5
    ],
    [
        'name' => 'Double Layer Gold Chain Bracelet',
        'type' => 'bracelet',
        'price' => 19800.00,
        'image' => 'prod_12_bracelet_687b5031419d6.jpg',
        'stock' => 14
    ],
    [
        'name' => 'Classic Rope Link Gold Bracelet',
        'type' => 'bracelet',
        'price' => 17200.00,
        'image' => 'prod_687b860346abe9.78028220.jpg',
        'stock' => 10
    ],
    [
        'name' => 'Minimalist Silver Beaded Bracelet',
        'type' => 'bracelet',
        'price' => 9500.00,
        'image' => '494326026_1886425448758881_1175508685880337266_n.jpg',
        'stock' => 20
    ]
];

try {
    // 1. Optional: clear existing active products to prevent duplication during testing
    // We do NOT truncate to prevent deleting custom orders if there are any.
    // Instead, we just delete premade products or mark them.
    // For a fresh start, let's delete all premade products currently in the database to prevent duplicate flooding.
    $pdo->exec("DELETE FROM products WHERE deleted = 0 AND (type IN ('ring', 'charm', 'earring', 'bracelet') OR type IS NULL)");
    echo "<p>Cleared previous active premade products from database to prevent duplication.</p>";
    
    // 2. Insert new list
    $stmt = $pdo->prepare("INSERT INTO products (name, type, price, image, stock, deleted) VALUES (?, ?, ?, ?, ?, 0)");
    
    $insertedCount = 0;
    foreach ($dummyProducts as $product) {
        $stmt->execute([
            $product['name'],
            $product['type'],
            $product['price'],
            $product['image'],
            $product['stock']
        ]);
        $insertedCount++;
    }
    
    echo "<p style='color: green; font-weight: bold;'>✅ Successfully flooded database with {$insertedCount} dummy jewelry products!</p>";
    echo "<p>You can now check the <a href='../shop.php'>Shop Page</a> to view and test purchasing them.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database Error seeding products: " . $e->getMessage() . "</p>";
}
?>
