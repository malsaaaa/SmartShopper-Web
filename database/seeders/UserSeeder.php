<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
public function run()
{
    // 1. Create a Test User
    $user = \App\Models\User::create([
        'name' => 'Test Student',
        'email' => 'student@test.com',
        'password' => bcrypt('password'), // simple password
    ]);

    // 2. Create a Budget for this user (Limit: RM 500)
    \App\Models\Budget::create([
        'user_id' => $user->id,
        'limit_amount' => 500.00,
        'current_spending' => 0.00,
    ]);
}
}
