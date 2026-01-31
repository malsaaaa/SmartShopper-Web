<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ShopeeCrawlerService
{
    /**
     * Search Shopee for products
     */
    public function search(string $query, int $maxResults = 10): array
    {
        if (empty(trim($query))) {
            return [];
        }

        // Cache results for 1 hour to reduce API calls
        $cacheKey = 'shopee_search_' . md5($query) . '_' . $maxResults;
        
        if (Cache::has($cacheKey)) {
            Log::info("Returning cached Shopee results for: {$query}");
            return Cache::get($cacheKey);
        }

        // Use JSON files directly - fast and reliable
        $results = $this->getFallbackData($query, $maxResults);
        
        // Cache the results
        Cache::put($cacheKey, $results, now()->addHours(1));
        
        return $results;
    }

    /**
     * Execute Python crawler script
     */
    private function crawlShopee(string $query, int $maxResults): array
    {
        try {
            // Try to find Python executable
            $pythonPath = $this->getPythonExecutable();
            
            if (!$pythonPath) {
                Log::warning("Python executable not found in PATH");
                return [];
            }

            // Create temp file to store Python code
            $tempFile = sys_get_temp_dir() . '/shopee_crawler_' . uniqid() . '.py';
            
            // Build the Python code with proper path quoting
            $scrapersPath = str_replace('\\', '\\\\', base_path('scrapers'));
            
            $pythonCode = sprintf(
                'import sys; sys.path.insert(0, %s); '
                . 'import logging; logging.disable(logging.CRITICAL); '
                . 'from shopee_crawler import ShopeeCrawler; import json; '
                . 'crawler = ShopeeCrawler(); results = crawler.search(%s, %d); print(json.dumps(results))',
                json_encode($scrapersPath),
                json_encode($query),
                $maxResults
            );
            
            file_put_contents($tempFile, $pythonCode);
            
            // Execute Python script
            $output = '';
            $exitCode = 0;
            exec('"' . $pythonPath . '" "' . $tempFile . '"', $output, $exitCode);
            
            // Clean up temp file
            @unlink($tempFile);
            
            if ($exitCode !== 0) {
                Log::warning("Python script exited with code: {$exitCode}", [
                    'exit_code' => $exitCode,
                    'output' => $output,
                    'python_path' => $pythonPath
                ]);
                return [];
            }
            
            // Output is an array, join it
            $jsonString = implode('', $output);
            
            if (empty($jsonString)) {
                Log::warning("No output from Shopee crawler for query: {$query}");
                return [];
            }
            
            $data = json_decode($jsonString, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("JSON decode error: " . json_last_error_msg() . " for query: {$query}");
                Log::debug("Raw output: " . substr($jsonString, 0, 500));
                return [];
            }

            // Data is already an array of products
            if (is_array($data) && count($data) > 0) {
                Log::info("Got " . count($data) . " products from crawler for: {$query}");
                return array_slice($data, 0, $maxResults);
            }

            Log::warning("No products in parsed data for query: {$query}");
            return [];
            
        } catch (\Exception $e) {
            Log::error("Crawler execution failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Find Python executable path
     */
    private function getPythonExecutable(): ?string
    {
        // First try to use the Python from the virtual environment
        $venvPython = base_path('.venv/Scripts/python.exe');
        if (file_exists($venvPython)) {
            return $venvPython;
        }
        
        // Try common Python executable names (py is preferred on Windows)
        $executables = ['py', 'python3', 'python', 'python.exe', 'python3.exe', 'py.exe'];
        
        foreach ($executables as $exe) {
            $result = shell_exec("where {$exe} 2>&1");
            if (!empty($result) && strpos($result, 'not found') === false && strpos($result, 'INFO:') === false) {
                $path = trim(explode("\n", $result)[0]);
                if (!empty($path) && file_exists($path)) {
                    return $path;
                }
            }
        }
        
        return null;
    }

    /**
     * Fallback data when crawler fails
     * Returns real products from JSON files based on search query
     */
    private function getFallbackData(string $query, int $maxResults): array
    {
        Log::info("Using fallback data for query: {$query}");
        
        $queryLower = strtolower(trim($query));
        $products = [];
        
        // Load products based on search term
        if (strpos($queryLower, 'samsung') !== false) {
            $products = $this->loadProductsFromJson('samsung.json');
        } elseif (strpos($queryLower, 'iphone') !== false) {
            $products = $this->loadProductsFromJson('iphone.json');
        } elseif (strpos($queryLower, 'gaming_laptop') !== false || strpos($queryLower, 'gaming laptop') !== false) {
            $products = $this->loadProductsFromJson('gaming_laptop.json');
        }
        
        // If no products found for specific query, return empty
        if (empty($products)) {
            Log::warning("No fallback products found for query: {$query}");
            return [];
        }
        
        return array_slice($products, 0, $maxResults);
    }
    
    /**
     * Load products from JSON file
     */
    private function loadProductsFromJson(string $filename): array
    {
        $filePath = base_path($filename);
        
        if (!file_exists($filePath)) {
            Log::warning("JSON file not found: {$filePath}");
            return [];
        }
        
        try {
            $jsonContent = file_get_contents($filePath);
            $data = json_decode($jsonContent, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Failed to decode JSON file {$filename}: " . json_last_error_msg());
                return [];
            }
            
            if (!is_array($data)) {
                Log::error("JSON file {$filename} does not contain an array");
                return [];
            }
            
            // Convert JSON data to standard format
            $products = [];
            foreach ($data as $item) {
                $products[] = [
                    'name' => $item['Product Name'] ?? 'Unknown Product',
                    'price' => (float)($item['Price'] ?? 0),
                    'original_price' => (float)($item['Price Before Discount'] ?? $item['Price'] ?? 0),
                    'discount' => (int)(str_replace('%', '', $item['Discount'] ?? '0')),
                    'rating' => (float)($item['Rating Star'] ?? 0),
                    'reviews' => (int)($item['Rating Count'] ?? 0),
                    'shop_name' => $item['Shop Name'] ?? 'Shopee',
                    'image_url' => $this->extractFirstImage($item['All Images'] ?? ''),
                    'url' => $item['Product URL'] ?? 'https://shopee.com.my',
                    'is_official_shop' => ($item['Is Official Shop'] ?? 'No') === 'Yes',
                ];
            }
            
            Log::info("Loaded " . count($products) . " products from {$filename}");
            return $products;
            
        } catch (\Exception $e) {
            Log::error("Error loading products from {$filename}: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Extract first image URL from the All Images field
     */
    private function extractFirstImage(string $imageString): ?string
    {
        if (empty($imageString)) {
            return null;
        }
        
        // Split by || and get the first URL
        $images = explode('||', $imageString);
        $firstImage = trim($images[0] ?? '');
        
        return !empty($firstImage) ? $firstImage : null;
    }

    /**
     * Format crawled products for display
     */
    public function formatProducts(array $crawledProducts): array
    {
        return array_map(function ($product) {
            // Handle both old format and new format from crawler
            $price = $product['price'] ?? 0;
            $originalPrice = $product['original_price'] ?? $price;
            
            // Calculate discount if not provided
            $discount = 0;
            if (isset($product['discount'])) {
                $discount = $product['discount'];
            } elseif ($originalPrice > $price && $price > 0) {
                $discount = round((($originalPrice - $price) / $originalPrice) * 100);
            }
            
            return [
                'name' => $product['name'] ?? 'Unknown Product',
                'price' => $price,
                'original_price' => $originalPrice,
                'discount' => $discount,
                'image_url' => $product['image_url'] ?? null,
                'url' => $product['url'] ?? ($product['product_url'] ?? 'https://shopee.com.my'),
                'shop_name' => $product['shop_name'] ?? 'Shopee',
                'rating' => $product['rating'] ?? 0,
                'reviews' => $product['reviews'] ?? 0,
                'is_official_shop' => $product['is_official_shop'] ?? false,
                'retailer' => 'Shopee',
                'source' => 'crawled',
            ];
        }, $crawledProducts);
    }
}
