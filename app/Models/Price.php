<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'retailer_id', 'amount', 'scraped_at', 'product_url'];

    public function retailer()
{
    return $this->belongsTo(Retailer::class);
}

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
