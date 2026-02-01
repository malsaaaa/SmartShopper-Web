<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check all products
$allProducts = \App\Models\Product::all();
$shopeeProducts = \App\Models\Product::where('category', '!=', 'Grocery')->get();
$miloProducts = \App\Models\Product::where('name', 'LIKE', '%Milo%')->get();

echo "\n📊 DATABASE PRODUCT SUMMARY\n";
echo "================================================================================\n";
echo sprintf("Total Products: %d\n", count($allProducts));
echo sprintf("Milo Products: %d\n", count($miloProducts));
echo sprintf("Shopee Tech Products (Samsung/iPhone/Gaming): %d\n", count($shopeeProducts));

echo "\n📱 SHOPEE TECH PRODUCTS:\n";
foreach ($shopeeProducts as $product) {
    echo "  • " . substr($product->name, 0, 60) . "\n";
}

echo "\n📦 MILO PRODUCTS:\n";
foreach ($miloProducts as $product) {
    echo "  • " . $product->name . "\n";
}

echo "\n================================================================================\n";
