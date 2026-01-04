<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Run the migration
    public function up() {
        Schema::create('budgets', function (Blueprint $table) {
         $table->id();
         $table->foreignId('user_id')->constrained()->onDelete('cascade');
         $table->decimal('limit_amount', 10, 2); // e.g., 500.00
         $table->decimal('current_spending', 10, 2)->default(0);
         $table->timestamps();
        });
    }

    // Reverse the migration
    public function down() {
        Schema::dropIfExists('budgets');
    }
};
