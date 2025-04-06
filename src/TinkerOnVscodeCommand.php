<?php

namespace Pkboom\TinkerOnVscode;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Pkboom\FileWatcher\FileWatcher;
use React\EventLoop\Loop;
use Symfony\Component\Finder\Finder;

class TinkerOnVscodeCommand extends Command
{
    protected $signature = 'tinker-on-vscode';

    public function handle()
    {
        $this->prepareFiles();

        $this->info("Write code in 'input.php' and save and see results at 'pick-server.test'");

        $this->info("You can visit https://github.com/pkboom/pick-server to set up 'pick-server.test'.");      

        $this->startWatching();
    }

    public function prepareFiles()
    {
        file_put_contents(Config::get('tinker-on-vscode.input'), "<?php\n\n");

        exec('code '.Config::get('tinker-on-vscode.input'));
    }

    public function startWatching()
    {
        $finder = (new Finder())
            ->name(Str::afterLast(Config::get('tinker-on-vscode.input'), '/'))
            ->files()
            ->in(Str::beforeLast(Config::get('tinker-on-vscode.input'), '/'));

        $watcher = FileWatcher::create($finder);

        Loop::addPeriodicTimer(0.5, function () use ($watcher) {
            $watcher->find()->whenChanged(function () {
                $code = file_get_contents(Config::get('tinker-on-vscode.input'));

                $viaTerminal = array_filter(['dump(', 'echo ', 'dv('], function ($expression) use ($code) {
                    return strpos($code, $expression) !== false;
                });

                if (count($viaTerminal)) {
                    $this->call(ExecuteCodeCommand::class, [
                        '--use-dump' => true,
                    ]);
                }

                exec('php artisan execute:code');
            });
        });
    }
}
