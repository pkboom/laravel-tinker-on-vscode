<?php

namespace Tests;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as Orchestra;
use Pkboom\TinkerOnVscode\TinkerOnVscodeServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            TinkerOnVscodeServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $this->initializeDirectory($this->getTempDirectory());

        config()->set('tinker-on-vscode.input', $this->getTempDirectory('input.php'));
        config()->set('tinker-on-vscode.output', $this->getTempDirectory('output.json'));

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    public function initializeDirectory(string $directory): void
    {
        File::deleteDirectory($directory);

        File::makeDirectory($directory);
    }

    public function getTempDirectory(?string $file = null): string
    {
        return __DIR__.'/temp'.($file ? '/'.$file : '');
    }
}
