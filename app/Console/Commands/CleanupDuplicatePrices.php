<?php

namespace App\Console\Commands;

use App\Models\Price;
use Illuminate\Console\Command;

class CleanupDuplicatePrices extends Command
{
    protected $signature = 'cleanup:duplicate-prices';
    protected $description = 'Remove duplicate price records, keeping only the latest per product-retailer';

    public function handle()
    {
        $this->info('Starting cleanup of duplicate prices...');

        // Get all prices
        $allPrices = Price::orderBy('updated_at', 'desc')->get();
        
        $seen = [];
        $deletedCount = 0;

        foreach ($allPrices as $price) {
            $key = $price->product_id . '-' . $price->retailer_id;
            
            if (isset($seen[$key])) {
                // This is a duplicate, delete it
                $price->delete();
                $deletedCount++;
            } else {
                // This is the first (latest) occurrence, keep it
                $seen[$key] = $price->id;
            }
        }

        $this->info("✓ Cleanup complete!");
        $this->info("Deleted {$deletedCount} duplicate price records.");
        
        // Show summary
        $totalProducts = \App\Models\Product::count();
        $totalRetailers = \App\Models\Retailer::count();
        $expectedPrices = $totalProducts * $totalRetailers;
        $actualPrices = Price::count();
        
        $this->info("Expected: {$expectedPrices} price records ({$totalProducts} products × {$totalRetailers} retailers)");
        $this->info("Actual: {$actualPrices} price records");
    }
}
