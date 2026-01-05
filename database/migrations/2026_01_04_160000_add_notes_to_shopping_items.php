<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shopping_items', function (Blueprint $table) {
            if (!Schema::hasColumn('shopping_items', 'notes')) {
                $table->text('notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('shopping_items', function (Blueprint $table) {
            if (Schema::hasColumn('shopping_items', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
