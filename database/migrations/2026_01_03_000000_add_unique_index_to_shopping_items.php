<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shopping_items', function (Blueprint $table) {
            // Prevent duplicate items for same user/name/retailer
            $table->unique(['user_id', 'name', 'retailer'], 'shopping_items_user_name_retailer_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shopping_items', function (Blueprint $table) {
            $table->dropUnique('shopping_items_user_name_retailer_unique');
        });
    }
};