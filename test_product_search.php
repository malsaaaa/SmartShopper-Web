<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

// Test 1: Get all products
$allProducts = Product::with('prices.retailer')->get();
echo "Total products: " . $allProducts->count() . "\n\n";

// Test 2: Search for Samsung
echo "=== Samsung Search Results ===\n";
$samsung = Product::where('name', 'LIKE', '%samsung%')
    ->orWhere('category', 'LIKE', '%samsung%')
    ->with('prices.retailer')
    ->get();
echo "Found: " . $samsung->count() . " products\n";
foreach ($samsung as $product) {
    echo "  - {$product->name} (RM {$product->prices->first()?->amount})\n";
}

// Test 3: Search for iPhone
echo "\n=== iPhone Search Results ===\n";
$iphone = Product::where('name', 'LIKE', '%iphone%')
    ->orWhere('category', 'LIKE', '%iphone%')
    ->with('prices.retailer')
    ->get();
echo "Found: " . $iphone->count() . " products\n";
foreach ($iphone as $product) {
    echo "  - {$product->name} (RM {$product->prices->first()?->amount})\n";
}

// Test 4: Search for Gaming Laptop
echo "\n=== Gaming Laptop Search Results ===\n";
$laptop = Product::where('name', 'LIKE', '%gaming laptop%')
    ->orWhere('category', 'LIKE', '%gaming laptop%')
    ->with('prices.retailer')
    ->get();
echo "Found: " . $laptop->count() . " products\n";
foreach ($laptop as $product) {
    echo "  - {$product->name} (RM {$product->prices->first()?->amount})\n";
}

// Test 5: By Category
echo "\n=== Products by Category ===\n";
$categories = Product::distinct('category')->pluck('category');
foreach ($categories as $cat) {
    $count = Product::where('category', $cat)->count();
    echo "$cat: $count products\n";
}
