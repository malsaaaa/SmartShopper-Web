<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingListShare extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'shared_with_user_id', 'share_token', 'can_edit', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'can_edit' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sharedWithUser()
    {
        return $this->belongsTo(User::class, 'shared_with_user_id');
    }
}
