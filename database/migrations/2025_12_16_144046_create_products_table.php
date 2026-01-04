<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Run the migrations.
    public function up() {
        Schema::create('products', function (Blueprint $table) {
         $table->id();
         $table->string('name');
         $table->string('sku')->nullable(); // Unique ID from scraper
         $table->text('image_url')->nullable();
         $table->string('category')->default('General');
         $table->timestamps();
        });
    }

    // Reverse the migrations.
    public function down() {
        Schema::dropIfExists('products');
    }
};
