<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingItem extends Model
{
    // Added 'retailer' so Laravel can save it to the new column
    protected $fillable = ['user_id', 'product_id', 'name', 'retailer', 'price', 'quantity', 'notes', 'image_url', 'is_wishlist', 'sort_order', 'estimated_cost'];

    /**
     * Casts for proper types
     *
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'is_wishlist' => 'boolean',
        'estimated_cost' => 'decimal:2',
    ];

    /**
     * Get the product associated with this shopping item
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who owns this shopping item
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}