<?php namespace MathiasGrimm\LaravelDotEnvGen;

use Illuminate\Support\ServiceProvider;

class DotEnvGenServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('command.env.gen', function ($app) {
            return $app['MathiasGrimm\LaravelDotEnvGen\DotEnvGenCommand'];
        });

        $this->commands('command.env.gen');
    }
}
