<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Retailer;
use App\Models\Product;
use App\Models\Price;
use App\Models\ShoppingItem;

class RetailerProductSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear old retailers and products (delete in correct order due to foreign keys)
        Price::truncate();
        ShoppingItem::truncate();
        Product::truncate();
        Retailer::truncate();
        
        // Re-enable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Retailers - Only Mydin & Shopee
        $retailers = [
            'Mydin',
            'Shopee',
        ];

        $retailerBaseUrls = [
            'Mydin' => 'https://www.mydin.com',
            'Shopee' => 'https://shopee.com.my',
        ];

        $retailerLogos = [
            'Mydin' => '/images/retailers/mydin.svg',
            'Shopee' => '/images/retailers/shopee.svg',
        ];

        $retailerMap = [];
        foreach ($retailers as $name) {
            $base = $retailerBaseUrls[$name] ?? null;
            $logo = $retailerLogos[$name] ?? '/images/retailers/default.svg';
            $r = Retailer::updateOrCreate(['name' => $name], ['base_url' => $base, 'logo_url' => $logo]);
            $retailerMap[$name] = $r;
        }

        // Products and prices (amounts are in RM)
        $products = [
            [
                'name' => 'Beras Faiza Emas 10KG',
                'category' => 'Grocery',
                'image_url' => '/images/products/beras-faiza-10kg.svg',
                'prices' => [
                    'Mydin' => 36.20,
                    'Shopee' => 39.00,
                ],
            ],
            [
                'name' => 'Saji Cooking Oil 1KG',
                'category' => 'Grocery',
                'image_url' => '/images/products/saji-cooking-oil-1kg.svg',
                'prices' => [
                    'Mydin' => 6.80,
                    'Shopee' => 6.50,
                ],
            ],
            [
                'name' => 'Milo 1KG',
                'category' => 'Grocery',
                'image_url' => '/images/products/milo-1kg.svg',
                'prices' => [
                    'Mydin' => 24.50,
                    'Shopee' => 27.00,
                ],
            ],
            [
                'name' => 'Samsung Galaxy Note 4',
                'category' => 'Electronics',
                'image_url' => '/images/products/samsung-galaxy-note-4.svg',
                'prices' => [
                    'Shopee' => 650.00,
                ],
            ],
            [
                'name' => 'HP Victus 15',
                'category' => 'Electronics',
                'image_url' => '/images/products/hp-victus-15.svg',
                'prices' => [
                    'Shopee' => 4399.00,
                ],
            ],
        ];

        foreach ($products as $p) {
            $product = Product::firstOrCreate(
                ['name' => $p['name']],
                ['category' => $p['category'], 'image_url' => $p['image_url']]
            );

            // Ensure product has the latest category and image (in case seeder is re-run)
            $product->update(['category' => $p['category'], 'image_url' => $p['image_url']]);

            foreach ($p['prices'] as $retailerName => $amount) {
                $retailer = $retailerMap[$retailerName] ?? null;
                if (! $retailer) continue;

                // Use updateOrCreate to avoid duplicate price rows on re-seeding
                // Add a plausible product_url per retailer for demo purposes when available
                $demoProductUrl = null;
                if ($retailer->base_url) {
                    $demoProductUrl = rtrim($retailer->base_url, '/') . '/search?q=' . urlencode($product->name);
                }

                Price::updateOrCreate(
                    ['product_id' => $product->id, 'retailer_id' => $retailer->id],
                    ['amount' => $amount, 'scraped_at' => now(), 'product_url' => $demoProductUrl]
                );
            }
        }
    }
}
