<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
public function run()
{
    // 1. Create Admin User
    $admin = \App\Models\User::create([
        'name' => 'Admin',
        'email' => 'admin@smartshopper.com',
        'password' => bcrypt('admin123'),
        'is_admin' => true,
    ]);

    // 2. Create Admin Budget
    \App\Models\Budget::create([
        'user_id' => $admin->id,
        'limit_amount' => 10000.00,
        'current_spending' => 0.00,
    ]);

    // 3. Create a Test User
    $user = \App\Models\User::create([
        'name' => 'Test Student',
        'email' => 'student@test.com',
        'password' => bcrypt('password'), // simple password
        'is_admin' => false,
    ]);

    // 4. Create a Budget for this user (Limit: RM 500)
    \App\Models\Budget::create([
        'user_id' => $user->id,
        'limit_amount' => 500.00,
        'current_spending' => 0.00,
    ]);
}
}
