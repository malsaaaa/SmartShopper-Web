<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model {

    use HasFactory;
    protected $fillable = ['name', 'description', 'category', 'image_url'];

    public function prices() {
        return $this->hasMany(Price::class);
    }
    
    // Helper to get latest price
    public function currentPrice() {
        return $this->hasOne(Price::class)->latestOfMany();
    }

    /**
     * Return the best (lowest non-zero) price and its retailer for this product.
     *
     * @return array{amount: float, retailer: \App\Models\Retailer|null}
     */
    public function bestPriceWithRetailer(): array
    {
        $best = $this->prices->filter(fn($p) => $p->amount > 0)->sortBy('amount')->first();
        return [
            'amount' => $best?->amount ?? 0,
            'retailer' => $best?->retailer ?? null,
        ];
    }
}
