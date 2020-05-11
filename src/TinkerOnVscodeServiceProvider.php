<?php

namespace Pkboom\TinkerOnVscode;

use Illuminate\Support\ServiceProvider;

class TinkerOnVscodeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                TinkerOnVscodeCommand::class,
            ]);
        }
    }
}
