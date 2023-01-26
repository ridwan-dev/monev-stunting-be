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
        Commands\EmonevInterkoneksiKomponenSatker::class,
        Commands\KemenkeuRkaklInterkoneksi::class,
        Commands\KrisnaDakInterkoneksi::class,
        Commands\KrisnaRenjaInterkoneksi::class,
        Commands\TableBackupRenja::class,
        Commands\TableSewaktuRenja::class,
        Commands\TablePemdaDak::class,
        Commands\TableDataDak::class,
        Commands\TableRealisasiRka::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
