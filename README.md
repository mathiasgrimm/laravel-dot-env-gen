# laravel-dot-env-gen
php artisan command that generates a .env.gen file based on the existing project source code.<br>
Analises for not used .env variables and variables that are used but not defined on the .env file

# Composer

<pre>
"mathiasgrimm/laravel-dot-env-gen": "dev-master"
</pre>

# Adding to Laravel

You can use it with Laravel 4 and/or Laravel 5


Laravel 4
---------
Add the following line to your app/start/artisan.php

Artisan::add(new mathiasgrimm\laraveldotenv\EnvGen());

Laravel 5
---------
First you need to add the service provider.<br>
Open your app/config/app.php file and add the following serice provider:

<pre>
	'mathiasgrimm\laraveldotenvgen\ServiceProvider' ,
</pre>

Then add the following to the app/Console/Kernel.php $commands array

<pre>
	'dotenvgen' ,
</pre>


# Executing the command
<pre>
php artisan env:gen
</pre>

The file .env.gen will be generated on your project root

Output
------
Along with the .env.gen file generation, the command will tell you if a .env variable is not used anywhere and/or if a
enviroment variable is being used but not defined on the .env file