<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use App\Models\Price;
use App\Models\Product;
use App\Models\Retailer;

class RunScraper extends Command
{
    protected $signature = 'scraper:run';
    protected $description = 'Run the web scraper to fetch product prices from retailers';

    public function handle()
    {
        $this->info('🔄 Starting web scraper...');
        
        try {
            $pythonPath = 'python'; // or 'python3' depending on your system
            $scriptPath = base_path('scrapers/scrape_products.py');
            
            // Run the Python scraper
            $process = new Process([$pythonPath, $scriptPath]);
            $process->setTimeout(300); // 5 minute timeout
            $process->run();

            if (!$process->isSuccessful()) {
                $this->error('Scraper failed: ' . $process->getErrorOutput());
                return 1;
            }

            $this->info($process->getOutput());
            $this->info('✓ Scraper completed successfully!');
            
            // Show stats
            $this->showStats();
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error running scraper: ' . $e->getMessage());
            return 1;
        }
    }

    private function showStats()
    {
        $this->line('');
        $this->info('📊 Current Data:');
        
        $totalProducts = Product::count();
        $totalRetailers = Retailer::count();
        $totalPrices = Price::count();
        
        $this->line("  Products: {$totalProducts}");
        $this->line("  Retailers: {$totalRetailers}");
        $this->line("  Price Records: {$totalPrices}");
        
        $lastPrice = Price::latest('updated_at')->first();
        if ($lastPrice) {
            $this->line("  Last Updated: " . $lastPrice->updated_at->format('M d, Y H:i:s'));
        }
    }
}
