<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Retailer;
use App\Models\Price;
use Illuminate\Console\Command;

class PopulateProductsAndPrices extends Command
{
    protected $signature = 'populate:products-prices';
    protected $description = 'Instantly populate all products and prices with test data';

    public function handle()
    {
        // Products: 3 categories × 3 brands each
        $products = [
            // Maggi (3 products)
            ['name' => 'Maggi 2-Minute Noodles Original', 'category' => 'Maggi', 'image_url' => 'https://via.placeholder.com/100?text=Maggi+Original'],
            ['name' => 'Maggi 2-Minute Noodles Chicken', 'category' => 'Maggi', 'image_url' => 'https://via.placeholder.com/100?text=Maggi+Chicken'],
            ['name' => 'Maggi 2-Minute Noodles Tomato', 'category' => 'Maggi', 'image_url' => 'https://via.placeholder.com/100?text=Maggi+Tomato'],
            
            // Cooking Oil (3 products)
            ['name' => 'Saji Cooking Oil 1L', 'category' => 'Cooking Oil', 'image_url' => 'https://via.placeholder.com/100?text=Saji+Oil'],
            ['name' => 'Aluanna Cooking Oil 1L', 'category' => 'Cooking Oil', 'image_url' => 'https://via.placeholder.com/100?text=Aluanna+Oil'],
            ['name' => 'Knife Cooking Oil 1L', 'category' => 'Cooking Oil', 'image_url' => 'https://via.placeholder.com/100?text=Knife+Oil'],
            
            // Condensed Milk (3 products)
            ['name' => 'Carnation Condensed Milk 397g', 'category' => 'Condensed Milk', 'image_url' => 'https://via.placeholder.com/100?text=Carnation'],
            ['name' => 'Ideal Condensed Milk 397g', 'category' => 'Condensed Milk', 'image_url' => 'https://via.placeholder.com/100?text=Ideal'],
            ['name' => 'Sweetened Condensed Milk 397g', 'category' => 'Condensed Milk', 'image_url' => 'https://via.placeholder.com/100?text=Sweetened'],
        ];

        // Test prices: Maggi (0.65-0.70), Oil (5.99-6.90), Milk (2.50-3.20)
        $prices = [
            // Maggi prices
            [0.70, 0.65, 0.68, 0.67], // Original
            [0.70, 0.65, 0.60, 0.68], // Chicken
            [0.72, 0.68, 0.65, 0.70], // Tomato
            
            // Oil prices
            [6.90, 6.80, 6.50, 6.75], // Saji
            [6.60, 6.50, 6.20, 6.45], // Aluanna
            [6.30, 6.20, 5.99, 6.15], // Knife
            
            // Milk prices
            [3.20, 3.10, 2.85, 3.00], // Carnation
            [2.95, 2.85, 2.60, 2.75], // Ideal
            [2.70, 2.60, 2.40, 2.55], // Sweetened
        ];

        $this->info('Creating products...');
        foreach ($products as $product) {
            Product::firstOrCreate(
                ['name' => $product['name']],
                ['category' => $product['category'], 'image_url' => $product['image_url']]
            );
        }
        $this->info('✓ Products created');

        // Get all retailers in order: Mydin, Shopee
        $retailerNames = ['Mydin', 'Shopee'];
        $retailers = collect($retailerNames)->map(fn($name) => Retailer::where('name', $name)->first())->filter()->values();

        $this->info('Creating prices for all products × retailers...');
        
        $allProducts = Product::all();
        $priceIndex = 0;

        foreach ($allProducts as $product) {
            $productPrices = $prices[$priceIndex] ?? [6.50, 6.40, 6.20, 6.30];
            
            foreach ($retailers as $retailerIndex => $retailer) {
                Price::firstOrCreate(
                    ['product_id' => $product->id, 'retailer_id' => $retailer->id],
                    [
                        'amount' => $productPrices[$retailerIndex],
                        'scraped_at' => now(),
                        'product_url' => $retailer->base_url ?? 'https://www.google.com/search?q=' . urlencode($product->name),
                    ]
                );
            }
            $priceIndex++;
        }

        $this->info('✓ Prices created');
        $this->info('Total: ' . count($allProducts) . ' products × ' . count($retailers) . ' retailers = ' . (count($allProducts) * count($retailers)) . ' prices');
        $this->info('Done! Dashboard ready to view.');
    }
}
