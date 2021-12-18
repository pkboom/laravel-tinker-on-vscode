<?php

namespace Pkboom\TinkerOnVscode;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Pkboom\FileWatcher\FileWatcher;
use React\EventLoop\Loop;

class TinkerOnVscodeCommand extends Command
{
    protected $signature = 'tinker-on-vscode {--query}';

    public function handle()
    {
        $this->prepareFiles();

        $this->info('Write code in `input.php` and save to see results in `output.json`.');

        $this->info('Run `File: Open Active File in New Window` to detach input and output files. (Ctrl+K O)');

        $this->startWatching();
    }

    public function prepareFiles()
    {
        file_put_contents(Config::get('tinker-on-vscode.input'), "<?php\n\n");

        file_put_contents(Config::get('tinker-on-vscode.output'), null);

        exec('code '.Config::get('tinker-on-vscode.input'));

        exec('code  '.Config::get('tinker-on-vscode.output'));
    }

    public function startWatching()
    {
        $watcher = FileWatcher::create(Config::get('tinker-on-vscode.input'));

        Loop::addPeriodicTimer(1, function () use ($watcher) {
            $watcher->find()->whenChanged(function () {
                $command = 'php artisan process:code';

                if ($this->option('query')) {
                    $command .= ' --query';
                }

                exec($command);
            });
        });
    }
}
