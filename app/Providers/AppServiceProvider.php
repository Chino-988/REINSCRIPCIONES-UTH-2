<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Notificacion;
use App\Observers\NotificacionObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Notificacion::observe(NotificacionObserver::class);
    }
}
