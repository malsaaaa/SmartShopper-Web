<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ShoppingItem;
use Illuminate\Support\Collection;

class RecommendationService
{
    /**
     * Get price recommendations for shopping items
     * Shows where to buy each item cheaper
     */
    public function getRecommendations(Collection $items): array
    {
        $recommendations = [];

        foreach ($items as $item) {
            // Find the product that matches this shopping item
            $product = Product::where('name', $item->name)->first();
            
            if (!$product) {
                continue;
            }

            // Get all prices for this product with retailer info
            $prices = $product->prices()
                ->with('retailer')
                ->where('amount', '>', 0)
                ->get()
                ->sortBy('amount');

            if ($prices->isEmpty()) {
                continue;
            }

            $cheapestPrice = $prices->first();
            $currentPrice = $item->price;
            $savings = $currentPrice - $cheapestPrice->amount;

            // Only show recommendation if there's actual savings
            if ($savings > 0) {
                $totalSavings = $savings * $item->quantity;

                $recommendations[] = [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'current_price' => $currentPrice,
                    'quantity' => $item->quantity,
                    'current_total' => $currentPrice * $item->quantity,
                    'cheapest_retailer' => $cheapestPrice->retailer->name,
                    'cheapest_price' => $cheapestPrice->amount,
                    'cheapest_total' => $cheapestPrice->amount * $item->quantity,
                    'price_per_unit_savings' => $savings,
                    'total_savings' => $totalSavings,
                    'savings_percentage' => round(($savings / $currentPrice) * 100, 1),
                ];
            }
        }

        // Sort by total savings (highest first)
        usort($recommendations, fn($a, $b) => $b['total_savings'] <=> $a['total_savings']);

        return $recommendations;
    }

    /**
     * Calculate total potential savings across all recommendations
     */
    public function getTotalPotentialSavings(array $recommendations): float
    {
        return array_reduce($recommendations, fn($carry, $rec) => $carry + $rec['total_savings'], 0);
    }

    /**
     * Get retailer comparison: total cost of all items if shopped at each retailer
     */
    public function getRetailerComparison(Collection $items): array
    {
        $retailerTotals = [];

        foreach ($items as $item) {
            $product = Product::where('name', $item->name)->first();
            
            if (!$product) {
                continue;
            }

            $prices = $product->prices()
                ->with('retailer')
                ->where('amount', '>', 0)
                ->get();

            foreach ($prices as $price) {
                $retailerName = $price->retailer->name;
                
                if (!isset($retailerTotals[$retailerName])) {
                    $retailerTotals[$retailerName] = [
                        'retailer_name' => $retailerName,
                        'total_cost' => 0,
                        'item_count' => 0,
                        'items' => []
                    ];
                }

                $itemCost = $price->amount * $item->quantity;
                $retailerTotals[$retailerName]['total_cost'] += $itemCost;
                $retailerTotals[$retailerName]['item_count']++;
                $retailerTotals[$retailerName]['items'][] = [
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'price' => $price->amount,
                    'subtotal' => $itemCost
                ];
            }
        }

        usort($retailerTotals, fn($a, $b) => $a['total_cost'] <=> $b['total_cost']);

        if (!empty($retailerTotals)) {
            $mostExpensive = end($retailerTotals);
            $cheapest = reset($retailerTotals);

            foreach ($retailerTotals as &$retailer) {
                $retailer['savings_vs_most_expensive'] = $mostExpensive['total_cost'] - $retailer['total_cost'];
                $retailer['savings_percentage'] = $mostExpensive['total_cost'] > 0 
                    ? round(($retailer['savings_vs_most_expensive'] / $mostExpensive['total_cost']) * 100, 1)
                    : 0;
                $retailer['is_cheapest'] = $retailer['retailer_name'] === $cheapest['retailer_name'];
            }
        }

        return $retailerTotals;
    }
}
