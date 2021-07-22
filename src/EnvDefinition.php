<?php

namespace MathiasGrimm\LaravelDotEnvGen;


class EnvDefinition
{
    const VALUE_NOT_SCALAR = '*** NOT SCALAR ***';
    
    public $name;
    public $default;
    public $file;
    public $value;
    
    public function __construct($name, $default, string $file = null, $value = null)
    {
        $this->name = $name;
        $this->default = $default;
        $this->file = $file;
        $this->value = $value;
    }
}