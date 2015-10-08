# Laravel Dot Env Generator

A Laravel 5 command that generates a `.env.gen` file containing all environment
variables defined in `.env` as well as any undefined variables that are being
used throughout the project.

# Installation

1. Install the package using composer:

    ```bash
    composer require mathiasgrimm/laravel-dot-env-gen:dev-master
    ```

2. Add the service provider in `config/app.php`:

    ```php
    'providers' => [
        MathiasGrimm\LaravelDotEnvGen\DotEnvGenServiceProvider::class,
    ],
    ```

3. Add `.env.gen` to your `.gitignore`

4. *(Suggested)* Add `php artisan env:gen` to composer's `post-update-cmd` scripts in `composer.json`:

    ```json
    "scripts": {
        "post-update-cmd": [
            "php artisan optimize",
            "php artisan env:gen"
        ],
    },
    ```

# Usage

From the command line, run `php artisan env:gen`.

A file named `.env.gen` will be generated in your project's root folder. Make any
changes you may need, then rename the file to `.env`.

Along with generating the `.env.gen` file, the command will notify you if a
defined environment variable is unused as well as alert you if an undefined
environment variable is being used.

## Screenshot

![Screenshot](screenshot.png)
