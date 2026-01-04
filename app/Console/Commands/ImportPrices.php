<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportPrices extends Command
{
    // Command signature and description
    protected $signature = 'prices:import';
    // Command description
    protected $description = 'Import scraped prices from JSON';

    public function handle() {
    // 1. In a real scenario, you run: exec('python3 scraper.py');
    // 2. For now, let's assume python saved a file 'data.json'
    
    $jsonData = '[
        {"name": "Rice 5kg", "retailer": "Mydin", "price": 24.99},
        {"name": "Milo 1kg", "retailer": "Giant", "price": 18.50}
    ]'; // Mock data representing your scraped output
    
    $items = json_decode($jsonData, true);

    foreach ($items as $item) {
        // Find or Create Retailer
        $retailer = \App\Models\Retailer::firstOrCreate(['name' => $item['retailer']]);

        // Find or Create Product
        $product = \App\Models\Product::firstOrCreate(['name' => $item['name']]);

        // Add Price Entry
        \App\Models\Price::create([
            'product_id' => $product->id,
            'retailer_id' => $retailer->id,
            'amount' => $item['price'],
            'scraped_at' => now(),
        ]);
    }

    $this->info('Prices updated successfully!');
    } 
}
