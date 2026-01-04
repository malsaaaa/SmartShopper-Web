<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Retailer extends Model
{
    use HasFactory;

    // This line allows 'name', 'base_url', and 'logo_url' to be saved
    protected $fillable = ['name', 'base_url', 'logo_url']; 
}