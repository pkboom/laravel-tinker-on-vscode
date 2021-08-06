<?php

namespace Pkboom\TinkerOnVscode;

use Illuminate\Console\Command;
use React\EventLoop\Loop;

class TinkerOnVscodeCommand extends Command
{
    protected $signature = 'tinker-on-vscode {--query}';

    public function handle()
    {
        $this->prepareFiles();

        $this->info('Write code in `input.php` and save.');

        $this->info(config('tinker-on-vscode.input'));

        $this->info(config('tinker-on-vscode.output'));

        $lastModifiedTimestamp = filemtime(config('tinker-on-vscode.input'));

        $loop = Loop::get();

        $loop->addPeriodicTimer(1, function () use (&$lastModifiedTimestamp) {
            clearstatcache();

            if ($lastModifiedTimestamp !== filemtime(config('tinker-on-vscode.input'))) {
                $lastModifiedTimestamp = filemtime(config('tinker-on-vscode.input'));

                $command = 'php artisan process:code';

                if ($this->option('query')) {
                    $command .= ' --query';
                }

                exec($command);
            }
        });

        $loop->run();
    }

    public function prepareFiles()
    {
        file_put_contents(config('tinker-on-vscode.input'), "<?php\n\n");

        file_put_contents(config('tinker-on-vscode.output'), null);

        exec('code '.config('tinker-on-vscode.input'));

        exec('code '.config('tinker-on-vscode.output'));
    }
}
