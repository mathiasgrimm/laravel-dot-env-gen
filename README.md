# laravel-dot-env-gen
A Laravel 5 command that generates a `.env.gen` file containing all environment
variables defined in `.env` as well as any undefined variables that are being
used throughout the project.

# Installation

Install the package using composer:

```bash
composer require mathiasgrimm/laravel-dot-env-gen:dev-master
```

Add the service provider:

```php
// config/app.php

'providers' => [
	...
	'MathiasGrimm\LaravelDotEnvGen\DotEnvGenServiceProvider',
	...
],
```

# Usage

From the command line, run `php artisan env:gen`.

A `.env.gen` file will be generated in your project's root folder. Make any
changes you may need, then rename the file to `.env`.

Along with generating the `.env.gen` file, the command will notify you if a
defined environment variable is unused as well as alert you if an undefined
environment variable is being used.

## Screenshot

![Screenshot](screenshot.png)
