<?php

namespace App\Console\Commands;

use App\Models\Price;
use App\Models\Product;
use App\Models\Retailer;
use Illuminate\Console\Command;

class ImportFromParsehub extends Command
{
    protected $signature = 'prices:parsehub {--api-key=} {--project-token=} {--retailer=}';
    protected $description = 'Import prices from ParseHub API';

    public function handle()
    {
        $apiKey = $this->option('api-key') ?: config('services.parsehub.api_key');
        $projectToken = $this->option('project-token');
        $retailerName = $this->option('retailer');

        if (!$apiKey || !$projectToken || !$retailerName) {
            $this->error('Missing required options: --api-key, --project-token, --retailer');
            return 1;
        }

        $this->info("🔄 Fetching data from ParseHub for {$retailerName}...");

        try {
            // Fetch data from ParseHub
            $data = $this->fetchFromParsehub($apiKey, $projectToken);

            if (!$data || empty($data['items'])) {
                $this->warn('No data returned from ParseHub');
                return 0;
            }

            // Find or create retailer
            $retailer = Retailer::firstOrCreate(
                ['name' => $retailerName],
                ['name' => $retailerName]
            );

            $count = 0;
            foreach ($data['items'] as $item) {
                try {
                    if (!isset($item['name']) || !isset($item['price'])) {
                        continue;
                    }

                    // Find or create product
                    $product = Product::firstOrCreate(
                        ['name' => $item['name']],
                        [
                            'name' => $item['name'],
                            'description' => $item['name'],
                            'category' => $item['category'] ?? 'Products',
                            'image_url' => $item['image_url'] ?? null
                        ]
                    );

                    // Update image if provided
                    if (isset($item['image_url']) && !$product->image_url) {
                        $product->update(['image_url' => $item['image_url']]);
                    }

                    // Create price entry
                    Price::create([
                        'product_id' => $product->id,
                        'retailer_id' => $retailer->id,
                        'amount' => (float) $this->extractPrice($item['price']),
                        'scraped_at' => now(),
                    ]);

                    $count++;
                    $this->line("✓ {$item['name']} ({$retailerName}): RM {$item['price']}");

                } catch (\Exception $e) {
                    $this->warn("⚠️ Error: {$e->getMessage()}");
                }
            }

            $this->info("\n✅ Successfully imported {$count} prices from {$retailerName}!");
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            return 1;
        }
    }

    private function fetchFromParsehub($apiKey, $projectToken)
    {
        $url = "https://www.parsehub.com/api/v2/projects/{$projectToken}/last_ready_run/data";
        $params = ['api_key' => $apiKey];

        try {
            $response = \Http::get($url, $params);

            if ($response->successful()) {
                return $response->json();
            } else {
                throw new \Exception("ParseHub API error: " . $response->status());
            }
        } catch (\Exception $e) {
            throw new \Exception("Failed to fetch from ParseHub: {$e->getMessage()}");
        }
    }

    private function extractPrice($priceText)
    {
        // Remove currency symbols and non-numeric characters except decimal point
        $price = preg_replace('/[^\d.]/', '', $priceText);
        return (float) $price ?: 0.0;
    }
}
