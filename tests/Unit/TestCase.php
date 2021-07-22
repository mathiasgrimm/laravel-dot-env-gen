<?php

namespace Tests\Unit;


use MathiasGrimm\LaravelDotEnvGen\DotEnvGenServiceProvider;

class TestCase extends \Tests\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        
        $app = app();
        
        (new DotEnvGenServiceProvider($app))->register();
    }
}