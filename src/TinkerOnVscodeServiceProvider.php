<?php

namespace Pkboom\TinkerOnVscode;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Symfony\Component\VarDumper\Server\Connection;
use Symfony\Component\VarDumper\VarDumper;

class TinkerOnVscodeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ExecuteCodeCommand::class,
                TinkerOnVscodeCommand::class,
                DumpServerCommand::class,
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/tinker-on-vscode.php', 'tinker-on-vscode');

        $host = $this->app['config']->get('tinker-on-vscode.host');

        $connection = new Connection($host, [
            'request' => new RequestContextProvider($this->app['request']),
            'source' => new SourceContextProvider('utf-8', base_path()),
        ]);

        VarDumper::setHandler(function ($var) use ($connection) {
            $this->app->make(Dumper::class, ['connection' => $connection])->dump($var);
        });
    }
}
