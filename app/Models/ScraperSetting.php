<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScraperSetting extends Model
{
    use HasFactory;

    protected $table = 'scraper_settings';

    protected $fillable = [
        'auto_scrape_enabled',
        'scrape_frequency',
        'scrape_time',
        'timezone',
        'last_scrape_timestamp',
        'next_scrape_timestamp',
        'last_scrape_status',
        'last_scrape_message',
    ];

    protected $casts = [
        'auto_scrape_enabled' => 'boolean',
        'last_scrape_timestamp' => 'timestamp',
        'next_scrape_timestamp' => 'timestamp',
    ];

    /**
     * Get the singleton instance
     */
    public static function getInstance()
    {
        return self::first() ?? self::create([
            'auto_scrape_enabled' => true,
            'scrape_frequency' => 'daily',
            'scrape_time' => '02:00',
            'timezone' => 'Asia/Kuala_Lumpur',
        ]);
    }

    /**
     * Get frequency options
     */
    public static function frequencyOptions()
    {
        return [
            'hourly' => 'Every Hour',
            'every_6_hours' => 'Every 6 Hours',
            'every_12_hours' => 'Every 12 Hours',
            'daily' => 'Daily',
        ];
    }
}
