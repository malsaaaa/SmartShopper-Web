<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test query
$products = \App\Models\Product::where('name', 'LIKE', '%Milo%')->with('prices.retailer')->get();

echo "\n✅ MILO PRODUCTS MIGRATED TO DATABASE\n";
echo "================================================================================\n";
echo sprintf("Found %d Milo products\n\n", count($products));

foreach ($products as $product) {
    echo $product->name . "\n";
    echo "  Category: " . $product->category . "\n";
    
    foreach ($product->prices as $price) {
        echo sprintf("    %s: RM %.2f\n", $price->retailer->name, $price->amount);
    }
    echo "\n";
}

$totalPrices = \App\Models\Price::whereIn('product_id', $products->pluck('id'))->count();
echo sprintf("📊 Total: %d Milo products with %d price entries\n", count($products), $totalPrices);
echo "================================================================================\n";
