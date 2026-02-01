<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check all products
$allProducts = \App\Models\Product::all();
$shopeeProducts = \App\Models\Product::where('category', '!=', 'Grocery')->get();
$miloProducts = \App\Models\Product::where('name', 'LIKE', '%Milo%')->get();
$maggiProducts = \App\Models\Product::where('name', 'LIKE', '%Maggi%')->get();
$coffeeProducts = \App\Models\Product::where('name', 'LIKE', '%Coffee%')->orWhere('name', 'LIKE', '%Nescafé%')->orWhere('name', 'LIKE', '%Kopiko%')->orWhere('name', 'LIKE', '%Jacobs%')->orWhere('name', 'LIKE', '%Illy%')->orWhere('name', 'LIKE', '%Lavazza%')->orWhere('name', 'LIKE', '%Highlands%')->orWhere('name', 'LIKE', '%Kopi%')->get();

echo "\n📊 DATABASE PRODUCT SUMMARY\n";
echo "================================================================================\n";
echo sprintf("Total Products: %d\n", count($allProducts));
echo sprintf("Shopee Tech Products (Samsung/iPhone/Gaming): %d\n", count($shopeeProducts));
echo sprintf("Milo Products: %d\n", count($miloProducts));
echo sprintf("Maggi Products: %d\n", count($maggiProducts));
echo sprintf("Coffee Products: %d\n", count($coffeeProducts));

echo "\n📱 SHOPEE TECH PRODUCTS (19):\n";
foreach ($shopeeProducts as $product) {
    echo "  • " . substr($product->name, 0, 65) . "\n";
}

echo "\n📦 MILO PRODUCTS (5):\n";
foreach ($miloProducts as $product) {
    echo "  • " . $product->name . "\n";
}

echo "\n🍜 MAGGI PRODUCTS (10):\n";
foreach ($maggiProducts as $product) {
    echo "  • " . $product->name . "\n";
}

echo "\n☕ COFFEE PRODUCTS (10):\n";
foreach ($coffeeProducts as $product) {
    echo "  • " . $product->name . "\n";
}

// Show total prices
$totalPrices = \App\Models\Price::count();
echo "\n💰 PRICE ENTRIES: $totalPrices\n";
echo "   (Each product typically has 2 price entries - one per retailer)\n";

echo "\n================================================================================\n";
