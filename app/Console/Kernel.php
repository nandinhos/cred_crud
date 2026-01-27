<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Backup diário do banco de dados às 2h da manhã
        // Mantém apenas os últimos 7 backups
        $schedule->command('db:backup --keep=7')
            ->dailyAt('02:00')
            ->onOneServer()
            ->runInBackground()
            ->withoutOverlapping();

        // Coleta de métricas do sistema a cada 6 horas
        $schedule->command('metrics:collect')
            ->everySixHours()
            ->onOneServer()
            ->runInBackground()
            ->withoutOverlapping();

        // Limpeza de logs antigos (mantém 14 dias)
        $schedule->command('log:clean')
            ->weekly()
            ->sundays()
            ->at('03:00')
            ->onOneServer();

        // Notificar credenciais expirando em 30 dias (diariamente às 8h)
        $schedule->command('credentials:notify-expiring --days=30')
            ->dailyAt('08:00')
            ->onOneServer()
            ->runInBackground()
            ->withoutOverlapping();

        // Alerta crítico: credenciais expirando em 7 dias (diariamente às 9h)
        $schedule->command('credentials:notify-expiring --days=7')
            ->dailyAt('09:00')
            ->onOneServer()
            ->runInBackground()
            ->withoutOverlapping();
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
