<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetHistory;
use App\Models\Product;
use App\Models\Retailer;
use App\Models\ShoppingItem;
use App\Models\ShoppingListShare;
use App\Models\User;
use App\Services\RecommendationService;
use App\Services\ShopeeCrawlerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

class BudgetController extends Controller
{
    public function index() {
        // Get one product from each price comparison category: Milo, Maggi, Coffee
        $milo = Product::where('name', 'LIKE', '%Milo%')->with('prices')->first();
        $maggi = Product::where('name', 'LIKE', '%Maggi%')->with('prices')->first();
        $coffee = Product::where('name', 'LIKE', '%Coffee%')
            ->orWhere('name', 'LIKE', '%Nescafé%')
            ->orWhere('name', 'LIKE', '%Kopiko%')
            ->orWhere('name', 'LIKE', '%Jacobs%')
            ->orWhere('name', 'LIKE', '%Illy%')
            ->orWhere('name', 'LIKE', '%Lavazza%')
            ->orWhere('name', 'LIKE', '%Highlands%')
            ->orWhere('name', 'LIKE', '%Kopi%')
            ->with('prices')
            ->first();
        
        // Create a collection with the three featured products
        $products = collect([$milo, $maggi, $coffee])->filter();
        
        $budget = $this->getBudget();

        // Get retailers in a specific display order
        $retailerNames = ['Mydin', 'Shopee'];
        $retailers = collect($retailerNames)->map(fn($name) => Retailer::where('name', $name)->first())->filter()->values();

        return view('dashboard', compact('products', 'budget', 'retailers'));
    }

    public function syncPrices($id)
    {
        $product = Product::findOrFail($id);
    
        // Example for a Retailer Scrape
        $retailUrl = "https://www.mydin.com.my/product/" . $product->slug;
        $output = shell_exec("python3 " . base_path('scrapers/retail_scraper.py') . " " . escapeshellarg($retailUrl));
        $data = json_decode($output, true);

        // Update prices table with scraped data
        $product->prices()->updateOrCreate(
            ['retailer_id' => 1], // Mydin ID
            [
            'amount' => preg_replace('/[^0-9.]/', '', $data['price']),
            'updated_at' => now()
            ]
        );

        return back()->with('success', 'Marketplace prices synced!');
    }

