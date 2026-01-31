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
        Schema::create('scraper_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('auto_scrape_enabled')->default(true);
            $table->string('scrape_frequency')->default('daily'); // daily, hourly, every_6_hours, every_12_hours
            $table->string('scrape_time')->default('02:00'); // Time for daily scrapes
            $table->string('timezone')->default('Asia/Kuala_Lumpur');
            $table->integer('last_scrape_timestamp')->nullable();
            $table->integer('next_scrape_timestamp')->nullable();
            $table->text('last_scrape_status')->nullable(); // success, failed, running
            $table->text('last_scrape_message')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        \DB::table('scraper_settings')->insert([
            'auto_scrape_enabled' => true,
            'scrape_frequency' => 'daily',
            'scrape_time' => '02:00',
            'timezone' => 'Asia/Kuala_Lumpur',
            'last_scrape_status' => 'not_run',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scraper_settings');
    }
};
