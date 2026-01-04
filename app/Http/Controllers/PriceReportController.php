<?php

namespace App\Http\Controllers;

use App\Models\PriceReport;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PriceReportController extends Controller
{
    public function store(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'retailer_id' => 'nullable|exists:retailers,id',
            'reported_price' => 'nullable|numeric|min:0',
            'comment' => 'nullable|string|max:1000',
        ]);

        $report = PriceReport::create([
            'product_id' => $product->id,
            'retailer_id' => $data['retailer_id'] ?? null,
            'user_id' => Auth::id() ?? null,
            'reported_price' => $data['reported_price'] ?? null,
            'comment' => $data['comment'] ?? null,
        ]);

        return back()->with('success', 'Thanks — price report received. We will review it shortly.');
    }
}
