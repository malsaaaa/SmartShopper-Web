<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Retailer;
use App\Models\Price;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class ShopeeProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the Shopee retailer (created by RetailerProductSeeder)
        $retailer = Retailer::where('name', 'Shopee')->first();
        
        if (!$retailer) {
            $this->command->error('Shopee retailer not found. Run RetailerProductSeeder first.');
            return;
        }

        // Read the JSON file
        $jsonPath = base_path('shopee_products.json');
        
        if (!File::exists($jsonPath)) {
            $this->command->error("File not found: {$jsonPath}");
            return;
        }

        $jsonContent = File::get($jsonPath);
        $products = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error("Invalid JSON format in shopee_products.json: " . json_last_error_msg());
            return;
        }

        if (!is_array($products)) {
            $this->command->error("Invalid JSON format in shopee_products.json - expected array");
            return;
        }

        $this->command->info("Seeding " . count($products) . " products from Shopee...");

        foreach ($products as $item) {
            try {
                // Determine category based on product name
                $category = $this->categorizeProduct($item['name']);

                // Create or update product
                $product = Product::updateOrCreate(
                    ['name' => $item['name']],
                    [
                        'category' => $category,
                        'image_url' => $item['image_url'] ?? null,
                        'sku' => $this->extractSkuFromUrl($item['url'] ?? ''),
                    ]
                );

                // Extract and store price
                $priceAmount = $this->extractPrice($item['price'] ?? '');
                
                if ($priceAmount > 0) {
                    Price::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'retailer_id' => $retailer->id,
                        ],
                        [
                            'amount' => $priceAmount,
                            'product_url' => $item['url'] ?? null,
                            'scraped_at' => isset($item['scraped_at']) ? Carbon::parse($item['scraped_at']) : now(),
                        ]
                    );

                    $this->command->line("✓ {$product->name} - RM {$priceAmount}");
                }
            } catch (\Exception $e) {
                $this->command->error("Error seeding product: " . ($item['name'] ?? 'Unknown') . " - " . $e->getMessage());
            }
        }

        $this->command->info("Shopee products seeding completed!");
    }

    /**
     * Categorize product based on name
     */
    private function categorizeProduct(string $name): string
    {
        $name = strtolower($name);

        if (str_contains($name, 'samsung')) {
            return 'Samsung';
        }
        if (str_contains($name, 'iphone')) {
            return 'iPhone';
        }
        if (str_contains($name, 'laptop') || str_contains($name, 'notebook')) {
            return 'Gaming Laptop';
        }

        return 'General';
    }

    /**
     * Extract SKU from Shopee URL
     */
    private function extractSkuFromUrl(string $url): ?string
    {
        if (preg_match('/\/p\/(\d+)/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Extract numeric price from price string
     */
    private function extractPrice(string $price): float
    {
        // Remove 'RM' and whitespace, extract numbers and commas
        $cleaned = preg_replace('/[^\d,]/', '', $price);
        // Remove commas
        $cleaned = str_replace(',', '', $cleaned);
        
        return (float) $cleaned;
    }
}
