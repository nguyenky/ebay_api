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
        $schedule->command('command:ebay-product')
                 ->everyMinute();

            // $schedule->call(function(){
            //      \Log::info('Job [Ebay] 123 ');
            // })->everyMinute();
        // $schedule->job(new UploadProductToEbay(auth()->user()))
        //          ->daily();
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
