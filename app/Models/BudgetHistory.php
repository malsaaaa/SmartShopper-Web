<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetHistory extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'limit_amount', 'current_spending', 'recorded_at'];

    protected $casts = [
        'recorded_at' => 'date',
        'limit_amount' => 'decimal:2',
        'current_spending' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
