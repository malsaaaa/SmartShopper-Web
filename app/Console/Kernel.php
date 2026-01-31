<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\ScraperSetting;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Get scraper settings from database
        try {
            $settings = ScraperSetting::getInstance();
            
            // Only schedule if auto scraping is enabled
            if ($settings->auto_scrape_enabled) {
                $command = $schedule->command('scraper:run')
                    ->withoutOverlapping()
                    ->appendOutputTo(storage_path('logs/scraper.log'))
                    ->timezone($settings->timezone);

                // Apply frequency from settings
                switch ($settings->scrape_frequency) {
                    case 'hourly':
                        $command->hourly();
                        break;
                    case 'every_6_hours':
                        $command->everyThreeHours();
                        break;
                    case 'every_12_hours':
                        $command->everySixHours();
                        break;
                    case 'daily':
                    default:
                        $command->dailyAt($settings->scrape_time);
                        break;
                }
            }
        } catch (\Exception $e) {
            // If database not ready yet (fresh install), use default schedule
            $schedule->command('scraper:run')
                ->dailyAt('02:00')
                ->timezone('Asia/Kuala_Lumpur')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/scraper.log'));
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
