<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Retailer;
use App\Models\Price;
use App\Models\User;
use App\Models\PriceReport;
use App\Models\ScraperSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function index()
    {
        $stats = [
            'total_products' => Product::count(),
            'total_retailers' => Retailer::count(),
            'total_prices' => Price::count(),
            'total_users' => User::count(),
            'total_admins' => User::where('is_admin', true)->count(),
            'price_reports' => PriceReport::where('status', 'pending')->count(),
        ];

        // Scraping Stats
        $lastPrice = Price::latest('updated_at')->first();
        $scrapingStats = [
            'last_scrape' => $lastPrice?->updated_at ?? null,
            'products_with_prices' => Price::distinct('product_id')->count(),
            'price_coverage' => $this->getPriceCoverage(),
            'latest_prices' => Price::with('product', 'retailer')->latest('updated_at')->take(5)->get(),
        ];

        $recent_products = Product::latest()->take(5)->get();
        $recent_reports = PriceReport::where('status', 'pending')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recent_products', 'recent_reports', 'scrapingStats'));
    }

    /**
     * Calculate price coverage by retailer
     */
    private function getPriceCoverage()
    {
        $retailers = Retailer::all();
        $totalProducts = Product::count();
        $coverage = [];

        foreach ($retailers as $retailer) {
            $count = Price::where('retailer_id', $retailer->id)->distinct('product_id')->count();
            $coverage[] = [
                'retailer' => $retailer->name,
                'count' => $count,
                'percentage' => $totalProducts > 0 ? round(($count / $totalProducts) * 100, 1) : 0,
            ];
        }

        return $coverage;
    }

    /**
     * Display products management page
     */
    public function products(Request $request)
    {
        $query = Product::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $products = $query->paginate(15);
        $categories = Product::distinct()->pluck('category');

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show create product form
     */
    public function createProduct()
    {
        return view('admin.products.create');
    }

    /**
     * Store new product
     */
    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:products',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'image_url' => 'nullable|url',
        ]);

        Product::create($validated);

        return redirect()->route('admin.products')->with('success', 'Product created successfully');
    }

    /**
     * Show edit product form
     */
    public function editProduct(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update product
     */
    public function updateProduct(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'image_url' => 'nullable|url',
        ]);

        $product->update($validated);

        return redirect()->route('admin.products')->with('success', 'Product updated successfully');
    }

    /**
     * Delete product
     */
    public function deleteProduct(Product $product)
    {
        $product->prices()->delete();
        $product->delete();

        return redirect()->route('admin.products')->with('success', 'Product deleted successfully');
    }

    /**
     * Display retailers management page
     */
    public function retailers()
    {
        $retailers = Retailer::withCount('prices')->paginate(10);
        return view('admin.retailers.index', compact('retailers'));
    }

    /**
     * Show create retailer form
     */
    public function createRetailer()
    {
        return view('admin.retailers.create');
    }

    /**
     * Store new retailer
     */
    public function storeRetailer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:retailers',
            'base_url' => 'nullable|url',
            'logo_url' => 'nullable|url',
        ]);

        Retailer::create($validated);

        return redirect()->route('admin.retailers')->with('success', 'Retailer created successfully');
    }

    /**
     * Show edit retailer form
     */
    public function editRetailer(Retailer $retailer)
    {
        return view('admin.retailers.edit', compact('retailer'));
    }

    /**
     * Update retailer
     */
    public function updateRetailer(Request $request, Retailer $retailer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_url' => 'nullable|url',
            'logo_url' => 'nullable|url',
        ]);

        $retailer->update($validated);

        return redirect()->route('admin.retailers')->with('success', 'Retailer updated successfully');
    }

    /**
     * Display prices management page
     */
    public function prices(Request $request)
    {
        // Get all prices with latest first
        $allPrices = Price::with('product', 'retailer')
            ->orderBy('updated_at', 'desc')
            ->get();
        
        // Deduplicate by product-retailer combination
        $uniquePrices = $allPrices->unique(function($item) {
            return $item->product_id . '-' . $item->retailer_id;
        })->values();

        // Apply filters after deduplication
        if ($request->has('product_id') && $request->product_id) {
            $uniquePrices = $uniquePrices->where('product_id', $request->product_id)->values();
        }

        if ($request->has('retailer_id') && $request->retailer_id) {
            $uniquePrices = $uniquePrices->where('retailer_id', $request->retailer_id)->values();
        }

        // Pagination with LengthAwarePaginator
        $perPage = 20;
        $page = $request->input('page', 1);
        $total = count($uniquePrices);
        $items = $uniquePrices->forPage($page, $perPage);
        
        $prices = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $products = Product::pluck('name', 'id');
        $retailers = Retailer::pluck('name', 'id');

        return view('admin.prices.index', compact('prices', 'products', 'retailers'));
    }

    /**
     * Update price
     */
    public function updatePrice(Request $request, Price $price)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'product_url' => 'nullable|url',
        ]);

        $price->update($validated);

        return back()->with('success', 'Price updated successfully');
    }

    /**
     * Delete price (remove product from retailer)
     */
    public function deletePrice(Price $price)
    {
        $productName = $price->product->name;
        $retailerName = $price->retailer->name;
        
        $price->delete();

        return back()->with('success', "Removed {$productName} from {$retailerName}");
    }

    /**
     * Display users management page
     */
    public function users(Request $request)
    {
        $query = User::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $users = $query->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Toggle admin status for a user
     */
    public function toggleAdmin(User $user)
    {
        // Prevent removing your own admin status
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot remove your own admin status');
        }

        $user->update(['is_admin' => !$user->is_admin]);

        $status = $user->is_admin ? 'promoted to admin' : 'demoted from admin';
        return back()->with('success', "User {$status} successfully");
    }

    /**
     * Display price reports
     */
    public function reports(Request $request)
    {
        $query = PriceReport::with('product', 'user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->paginate(15);
        return view('admin.reports.index', compact('reports'));
    }

    /**
     * Update report status
     */
    public function updateReportStatus(Request $request, PriceReport $report)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,resolved,rejected',
            'admin_notes' => 'nullable|string',
        ]);

        $report->update($validated);

        return back()->with('success', 'Report updated successfully');
    }

    /**
     * Run the web scraper
     */
    public function runScraper()
    {
        try {
            Artisan::call('scraper:run');
            
            return response()->json([
                'success' => true,
                'message' => 'Scraper completed successfully! Refresh to see updated data.',
                'output' => Artisan::output()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error running scraper: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display scraper settings page
     */
    public function scraperSettings()
    {
        $settings = ScraperSetting::getInstance();
        $frequencyOptions = ScraperSetting::frequencyOptions();

        // Get last 20 lines of scraper log
        $logFile = storage_path('logs/scraper.log');
        $logs = [];
        if (file_exists($logFile)) {
            $lines = file($logFile);
            $logs = array_reverse(array_slice($lines, -50));
        }

        return view('admin.settings.scraper', compact('settings', 'frequencyOptions', 'logs'));
    }

    /**
     * Update scraper settings
     */
    public function updateScraperSettings(Request $request)
    {
        $validated = $request->validate([
            'auto_scrape_enabled' => 'nullable|boolean',
            'scrape_frequency' => 'required|in:hourly,every_6_hours,every_12_hours,daily',
            'scrape_time' => 'required|date_format:H:i',
            'timezone' => 'required|timezone',
        ]);

        $settings = ScraperSetting::getInstance();
        $settings->update([
            'auto_scrape_enabled' => $request->has('auto_scrape_enabled'),
            'scrape_frequency' => $validated['scrape_frequency'],
            'scrape_time' => $validated['scrape_time'],
            'timezone' => $validated['timezone'],
        ]);

        return back()->with('success', 'Scraper settings updated successfully. Schedule will apply at next scheduler run.');
    }

    /**
     * Clear scraper logs
     */
    public function clearScraperLogs()
    {
        $logFile = storage_path('logs/scraper.log');
        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
        }

        return back()->with('success', 'Scraper logs cleared');
    }
}

