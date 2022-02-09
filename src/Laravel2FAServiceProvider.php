<?php

namespace Hydrat\Laravel2FA;

use Illuminate\Support\ServiceProvider;

class Laravel2FAServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $dir = realpath(sprintf('%s/..', __DIR__));

        $this->loadMigrationsFrom($dir . '/migrations');
        $this->loadRoutesFrom($dir . '/routes.php');
        $this->loadTranslationsFrom($dir . '/lang', 'laravel-2fa');

        $this->publishes([
            $dir . '/config' => base_path('config'),
            $dir . '/views'  => base_path('resources/views'),
        ]);
    }
}
