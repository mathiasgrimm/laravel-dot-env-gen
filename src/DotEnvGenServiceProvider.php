<?php namespace MathiasGrimm\LaravelDotEnvGen;

use Illuminate\Support\ServiceProvider;

class DotEnvGenServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/dotenvgen.php' => config_path('dotenvgen.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/dotenvgen.php', 'dotenvgen');

        $this->app->singleton('command.env.gen', function ($app) {
            return $app['MathiasGrimm\LaravelDotEnvGen\DotEnvGenCommand'];
        });

        $this->commands('command.env.gen');
    }
}
