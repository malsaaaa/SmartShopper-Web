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
            // Add image_url, wishlist status, and notification settings
            if (!Schema::hasColumn('shopping_items', 'image_url')) {
                $table->string('image_url')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('shopping_items', 'is_wishlist')) {
                $table->boolean('is_wishlist')->default(false)->after('image_url');
            }
            if (!Schema::hasColumn('shopping_items', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_wishlist');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shopping_items', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'is_wishlist', 'sort_order']);
        });
    }
};
