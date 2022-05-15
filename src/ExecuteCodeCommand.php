<?php

namespace Pkboom\TinkerOnVscode;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Laravel\Tinker\ClassAliasAutoloader;
use Psy\Configuration;
use Psy\Shell;
use Symfony\Component\Console\Output\BufferedOutput;
use Throwable;

class ExecuteCodeCommand extends Command
{
    protected $signature = 'execute:code {--query} {--use-dump}';

    public function handle()
    {
        if ($this->option('query')) {
            DB::enableQueryLog();
        }

        $code = file_get_contents(Config::get('tinker-on-vscode.input'));

        if (!$this->option('use-dump')) {
            $code = preg_replace(['/^\s*dump\(/m', '/^\s*echo\s/m', '/^\s*dv\(/m'], '//', $code);
        } else {
            $code = preg_replace(['/^\s*(dv\()([^)]+)(\))/m'], 'dump(\'${2}\', ${2}${3}', $code);
        }

        $code = $this->removeComments($code);

        $shell = $this->createShell();

        $path = $this->getLaravel()->environment('testing')
            ? getcwd().'/vendor'
            : Env::get('COMPOSER_VENDOR_DIR', $this->getLaravel()->basePath().DIRECTORY_SEPARATOR.'vendor');

        $path .= '/composer/autoload_classmap.php';

        $config = $this->getLaravel()->make('config');

        $loader = ClassAliasAutoloader::register(
            $shell,
            $path,
            $config->get('tinker.alias', []),
            $config->get('tinker.dont_alias', [])
        );

        $output = $this->option('use-dump') ? $this->output : new BufferedOutput();

        try {
            $shell->setOutput($output);

            $result = $shell->execute($code, true);

            $result = json_encode($result, JSON_PRETTY_PRINT);
        } catch (Throwable $exception) {
            $result = wordwrap($exception->getMessage(), 80);
        } finally {
            $loader->unregister();
        }

        if (!$this->option('use-dump')) {
            file_put_contents(Config::get('tinker-on-vscode.output'), $result);
        }

        if ($this->option('query')) {
            $result = "\n\n".json_encode(DB::getQueryLog(), JSON_PRETTY_PRINT);

            $result = str_replace('\"', '', $result);

            file_put_contents(Config::get('tinker-on-vscode.output'), $result, FILE_APPEND);

            DB::flushQueryLog();
            DB::disableQueryLog();
        }
    }

    /**
     * @see: https://github.com/spatie/laravel-web-tinker/blob/master/src/Tinker.php
     */
    protected function createShell()
    {
        $config = new Configuration([
            'updateCheck' => 'never',
        ]);

        $config->setHistoryFile(defined('PHP_WINDOWS_VERSION_BUILD') ? 'null' : '/dev/null');

        $config->addCasters([
            Collection::class => 'Laravel\Tinker\TinkerCaster::castCollection',
            Model::class => 'Laravel\Tinker\TinkerCaster::castModel',
            Application::class => 'Laravel\Tinker\TinkerCaster::castApplication',
        ]);

        return new Shell($config);
    }

    /**
     * @see: https://github.com/spatie/laravel-web-tinker/blob/master/src/Tinker.php
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
     * @see: https://github.com/spatie/laravel-web-tinker/blob/master/src/Tinker.php
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
