<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;


class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\UpdateProductToEbayOne::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('command:download-file-csv')
        //          ->daily();
        
        // $schedule->command('command:refresh-token-ebay')
        //           ->everyThirtyMinutes();

        // $schedule->command('command:check-file-csv')
        //           ->daily();

        // $schedule->command('command:create-offer')
        //           ->timezone('America/New_York')
        //             ->at('00:00');
                    
        // $schedule->command('command:create-inventory')
        //           ->timezone('America/New_York')
        //             ->at('12:00');
        $schedule->command('command:public-offer')
                 ->everyMinute();
        // $schedule->job(new \App\Jobs\ebay\PublicOfferEbay, 'public')->everyMinute();
        
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
    // php artisan queue:listen --timeout=0 --queue=uploads
    // php artisan schedule:run >> /dev/null/2>&1
}
