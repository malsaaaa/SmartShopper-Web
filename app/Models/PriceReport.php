<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'retailer_id',
        'user_id',
        'reported_price',
        'comment',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function retailer()
    {
        return $this->belongsTo(Retailer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
