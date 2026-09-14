<?php

namespace App\Providers;

use Illuminate\Foundation\DevCommands;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Bref includes Octane, but this blog uses the regular PHP server locally.
        DevCommands::artisan('serve', 'server');
        DevCommands::only('server', 'vite');
    }
}
