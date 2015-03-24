<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Directory Exclusion Rules
    |--------------------------------------------------------------------------
    |
    | To speed up the process of scanning for `env()` calls, you may choose to
    | ignore various directories. To ignore a directory, you must place the
    | path to the directory relative to the application's root in a new
    | key. If you wish to ignore all but specific subdirectories, you must
    | place those subdirectories in the right hand side of the array entry.
    |
    | By default, we are ignoring all files & folders in the `vendor`
    | directory except those in the `vendor/laravel` subdirectory.
    |
    | To ignore all files in the `vendor` directory except those in the
    | `vendor/laravel` subdirectory, you may use the following:
    |
    |     'rules' => [
    |         'vendor' => ['laravel'],
    |     ],
    |
    | Or if you want to ignore a specific vendor package like `mathiasgrimm/*`,
    | you may use the path to the vendor's root or package folder like so:
    |
    |     'rules' => [
    |         'vendor/mathiasgrimm' => [],
    |     ],
    |
    */

    'rules' => [

        /**
         * We recommend uncommenting this line to improve performance in a
         * fresh Laravel install. If you are using any packages that use
         * environment variables, you may simply add the vendor/package folder
         * name before, after, or in place of `'laravel'`:
         *
         *     'vendor' => ['laravel', 'foodeveloper/barpackage'],
         *
         * Or if you want to scan all of foodeveloper's packages:
         *
         *     'vendor' => ['laravel', 'foodeveloper'],
         *
         */
        // 'vendor' => ['laravel'],

    ],

];
