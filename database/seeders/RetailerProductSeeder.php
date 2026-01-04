<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Retailer;
use App\Models\Product;
use App\Models\Price;

class RetailerProductSeeder extends Seeder
{
    public function run()
    {
        // Retailers
        $retailers = [
            "Lotus's",
            'Mydin',
            'Giant',
            'Shopee',
            'Lazada',
        ];

        $retailerBaseUrls = [
            "Lotus's" => 'https://www.lotus.com',
            'Mydin' => 'https://www.mydin.com',
            'Giant' => 'https://www.giant.com.my',
            'Shopee' => 'https://shopee.com.my',
            'Lazada' => 'https://www.lazada.com.my',
        ];

        $retailerLogos = [
            "Lotus's" => '/images/retailers/lotuss.svg',
            'Mydin' => '/images/retailers/mydin.svg',
            'Giant' => '/images/retailers/giant.svg',
            'Shopee' => '/images/retailers/shopee.svg',
            'Lazada' => '/images/retailers/lazada.svg',
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
                    "Lotus's" => 35.50,
                    'Mydin' => 36.20,
                    'Giant' => 37.30,
                    'Shopee' => 39.00,
                    'Lazada' => 38.50,
                ],
            ],
            [
                'name' => 'Saji Cooking Oil 1KG',
                'category' => 'Grocery',
                'image_url' => '/images/products/saji-cooking-oil-1kg.svg',
                'prices' => [
                    "Lotus's" => 6.90,
                    'Mydin' => 6.80,
                    'Giant' => 7.00,
                    'Shopee' => 6.50,
                    'Lazada' => 6.60,
                ],
            ],
            [
                'name' => 'Milo 1KG',
                'category' => 'Grocery',
                'image_url' => '/images/products/milo-1kg.svg',
                'prices' => [
                    "Lotus's" => 25.90,
                    'Mydin' => 24.50,
                    'Giant' => 26.00,
                    'Shopee' => 27.00,
                    'Lazada' => 26.50,
                ],
            ],
            [
                'name' => 'Samsung Galaxy Note 4',
                'category' => 'Electronics',
                'image_url' => '/images/products/samsung-galaxy-note-4.svg',
                'prices' => [
                    "Lotus's" => 0.00,
                    'Mydin' => 0.00,
                    'Giant' => 0.00,
                    'Shopee' => 650.00,
                    'Lazada' => 645.00,
                ],
            ],
            [
                'name' => 'HP Victus 15',
                'category' => 'Electronics',
                'image_url' => '/images/products/hp-victus-15.svg',
                'prices' => [
                    "Lotus's" => 0.00,
                    'Mydin' => 0.00,
                    'Giant' => 0.00,
                    'Shopee' => 4399.00,
                    'Lazada' => 4299.00,
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
