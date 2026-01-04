<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Run the migration
    public function up() {
        Schema::create('prices', function (Blueprint $table) {
         $table->id();
         $table->foreignId('product_id')->constrained()->onDelete('cascade');
         $table->foreignId('retailer_id')->constrained()->onDelete('cascade');
         $table->decimal('amount', 8, 2); // The price (e.g., 12.99)
         $table->timestamp('scraped_at'); // When did we find this price?
         $table->timestamps();
        });
    }

    // Reverse the migration
    public function down() {
        Schema::dropIfExists('prices');
    }
};
