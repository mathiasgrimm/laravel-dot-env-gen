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
    | To ignore only `vendor/mathiasgrimm`, for example, you may use the
    | following:
    |
    |     'rules' => [
    |         'vendor/mathiasgrimm' => [],
    |     ],
    |
    */

    'rules' => [

        'vendor' => ['laravel'],

    ],

];
