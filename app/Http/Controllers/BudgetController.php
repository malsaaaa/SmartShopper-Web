<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Product;
use App\Models\Retailer;
use App\Models\ShoppingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class BudgetController extends Controller
{
    public function index() {
        $products = Product::with('prices')->get();
        $budget = $this->getBudget();

        // Get retailers in a specific display order
        $retailerNames = ["Lotus's", 'Mydin', 'Giant', 'Shopee', 'Lazada'];
        $retailers = collect($retailerNames)->map(fn($name) => Retailer::where('name', $name)->first())->filter()->values();

        return view('dashboard', compact('products', 'budget', 'retailers'));
    }

    public function showBudget() {
        $budget = $this->getBudget();
        $items = ShoppingItem::where('user_id', Auth::id())->get();

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

        return view('budget-list', compact('budget', 'items', 'categoryChartUrl', 'percentSpent', 'labels', 'data', 'colors'));
    }

    public function show($id) {
        $product = Product::with('prices.retailer')->findOrFail($id);
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
        $products = Product::with('prices')->where('name', 'LIKE', "%{$query}%")->get();
        $budget = $this->getBudget();
        return view('search', compact('products', 'budget', 'query'));
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
                    ShoppingItem::create([
                        'user_id' => Auth::id(),
                        'name' => $request->name,
                        'retailer' => $request->retailer,
                        'price' => $request->price,
                        'quantity' => 1,
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
}