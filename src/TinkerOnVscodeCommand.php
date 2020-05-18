<?php

namespace Pkboom\TinkerOnVscode;

use Psy\Shell;
use Throwable;
use Psy\Configuration;
use React\EventLoop\Factory;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Application;
use Illuminate\Database\Eloquent\Model;

class TinkerOnVscodeCommand extends Command
{
    protected $signature = 'tinker-on-vscode {--query}';

    protected $inputFile;

    protected $outputFile;

    public function handle()
    {
        if ($this->option('query')) {
            DB::enableQueryLog();
        }

        $this->prepareFiles();

        $this->info('Write code in `input.php` and save.');

        $lastModifiedTimestamp = filemtime($this->inputFile);

        $loop = Factory::create();

        $loop->addPeriodicTimer(0.5, function () use (&$lastModifiedTimestamp) {
            clearstatcache();

            if ($lastModifiedTimestamp !== filemtime($this->inputFile)) {
                $lastModifiedTimestamp = filemtime($this->inputFile);

                $this->executeCode(file_get_contents($this->inputFile));
            }
        });

        $loop->run();
    }

    public function prepareFiles()
    {
        $this->inputFile = storage_path('app/input.php');
        file_put_contents($this->inputFile, "<?php\n\n");

        $this->outputFile = storage_path('app/output.json');
        file_put_contents($this->outputFile, null);

        exec('code '.$this->inputFile);
        exec('code '.$this->outputFile);
    }

    public function executeCode(string $code)
    {
        $code = $this->removeComments($code);

        try {
            $result = $this->createShell()->execute($code, true);

            $result = json_encode($result, JSON_PRETTY_PRINT);
        } catch (Throwable $exception) {
            $result = $exception;
        }

        file_put_contents($this->outputFile, $result);

        if ($this->option('query')) {
            $result = "\n\n".json_encode(DB::getQueryLog(), JSON_PRETTY_PRINT);

            file_put_contents($this->outputFile, $result, FILE_APPEND);

            DB::flushQueryLog();
        }

        return;
    }

    /**
     * @link: https://github.com/spatie/laravel-web-tinker/blob/master/src/Tinker.php
     */
    protected function createShell()
    {
        $config = new Configuration([
            'updateCheck' => 'never',
        ]);

        $config->setHistoryFile(defined('PHP_WINDOWS_VERSION_BUILD') ? 'null' : '/dev/null');

        $config->getPresenter()->addCasters([
            Collection::class => 'Laravel\Tinker\TinkerCaster::castCollection',
            Model::class => 'Laravel\Tinker\TinkerCaster::castModel',
            Application::class => 'Laravel\Tinker\TinkerCaster::castApplication',
        ]);

        return  new Shell($config);
    }

    /**
     * @link: https://github.com/spatie/laravel-web-tinker/blob/master/src/Tinker.php
     */
    public function removeComments(string $code)
    {
        $tokens = collect(token_get_all($code));

        return $tokens->reduce(function ($carry, $token) {
            if (is_string($token)) {
                return $carry.$token;
            }

            $text = $this->ignoreCommentsAndPhpTags($token);

            return $carry.$text;
        }, '');
    }

    /**
     * @link: https://github.com/spatie/laravel-web-tinker/blob/master/src/Tinker.php
     */
    protected function ignoreCommentsAndPhpTags(array $token)
    {
        [$id, $text] = $token;

        if ($id === T_COMMENT) {
            return '';
        }
        if ($id === T_DOC_COMMENT) {
            return '';
        }
        if ($id === T_OPEN_TAG) {
            return '';
        }
        if ($id === T_CLOSE_TAG) {
            return '';
        }

        return $text;
    }
}
