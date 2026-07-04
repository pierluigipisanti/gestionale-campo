<?php

namespace App\Providers;

use App\Models\Campo;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Il layout mostra sempre il nome del campo nella topbar (single-tenant).
        View::composer('layouts.app', fn ($view) => $view->with('campoCorrente', Campo::first()));

        // Un solo permesso fine: cose da admin (gestione utenti, eliminazione tende).
        Gate::define('admin', fn (User $user) => $user->isAdmin());
    }
}
