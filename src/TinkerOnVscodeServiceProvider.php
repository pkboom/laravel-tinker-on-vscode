<?php

namespace Pkboom\TinkerOnVscode;

use Illuminate\Support\ServiceProvider;

class TinkerOnVscodeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ExecuteCodeCommand::class,
                TinkerOnVscodeCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../config/tinker-on-vscode.php' => config_path('tinker-on-vscode.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/tinker-on-vscode.php', 'tinker-on-vscode');
    }
}
