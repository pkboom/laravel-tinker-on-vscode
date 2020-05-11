<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Pkboom\TinkerOnVscode\TinkerOnVscodeServiceProvider;

abstract class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            TinkerOnVscodeServiceProvider::class,
        ];
    }
}
