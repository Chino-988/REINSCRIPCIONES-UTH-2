<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Registra comandos de Artisan.
     *
     * @var array<int, class-string>
     */
    protected $commands = [
        \App\Console\Commands\ReferenciasPushDemo::class,
    ];

    /**
     * Define el schedule (opcional).
     */
    protected function schedule(Schedule $schedule): void
    {
        /**
         * Demo automática opcional:
         * Habilita poniendo API_DEMO_ENABLE_CRON=true en .env
         * y corre el scheduler: php artisan schedule:work
         */
        if (env('API_DEMO_ENABLE_CRON', false)) {
            // Cada 5 minutos marca algunos como EN_PROCESO
            $schedule->command('referencias:push-demo --n=5 --status=EN_PROCESO --obs="Robot: verificando"')
                ->everyFiveMinutes();

            // Cada 10 minutos valida algunos
            $schedule->command('referencias:push-demo --n=3 --status=VALIDADO --obs="Robot: confirmado"')
                ->everyTenMinutes();
        }
    }

    /**
     * Registra los comandos para Artisan.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
