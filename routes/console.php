<?php

use App\Jobs\SincronizarCrm;
use Illuminate\Support\Facades\Schedule;

/*
 * Hostinger ejecuta cada minuto: php artisan schedule:run (sección 06).
 * Sin procesos permanentes: la cola se vacía en cada minuto con --stop-when-empty.
 */
Schedule::job(new SincronizarCrm)->everyMinute()->name('sincronizar-crm')->withoutOverlapping(5);

Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3')->everyMinute()->name('cola')->withoutOverlapping(2);

Schedule::command('sitio:publicar-programados')->everyMinute()->withoutOverlapping(2);

Schedule::command('backup:clean')->dailyAt('02:30');
Schedule::command('backup:run --only-db')->dailyAt('03:00');
Schedule::command('backup:run')->weeklyOn(0, '03:30');

Schedule::command('queue:prune-failed --hours=720')->daily();
Schedule::command('activitylog:clean --days=730')->monthly();
