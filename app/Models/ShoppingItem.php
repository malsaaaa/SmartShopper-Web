<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingItem extends Model
{
    // Added 'retailer' so Laravel can save it to the new column
    protected $fillable = ['user_id', 'name', 'retailer', 'price', 'quantity'];

    /**
     * Casts for proper types
     *
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];
}