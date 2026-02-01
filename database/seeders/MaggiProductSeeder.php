<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Price;
use App\Models\Retailer;

class MaggiProductSeeder extends Seeder
{
    /**
     * Run the database seeds for Maggi products
     * Migrates price comparison data from maggi_price_comparison.json
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

        // Maggi products with prices from both retailers
        $products = [
            [
                'name' => 'Maggi 2-Minute Noodles Curry Flavour 5 Pack',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/maggi-curry-5pack.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 2.50,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=maggi',
                    ],
                    'Mydin' => [
                        'amount' => 2.80,
                        'product_url' => 'https://www.mydin.my/search?q=maggi',
                    ],
                ],
            ],
            [
                'name' => 'Maggi 2-Minute Noodles Chicken Flavour 5 Pack',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/maggi-chicken-5pack.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 2.50,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=maggi',
                    ],
                    'Mydin' => [
                        'amount' => 2.80,
                        'product_url' => 'https://www.mydin.my/search?q=maggi',
                    ],
                ],
            ],
            [
                'name' => 'Maggi Seasoning 200g Bottle',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/maggi-seasoning-200g.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 8.50,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=maggi',
                    ],
                    'Mydin' => [
                        'amount' => 9.20,
                        'product_url' => 'https://www.mydin.my/search?q=maggi',
                    ],
                ],
            ],
            [
                'name' => 'Maggi Hot & Spicy Noodles 5 Pack',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/maggi-hot-spicy-5pack.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 3.20,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=maggi',
                    ],
                    'Mydin' => [
                        'amount' => 3.50,
                        'product_url' => 'https://www.mydin.my/search?q=maggi',
                    ],
                ],
            ],
            [
                'name' => 'Maggi Cube Seasoning 18 Cubes',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/maggi-cube-seasoning.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 4.50,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=maggi',
                    ],
                    'Mydin' => [
                        'amount' => 5.00,
                        'product_url' => 'https://www.mydin.my/search?q=maggi',
                    ],
                ],
            ],
            [
                'name' => 'Maggi Tomato Ketchup 340g',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/maggi-ketchup-340g.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 5.50,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=maggi',
                    ],
                    'Mydin' => [
                        'amount' => 6.20,
                        'product_url' => 'https://www.mydin.my/search?q=maggi',
                    ],
                ],
            ],
            [
                'name' => 'Maggi Oyster Sauce 400g',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/maggi-oyster-sauce.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 6.20,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=maggi',
                    ],
                    'Mydin' => [
                        'amount' => 6.80,
                        'product_url' => 'https://www.mydin.my/search?q=maggi',
                    ],
                ],
            ],
            [
                'name' => 'Maggi Chili Sauce 340g',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/maggi-chili-sauce.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 5.80,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=maggi',
                    ],
                    'Mydin' => [
                        'amount' => 6.50,
                        'product_url' => 'https://www.mydin.my/search?q=maggi',
                    ],
                ],
            ],
            [
                'name' => 'Maggi Soy Sauce 400ml',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/maggi-soy-sauce.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 4.80,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=maggi',
                    ],
                    'Mydin' => [
                        'amount' => 5.50,
                        'product_url' => 'https://www.mydin.my/search?q=maggi',
                    ],
                ],
            ],
            [
                'name' => 'Maggi Calamansi Seasoning 150g',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/maggi-calamansi.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 7.50,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=maggi',
                    ],
                    'Mydin' => [
                        'amount' => 8.20,
                        'product_url' => 'https://www.mydin.my/search?q=maggi',
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

        $this->command->info('✅ Migrated ' . count($products) . ' Maggi products to database');
    }
}
