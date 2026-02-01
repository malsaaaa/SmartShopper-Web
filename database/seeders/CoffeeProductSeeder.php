<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Price;
use App\Models\Retailer;

class CoffeeProductSeeder extends Seeder
{
    /**
     * Run the database seeds for Coffee products
     * Migrates price comparison data from coffee_price_comparison.json
     */
    public function run()
    {
        // Get retailers
        $mydin = Retailer::where('name', 'Mydin')->first();
        $shopee = Retailer::where('name', 'Shopee')->first();

        if (!$mydin || !$shopee) {
            $this->command->error('Retailers not found. Run RetailerProductSeeder first.');
            return;
        }

        // Coffee products with prices from both retailers
        $products = [
            [
                'name' => 'Nescafé Classic Instant Coffee 100g',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/nescafe-classic-100g.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 8.90,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=coffee',
                    ],
                    'Mydin' => [
                        'amount' => 9.50,
                        'product_url' => 'https://www.mydin.my/search?q=coffee',
                    ],
                ],
            ],
            [
                'name' => 'Nescafé Gold Instant Coffee 200g',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/nescafe-gold-200g.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 15.50,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=coffee',
                    ],
                    'Mydin' => [
                        'amount' => 16.80,
                        'product_url' => 'https://www.mydin.my/search?q=coffee',
                    ],
                ],
            ],
            [
                'name' => 'Kopiko Black Coffee Bag 30 Sachets',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/kopiko-black-30s.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 12.00,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=coffee',
                    ],
                    'Mydin' => [
                        'amount' => 13.20,
                        'product_url' => 'https://www.mydin.my/search?q=coffee',
                    ],
                ],
            ],
            [
                'name' => 'Nescafé 3-in-1 Original Mix 10 Sachets',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/nescafe-3in1-10s.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 4.20,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=coffee',
                    ],
                    'Mydin' => [
                        'amount' => 4.80,
                        'product_url' => 'https://www.mydin.my/search?q=coffee',
                    ],
                ],
            ],
            [
                'name' => 'Nescafé Dolce Gusto Espresso Capsule 16 Pack',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/nescafe-dolce-16.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 18.99,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=coffee',
                    ],
                    'Mydin' => [
                        'amount' => 20.50,
                        'product_url' => 'https://www.mydin.my/search?q=coffee',
                    ],
                ],
            ],
            [
                'name' => 'Illy Ground Coffee 250g Medium Roast',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/illy-ground-250g.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 22.50,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=coffee',
                    ],
                    'Mydin' => [
                        'amount' => 24.90,
                        'product_url' => 'https://www.mydin.my/search?q=coffee',
                    ],
                ],
            ],
            [
                'name' => 'Lavazza Espresso Coffee Beans 250g',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/lavazza-espresso-250g.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 19.90,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=coffee',
                    ],
                    'Mydin' => [
                        'amount' => 21.50,
                        'product_url' => 'https://www.mydin.my/search?q=coffee',
                    ],
                ],
            ],
            [
                'name' => 'Jacobs Kronung Instant Coffee 200g',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/jacobs-kronung-200g.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 16.80,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=coffee',
                    ],
                    'Mydin' => [
                        'amount' => 18.20,
                        'product_url' => 'https://www.mydin.my/search?q=coffee',
                    ],
                ],
            ],
            [
                'name' => 'Highlands Coffee 200g Vietnam Original',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/highlands-coffee-200g.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 14.50,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=coffee',
                    ],
                    'Mydin' => [
                        'amount' => 15.80,
                        'product_url' => 'https://www.mydin.my/search?q=coffee',
                    ],
                ],
            ],
            [
                'name' => 'Kopi Rajahhh 3-in-1 25 Sachets',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/kopi-rajahhh-25s.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 11.20,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=coffee',
                    ],
                    'Mydin' => [
                        'amount' => 12.50,
                        'product_url' => 'https://www.mydin.my/search?q=coffee',
                    ],
                ],
            ],
        ];

        // Insert products and prices
        foreach ($products as $p) {
            $product = Product::firstOrCreate(
                ['name' => $p['name']],
                ['category' => $p['category'], 'image_url' => $p['image_url']]
            );

            // Update to ensure latest data
            $product->update(['category' => $p['category'], 'image_url' => $p['image_url']]);

            // Create or update prices for each retailer
            foreach ($p['prices'] as $retailerName => $priceData) {
                $retailer = $retailerName === 'Shopee' ? $shopee : $mydin;

                Price::updateOrCreate(
                    ['product_id' => $product->id, 'retailer_id' => $retailer->id],
                    [
                        'amount' => $priceData['amount'],
                        'scraped_at' => now(),
                        'product_url' => $priceData['product_url'],
                    ]
                );
            }
        }

        $this->command->info('✅ Migrated ' . count($products) . ' Coffee products to database');
    }
}
