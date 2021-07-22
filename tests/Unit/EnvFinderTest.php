<?php

namespace Tests\Unit;

use MathiasGrimm\LaravelDotEnvGen\EnvDefinition;
use MathiasGrimm\LaravelDotEnvGen\EnvFinder;

class EnvFinderTest extends TestCase
{
    public function test_it_finds_only_one_env_with_scalar_name_and_no_default()
    {
        $envFinder = new EnvFinder();
        
        $code = <<<'CODE'
<?php
return [
    'something' => env('MY_VAR')
];
CODE;
        
        $envCalls = $envFinder->find($code);
        $this->assertCount(1, $envCalls);
        
        $this->assertEquals(new EnvDefinition('MY_VAR', null), $envCalls->first());
    }

    public function test_it_finds_only_one_env_with_scalar_name_and_default_null()
    {
        $envFinder = new EnvFinder();

        $code = <<<'CODE'
<?php
return [
    'something' => env('MY_VAR', null)
];
CODE;

        $envCalls = $envFinder->find($code);
        $this->assertCount(1, $envCalls);

        $this->assertEquals(new EnvDefinition('MY_VAR', 'null'), $envCalls->first());
    }

    public function test_it_finds_only_one_env_with_null_name_and_default_null()
    {
        $envFinder = new EnvFinder();

        $code = <<<'CODE'
<?php
return [
    'something' => env(null, 'null')
];
CODE;

        $envCalls = $envFinder->find($code);
        $this->assertCount(1, $envCalls);

        $this->assertEquals(new EnvDefinition('null', 'null'), $envCalls->first());
    }

    public function test_it_finds_only_one_env_with_scalar_name_and_scalar_default()
    {
        $envFinder = new EnvFinder();

        $code = <<<'CODE'
<?php
return [
    'something' => env('MY_VAR', 10)
];
CODE;

        $envCalls = $envFinder->find($code);
        $this->assertCount(1, $envCalls);

        $this->assertEquals(new EnvDefinition('MY_VAR', 10), $envCalls->first());
    }

    public function test_it_finds_only_one_env_with_non_scalar_name_and_not_default()
    {
        $envFinder = new EnvFinder();

        $code = <<<'CODE'
<?php
return [
    'something' => env(get_var())
];
CODE;

        $envCalls = $envFinder->find($code);
        $this->assertCount(1, $envCalls);

        $this->assertEquals(new EnvDefinition(EnvDefinition::VALUE_NOT_SCALAR, null), $envCalls->first());
    }

    public function test_it_finds_only_one_env_with_non_scalar_name_and_non_scalar_default()
    {
        $envFinder = new EnvFinder();

        $code = <<<'CODE'
<?php
return [
    'something' => env(get_var(), get_default())
];
CODE;

        $envCalls = $envFinder->find($code);
        $this->assertCount(1, $envCalls);

        $this->assertEquals(new EnvDefinition(EnvDefinition::VALUE_NOT_SCALAR, EnvDefinition::VALUE_NOT_SCALAR), $envCalls->first());
    }

    public function test_find_throws_exception_on_parse_error()
    {
        $envFinder = new EnvFinder();

        $code = <<<'CODE'
<?php
return [
    'something' => ENV('MY_VAR'(,
];
CODE;

        $this->expectException(\PhpParser\Error::class);
        
        $envCalls = $envFinder->find($code);
    }
    
    public function test_it_finds_multiple_variables()
    {
        $envFinder = new EnvFinder();

        $code = <<<'CODE'
<?php
return [
    'conf_1' => env('MY_VAR_1'),
    'conf_2' => env('MY_VAR_2'),
    'conf_3' => env('MY_VAR_3'),
    'conf_4' => env('MY_VAR_3'),
    'conf_5' => env('MY_VAR_3', 'default 1'),
    'conf_6' => env('MY_VAR_4', get_default()),
    'conf_7' => env(get_name(), get_default()),
    'conf_8' => enve('FAKE'),
];
CODE;

        $envCalls = $envFinder->find($code);
        $this->assertCount(7, $envCalls);

        $this->assertEquals(new EnvDefinition('MY_VAR_1', null), $envCalls[0]);
        $this->assertEquals(new EnvDefinition('MY_VAR_2', null), $envCalls[1]);
        $this->assertEquals(new EnvDefinition('MY_VAR_3', null), $envCalls[2]);
        $this->assertEquals(new EnvDefinition('MY_VAR_3', null), $envCalls[3]);
        $this->assertEquals(new EnvDefinition('MY_VAR_3', 'default 1'), $envCalls[4]);
        $this->assertEquals(new EnvDefinition('MY_VAR_4', EnvDefinition::VALUE_NOT_SCALAR), $envCalls[5]);
        $this->assertEquals(new EnvDefinition(EnvDefinition::VALUE_NOT_SCALAR, EnvDefinition::VALUE_NOT_SCALAR), $envCalls[6]);
    }
}