# laravel-dot-env-gen
php artisan command that generates a .env.gen file based on the existing project source code.

Analises for not used .env variables and variables that are used but not defined on the .env file

# Composer

`"mathiasgrimm/laravel-dot-env-gen": "dev-master"`

## Adding to Laravel

You can use it with Laravel 4 and/or Laravel 5

## Laravel 4

Add the following line to your app/start/artisan.php

`Artisan::add(new mathiasgrimm\laraveldotenv\EnvGen());`

## Laravel 5

First you need to add the service provider.

Open your `config/app.php` file and add the following serice provider:

`'mathiasgrimm\laraveldotenvgen\ServiceProvider',`

Then add the following to the `app/Console/Kernel.php` `$commands` array

`'dotenvgen',`

## Executing the command

`php artisan env:gen`

The file `.env.gen` will be generated on your project root

### Output

Along with the .env.gen file generation, the command will tell you if a .env variable is not used anywhere and/or if an
environment variable is being used but is not defined on the .env file

![alt tag](http://img.ctrlv.in/img/15/03/04/54f78bb973fa9.png)
