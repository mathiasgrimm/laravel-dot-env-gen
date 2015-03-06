<?php 
namespace mathiasgrimm\laraveldotenvgen;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
	public function boot()
	{
		//
	}

	public function register()
	{
		$this->app->bind('dotenvgen','mathiasgrimm\\laraveldotenvgen\\EnvGen');
	}
}
