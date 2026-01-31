<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Retailer;
use App\Models\Price;
use Illuminate\Console\Command;

class PopulatePricesQuick extends Command
{
    protected $signature = 'prices:populate-quick';
    protected $description = 'Quickly populate prices with test data (no scraping)';

    public function handle()
    {
        // Test price data - realistic variations per retailer
        $priceData = [
            'Maggi 2-Minute Noodles Original' => [
                'Mydin' => 0.65,
                'Shopee' => 0.60,
            ],
            'Maggi 2-Minute Noodles Chicken' => [
                'Mydin' => 0.65,
                'Shopee' => 0.60,
            ],
            'Maggi 2-Minute Noodles Beef' => [
                'Mydin' => 0.70,
                'Shopee' => 0.65,
            ],
            'Saji Cooking Oil 1L' => [
                'Mydin' => 6.80,
                'Shopee' => 6.50,
            ],
            'Aluanna Cooking Oil 1L' => [
                'Mydin' => 6.50,
                'Shopee' => 6.20,
            ],
            'Knife Cooking Oil 1L' => [
                'Mydin' => 6.20,
                'Shopee' => 5.99,
            ],
            'Ideal Condensed Milk 397g' => [
                'Mydin' => 3.40,
                'Shopee' => 3.25,
            ],
            'Carnation Condensed Milk 397g' => [
                'Mydin' => 3.70,
                'Shopee' => 3.50,
            ],
            'F&N Condensed Milk 397g' => [
                'Mydin' => 3.50,
                'Shopee' => 3.30,
            ],
        ];

        $count = 0;
        foreach ($priceData as $productName => $retailers) {
            $product = Product::where('name', $productName)->first();
            
            if (!$product) {
                $this->warn("Product not found: $productName");
                continue;
            }

            foreach ($retailers as $retailerName => $price) {
                $retailer = Retailer::where('name', $retailerName)->first();
                
                if (!$retailer) {
                    $this->warn("Retailer not found: $retailerName");
                    continue;
                }

                Price::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'retailer_id' => $retailer->id,
                    ],
                    [
                        'amount' => $price,
                        'scraped_at' => now(),
                    ]
                );

                $count++;
            }
        }

        $this->info("✓ Populated $count prices in seconds!");
    }
}
