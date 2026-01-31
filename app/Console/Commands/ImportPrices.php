<?php

namespace App\Console\Commands;

use App\Models\Price;
use App\Models\Product;
use App\Models\Retailer;
use Illuminate\Console\Command;

class ImportPrices extends Command
{
    protected $signature = 'prices:import';
    protected $description = 'Scrape and import prices from retailers';

    public function handle()
    {
        $this->info('🔄 Starting price scraping...');
        
        try {
            // Run the Python scraper
            $output = $this->runScraper();
            
            if (!$output) {
                $this->error('❌ Scraper returned no data');
                return 1;
            }
            
            // Parse JSON output
            $items = json_decode($output, true);
            
            if (!is_array($items) || empty($items)) {
                $this->error('❌ Invalid JSON from scraper');
                return 1;
            }
            
            $count = 0;
            $errors = 0;
            
            foreach ($items as $item) {
                try {
                    // Validate required fields
                    if (!isset($item['name'], $item['retailer'], $item['price'])) {
                        $errors++;
                        continue;
                    }
                    
                    // Find or create retailer
                    $retailer = Retailer::firstOrCreate(
                        ['name' => $item['retailer']],
                        ['name' => $item['retailer']]
                    );
                    
                    // Find or create product (with image URL if available)
                    $product = Product::firstOrCreate(
                        ['name' => $item['name']],
                        [
                            'name' => $item['name'],
                            'description' => $item['name'],
                            'category' => $item['category'] ?? 'Products',
                            'image_url' => $item['image_url'] ?? null
                        ]
                    );
                    
                    // Update image URL if a new one is provided
                    if (isset($item['image_url']) && !$product->image_url) {
                        $product->update(['image_url' => $item['image_url']]);
                    }
                    
                    // Create price entry
                    Price::create([
                        'product_id' => $product->id,
                        'retailer_id' => $retailer->id,
                        'amount' => (float) $item['price'],
                        'scraped_at' => now(),
                    ]);
                    
                    $count++;
                    $this->line("✓ {$item['name']} ({$item['retailer']}): RM {$item['price']}");
                    
                } catch (\Exception $e) {
                    $errors++;
                    $this->warn("⚠️ Error: {$e->getMessage()}");
                }
            }
            
            $this->info("\n✅ Import complete!");
            $this->info("📊 Summary: {$count} prices imported");
            if ($errors > 0) {
                $this->warn("⚠️ {$errors} items had errors");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
    
    private function runScraper()
    {
        $scriptPath = base_path('scrapers/scrape_products.py');
        
        if (!file_exists($scriptPath)) {
            throw new \Exception("Scraper script not found at: {$scriptPath}");
        }
        
        // Run Python scraper using the virtual environment
        $pythonPath = base_path('.venv/Scripts/python.exe');
        
        if (!file_exists($pythonPath)) {
            // Fallback to system python
            $pythonPath = 'python';
        }
        
        // Run Python scraper and capture output, redirecting stderr to null
        $command = escapeshellarg($pythonPath) . " " . escapeshellarg($scriptPath) . " 2>nul";
        $output = shell_exec($command);
        
        // Extract only the last line which should be the JSON
        $lines = array_filter(explode("\n", trim($output)));
        $lastLine = end($lines);
        
        return $lastLine;
    }
}
