<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductSearchController extends Controller
{
    /**
     * Search for products by keyword
     * Supported keywords: "samsung", "iphone", "gaming laptop"
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->query('q', '');
        
        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required',
                'data' => []
            ], 400);
        }

        // Search in product name and category
        $products = Product::where('name', 'LIKE', "%{$query}%")
            ->orWhere('category', 'LIKE', "%{$query}%")
            ->with(['prices' => function ($q) {
                $q->with('retailer')->orderBy('created_at', 'desc')->limit(1);
            }])
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category,
                    'image_url' => $product->image_url,
                    'description' => $product->description,
                    'price' => $product->prices->first()?->amount ?? 0,
                    'product_url' => $product->prices->first()?->product_url,
                    'retailer' => $product->prices->first()?->retailer?->name ?? 'Unknown',
                    'scraped_at' => $product->prices->first()?->scraped_at,
                ];
            });

        return response()->json([
            'success' => true,
            'query' => $query,
            'count' => $products->count(),
            'data' => $products
        ]);
    }

    /**
     * Get products by specific category (Samsung, iPhone, Gaming Laptop)
     */
    public function getByCategory(Request $request, string $category): JsonResponse
    {
        // Validate category
        $validCategories = ['Samsung', 'iPhone', 'Gaming Laptop'];
        
        if (!in_array($category, $validCategories)) {
            return response()->json([
                'success' => false,
                'message' => "Invalid category. Valid categories: " . implode(', ', $validCategories),
                'data' => []
            ], 400);
        }

        $products = Product::where('category', $category)
            ->with(['prices' => function ($q) {
                $q->with('retailer')->orderBy('created_at', 'desc')->limit(1);
            }])
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category,
                    'image_url' => $product->image_url,
                    'description' => $product->description,
                    'price' => $product->prices->first()?->amount ?? 0,
                    'product_url' => $product->prices->first()?->product_url,
                    'retailer' => $product->prices->first()?->retailer?->name ?? 'Unknown',
                    'scraped_at' => $product->prices->first()?->scraped_at,
                ];
            });

        return response()->json([
            'success' => true,
            'category' => $category,
            'count' => $products->count(),
            'data' => $products
        ]);
    }

    /**
     * Get all available products with optional filtering
     */
    public function getAll(Request $request): JsonResponse
    {
        $products = Product::with(['prices' => function ($q) {
            $q->with('retailer')->orderBy('created_at', 'desc')->limit(1);
        }])
        ->get()
        ->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category,
                'image_url' => $product->image_url,
                'description' => $product->description,
                'price' => $product->prices->first()?->amount ?? 0,
                'product_url' => $product->prices->first()?->product_url,
                'retailer' => $product->prices->first()?->retailer?->name ?? 'Unknown',
                'scraped_at' => $product->prices->first()?->scraped_at,
            ];
        });

        return response()->json([
            'success' => true,
            'total' => $products->count(),
            'data' => $products
        ]);
    }
}
