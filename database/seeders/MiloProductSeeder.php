<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Price;
use App\Models\Retailer;

class MiloProductSeeder extends Seeder
{
    /**
     * Run the database seeds for Milo products
     * Migrates price comparison data from milo_price_comparison.json
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

        // Milo products with prices from both retailers
        $products = [
            [
                'name' => 'Milo Activ-Go Chocolate Malt Beverage 400g',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/milo-activ-go-400g.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 12.50,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=milo',
                    ],
                    'Mydin' => [
                        'amount' => 13.99,
                        'product_url' => 'https://www.mydin.my/search?q=milo',
                    ],
                ],
            ],
            [
                'name' => 'Milo Regular Powdered Drink 1kg',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/milo-regular-1kg.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 18.90,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=milo',
                    ],
                    'Mydin' => [
                        'amount' => 19.50,
                        'product_url' => 'https://www.mydin.my/search?q=milo',
                    ],
                ],
            ],
            [
                'name' => 'Milo 3in1 Less Sugar 20 Sticks',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/milo-3in1-20sticks.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 8.50,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=milo',
                    ],
                    'Mydin' => [
                        'amount' => 9.20,
                        'product_url' => 'https://www.mydin.my/search?q=milo',
                    ],
                ],
            ],
            [
                'name' => 'Milo Chocolate Powder 400g',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/milo-powder-400g.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 11.99,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=milo',
                    ],
                    'Mydin' => [
                        'amount' => 12.99,
                        'product_url' => 'https://www.mydin.my/search?q=milo',
                    ],
                ],
            ],
            [
                'name' => 'Milo Activ-Go 600g',
                'category' => 'Grocery',
                'image_url' => 'https://down-my.img.susercontent.com/milo-activ-go-600g.jpg',
                'prices' => [
                    'Shopee' => [
                        'amount' => 14.50,
                        'product_url' => 'https://shopee.com.my/supermarket?keyword=milo',
                    ],
                    'Mydin' => [
                        'amount' => 16.50,
                        'product_url' => 'https://www.mydin.my/search?q=milo',
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

        $this->command->info('✅ Migrated ' . count($products) . ' Milo products to database');
    }
}
