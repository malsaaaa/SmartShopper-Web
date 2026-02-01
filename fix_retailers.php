<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$shopee_malaysia = \App\Models\Retailer::where('name', 'Shopee Malaysia')->first();
$shopee = \App\Models\Retailer::where('name', 'Shopee')->first();

if ($shopee_malaysia && $shopee) {
    // Move all prices to Shopee
    \App\Models\Price::where('retailer_id', $shopee_malaysia->id)->update(['retailer_id' => $shopee->id]);
    
    // Delete Shopee Malaysia
    $shopee_malaysia->delete();
    
    echo "✓ Deleted Shopee Malaysia, moved prices to Shopee\n\n";
}

echo "Current Retailers:\n";
foreach (\App\Models\Retailer::all() as $r) {
    echo "- {$r->name}: {$r->prices->count()} prices\n";
}
