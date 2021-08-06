<?php

namespace Pkboom\TinkerOnVscode;

use Illuminate\Support\ServiceProvider;

class TinkerOnVscodeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/tinker-on-vscode.php', 'tinker-on-vscode');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ExecuteCodeCommand::class,
                TinkerOnVscodeCommand::class,
            ]);
        }
    }
}