    public function showBudget() {
        $budget = $this->getBudget();
        $items = ShoppingItem::where('user_id', Auth::id())->get();

        // Load product images for each shopping item from products table
        foreach ($items as $item) {
            $product = Product::where('name', $item->name)->first();
            // Use product's image_url if shopping item doesn't have one
            if (!$item->image_url && $product) {
                $item->image_url = $product->image_url;
            }
        }

        // Get smart recommendations for cheaper alternatives
        $recommendationService = new RecommendationService();
        $recommendations = $recommendationService->getRecommendations($items);
        $totalSavings = $recommendationService->getTotalPotentialSavings($recommendations);
        
        // Get retailer comparison for entire shopping list
        $retailerComparison = $recommendationService->getRetailerComparison($items);

        // Compute spending per category by mapping items back to products (fallback: General)
        $categoryTotals = [];
        foreach ($items as $item) {
            $product = Product::where('name', $item->name)->first();
            $category = $product?->category ?? 'General';
            if (! isset($categoryTotals[$category])) $categoryTotals[$category] = 0;
            $categoryTotals[$category] += (float) $item->price * (int) $item->quantity;
        }

        // Prepare chart data (labels + values)
        if (empty($categoryTotals)) {
            $labels = ['No spend'];
            $data = [1];
            $colors = ['#E5E7EB'];
        } else {
            $labels = array_keys($categoryTotals);
            $data = array_values($categoryTotals);
            // Simple palette — rotate if many categories
            $palette = ['#10B981', '#06B6D4', '#F59E0B', '#EF4444', '#6366F1', '#F97316'];

            // Build colors array using numeric indexes (avoid string modulo)
            $colors = [];
            foreach ($labels as $index => $lbl) {
                $colors[] = $palette[$index % count($palette)];
            }
        }

        $chartConfig = [
            'type' => 'doughnut',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'data' => $data,
                    'backgroundColor' => $colors,
                ]],
            ],
            'options' => [
                'plugins' => [
                    'legend' => ['display' => false]
                ]
            ]
        ];

        $categoryChartUrl = 'https://quickchart.io/chart?c=' . rawurlencode(json_encode($chartConfig));

        $percentSpent = $budget->limit_amount > 0 ? min(100, ($budget->current_spending / $budget->limit_amount) * 100) : 0;

        // Get new features data
        $shoppingTips = $this->getShoppingTips();
        $budgetHistory = $this->getBudgetHistory();
        $sharedWith = ShoppingListShare::where('user_id', Auth::id())->with('sharedWithUser')->get();

        return view('budget-list', compact('budget', 'items', 'recommendations', 'totalSavings', 'categoryChartUrl', 'percentSpent', 'labels', 'data', 'colors', 'shoppingTips', 'budgetHistory', 'sharedWith', 'retailerComparison'));
    }

    public function show($id) {
        $product = Product::with('prices.retailer')->findOrFail($id);
        
        // Remove duplicate prices - keep only latest per retailer
        $uniquePrices = $product->prices
            ->unique('retailer_id')
            ->values();
        $product->setRelation('prices', $uniquePrices);
        
        $budget = $this->getBudget();

        // Build price trend based on Price.scraped_at grouped by date
        $points = $product->prices()
            ->selectRaw("DATE(scraped_at) as date, MIN(NULLIF(amount, 0)) as min_amount")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($r) => ['date' => $r->date, 'amount' => (float) $r->min_amount]);

        if ($points->isEmpty()) {
            // Fallback: use current best price repeated across the last 7 days
            $best = $product->bestPriceWithRetailer();
            $labels = collect(range(6,0))->map(fn($d) => now()->subDays($d)->format('d M'))->toArray();
            $data = collect(range(0,6))->map(fn() => (float) $best['amount'])->toArray();
        } else {
            $labels = $points->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->toArray();
            $data = $points->pluck('amount')->map(fn($v) => (float) ($v ?? 0))->toArray();
        }

        $chartConfig = ['type' => 'line', 'data' => ['labels' => $labels, 'datasets' => [['label' => 'Price', 'data' => $data, 'fill' => false, 'borderColor' => 'rgb(16,185,129)', 'tension' => 0.4]]]];
        $chartUrl = 'https://quickchart.io/chart?c=' . rawurlencode(json_encode($chartConfig));

        return view('product-detail', compact('product', 'budget', 'chartUrl'));
    }

    public function search(Request $request) {
        $query = $request->input('query');
        $category = $request->input('category');
        $source = $request->input('source', 'shopee'); // Default to shopee for real data
        
        $products = [];
        $crawledProducts = [];
        $crawlDuration = 0;
        $useDatabase = false;
        
        // Check if query matches milo, maggi, or coffee - search database first
        $lowerQuery = strtolower(trim($query));
        if (in_array($lowerQuery, ['milo', 'maggi', 'coffee'])) {
            $useDatabase = true;
            $source = 'database';
            
            // Search database for matching products
            if ($lowerQuery === 'milo') {
                $products = Product::where('name', 'LIKE', '%Milo%')->with('prices')->get();
            } elseif ($lowerQuery === 'maggi') {
                $products = Product::where('name', 'LIKE', '%Maggi%')->with('prices')->get();
            } elseif ($lowerQuery === 'coffee') {
                $products = Product::where('name', 'LIKE', '%Coffee%')
                    ->orWhere('name', 'LIKE', '%Nescafé%')
                    ->orWhere('name', 'LIKE', '%Kopiko%')
                    ->orWhere('name', 'LIKE', '%Jacobs%')
                    ->orWhere('name', 'LIKE', '%Illy%')
                    ->orWhere('name', 'LIKE', '%Lavazza%')
                    ->orWhere('name', 'LIKE', '%Highlands%')
                    ->orWhere('name', 'LIKE', '%Kopi%')
                    ->with('prices')
                    ->get();
            }
        } else {
            // Crawl Shopee for products (only if query is provided)
            if (!empty(trim($query))) {
                $startTime = microtime(true);
                
                $crawlerService = new ShopeeCrawlerService();
                $crawledData = $crawlerService->search(trim($query), 10); // Limit to 10 products
                $crawledProducts = $crawlerService->formatProducts($crawledData);
                
                $crawlDuration = round((microtime(true) - $startTime) * 1000); // ms
                
                // Log crawler status
                \Log::info('Shopee Crawler Results', [
                    'query' => $query,
                    'product_count' => count($crawledProducts),
                    'crawl_duration_ms' => $crawlDuration,
                    'first_product' => $crawledProducts[0] ?? null
                ]);
            }
            
            // Search database products if requested
            if ($source === 'all' || $source === 'database') {
                $productsQuery = Product::with('prices')->where('name', 'LIKE', "%{$query}%");
                
                if ($category) {
                    $productsQuery->where('category', $category);
                }
                
                $products = $productsQuery->get(); // Keep as Collection, not array
            }
        }
        
        // Get all available categories for filter
        $allCategories = Product::select('category')->distinct()->orderBy('category')->pluck('category');
        $budget = $this->getBudget();
        
        // Get retailers for price comparison
        $retailerNames = ['Mydin', 'Shopee'];
        $retailers = collect($retailerNames)->map(fn($name) => Retailer::where('name', $name)->first())->filter()->values();

        return view('search', compact(
            'products', 
            'budget', 
            'query', 
            'category', 
            'allCategories',
            'crawledProducts',
            'crawlDuration',
            'source',
            'retailers'
        ));
    }

    public function check(Request $request) {
        // Validate inputs to avoid bad data
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'retailer' => 'nullable|string|max:255',
        ]);

        // Use a transaction + row-level lock to avoid races when incrementing
        try {
            DB::transaction(function () use ($request) {
                $item = ShoppingItem::where('user_id', Auth::id())
                                    ->where('name', $request->name)
                                    ->where('retailer', $request->retailer)
                                    ->lockForUpdate()
                                    ->first();

                if ($item) {
                    $item->increment('quantity');
                    if ((float) $item->price !== (float) $request->price) {
                        $item->update(['price' => $request->price]);
                    }
                } else {
                    // Find product image URL
                    $product = Product::where('name', $request->name)->first();
                    $imageUrl = $product?->image_url ?? null;
                    
                    ShoppingItem::create([
                        'user_id' => Auth::id(),
                        'name' => $request->name,
                        'retailer' => $request->retailer,
                        'price' => $request->price,
                        'quantity' => 1,
                        'image_url' => $imageUrl,
                    ]);
                }
            });
        } catch (QueryException $e) {
            // Unique constraint race — try to recover by incrementing the existing row
            $item = ShoppingItem::where('user_id', Auth::id())
                                ->where('name', $request->name)
                                ->where('retailer', $request->retailer)
                                ->first();

            if ($item) {
                $item->increment('quantity');
                if ((float) $item->price !== (float) $request->price) {
                    $item->update(['price' => $request->price]);
                }
            } else {
                // rethrow if we cannot resolve the conflict
                throw $e;
            }
        }

        $this->syncTotal();

        // Check if budget exceeded and send Telegram notification
        $budget = $this->getBudget();
        if ($budget->current_spending > $budget->limit_amount) {
            $telegramController = new TelegramBotController();
            $telegramController->sendBudgetAlert(Auth::id(), $budget->limit_amount, $budget->current_spending);
        }

        return back()->with('success', "Item added to plan.");
    }

    public function toggleAlert(Request $request, $id) {
        return back()->with('success', "Price alert set!");
    }

    public function updateQuantity($id, $action) {
        $item = ShoppingItem::where('user_id', Auth::id())->findOrFail($id);
        if ($action === 'add') $item->increment('quantity');
        elseif ($action === 'sub' && $item->quantity > 1) $item->decrement('quantity');
        $this->syncTotal();
        return back();
    }

    public function removeItem($id) {
        ShoppingItem::where('user_id', Auth::id())->where('id', $id)->delete();
        $this->syncTotal();
        return back();
    }

    public function clearList() {
        ShoppingItem::where('user_id', Auth::id())->delete();
        $this->syncTotal();
        return back();
    }

    public function updateLimit(Request $request) {
        $request->validate(['limit' => 'required|numeric|min:1']);
        Budget::where('user_id', Auth::id())->update(['limit_amount' => $request->limit]);
        return back();
    }

    public function updateItemNote(Request $request, $id) {
        $request->validate(['notes' => 'nullable|string|max:500']);
        ShoppingItem::where('user_id', Auth::id())->where('id', $id)->update(['notes' => $request->notes]);
        return back()->with('success', 'Note updated!');
    }

    public function getBudgetHistory() {
        $history = \App\Models\BudgetHistory::where('user_id', Auth::id())
            ->orderBy('recorded_at', 'desc')
            ->limit(30)
            ->get();
        
        return $history;
    }

    public function getShoppingTips() {
        $items = ShoppingItem::where('user_id', Auth::id())->get();
        $tips = [];

        if ($items->count() > 0) {
            $totalSpent = (float) $items->sum(fn($item) => $item->price * $item->quantity);
            $avgPrice = $totalSpent / $items->count();

            // Tip 1: High-quantity items
            $highQtyItems = $items->where('quantity', '>', 2);
            if ($highQtyItems->count() > 0) {
                $tips[] = [
                    'icon' => '📦',
                    'title' => 'Bulk Buying',
                    'message' => "You're buying {$highQtyItems->count()} items in bulk. Consider checking for bulk discounts!"
                ];
            }

            // Tip 2: Single item check
            if ($items->count() === 1) {
                $tips[] = [
                    'icon' => '🛒',
                    'title' => 'Add More Items',
                    'message' => 'You only have 1 item. Add more to compare prices across retailers!'
                ];
            }

            // Tip 3: Spending alert
            $budget = $this->getBudget();
            if ($budget->current_spending > $budget->limit_amount * 0.5) {
                $tips[] = [
                    'icon' => '💰',
                    'title' => 'Budget Usage',
                    'message' => 'You\'ve used over 50% of your budget. Review your shopping list to stay on track.'
                ];
            }

            // Tip 4: Variety tip
            $categories = Product::whereIn('name', $items->pluck('name')->toArray())
                ->select('category')
                ->distinct()
                ->count();
            
            if ($categories <= 1) {
                $tips[] = [
                    'icon' => '🥗',
                    'title' => 'Diversify',
                    'message' => 'Consider shopping across different categories for a balanced budget.'
                ];
            }
        }

        return $tips;
    }

    public function shareList(Request $request) {
        $request->validate(['email' => 'required|email|exists:users,email']);
        
        $sharedUser = \App\Models\User::where('email', $request->email)->first();
        
        if ($sharedUser->id === Auth::id()) {
            return back()->with('error', 'Cannot share with yourself!');
        }

        \App\Models\ShoppingListShare::firstOrCreate([
            'user_id' => Auth::id(),
            'shared_with_user_id' => $sharedUser->id,
        ], [
            'share_token' => \Illuminate\Support\Str::random(32),
            'can_edit' => false,
        ]);

        return back()->with('success', 'Shopping list shared with ' . $sharedUser->name . '!');
    }

    public function toggleWishlist(Request $request, $id) {
        $item = ShoppingItem::where('user_id', Auth::id())->where('id', $id)->first();
        if ($item) {
            $item->update(['is_wishlist' => !$item->is_wishlist]);
        }
        return back()->with('success', 'Item updated!');
    }

    public function addItemImage(Request $request, $id) {
        $request->validate(['image_url' => 'required|url']);
        ShoppingItem::where('user_id', Auth::id())->where('id', $id)->update(['image_url' => $request->image_url]);
        return back()->with('success', 'Image added!');
    }

    private function syncTotal() {
        $total = (float) ShoppingItem::where('user_id', Auth::id())
            ->selectRaw('COALESCE(SUM(price * quantity), 0) as total')
            ->value('total');

        // Ensure Budget exists and update atomically
        $budget = Budget::firstOrCreate(
            ['user_id' => Auth::id()],
            ['limit_amount' => 1000, 'current_spending' => 0]
        );

        $budget->update(['current_spending' => $total]);
    }

    private function getBudget() {
        return Auth::check() 
            ? Budget::firstOrCreate(['user_id' => Auth::id()], ['limit_amount' => 1000, 'current_spending' => 0])
            : (object)['limit_amount' => 1000, 'current_spending' => 0];
    }

    public function showProfile()
    {
        $user = Auth::user();
        $shoppingListCount = ShoppingItem::where('user_id', $user->id)->count();
        $budgetHistoryCount = BudgetHistory::where('user_id', $user->id)->count();
        $reportCount = \App\Models\PriceReport::where('user_id', $user->id)->count();
        $currentBudget = Budget::where('user_id', $user->id)->first();

        return view('profile', compact('user', 'shoppingListCount', 'budgetHistoryCount', 'reportCount', 'currentBudget'));
    }
}