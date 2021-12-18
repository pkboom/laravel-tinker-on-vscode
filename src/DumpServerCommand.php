<?php

namespace Pkboom\TinkerOnVscode;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use React\EventLoop\Loop;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Throwable;

class DumpServerCommand extends Command
{
    protected $signature = 'dump-server';

    public function handle()
    {
        $server = stream_socket_server(Config::get('tinker-on-vscode.host'));

        stream_set_blocking($server, false);

        $this->info(sprintf('Server listening on %s', Config::get('tinker-on-vscode.host')));

        $descriptor = new CliDescriptor(new CliDumper());

        $io = new SymfonyStyle($this->input, $this->output);

        Loop::addReadStream($server, function ($server) use ($descriptor, $io) {
            $stream = stream_socket_accept($server);

            while (($message = fgets($stream)) !== false) {
                $payload = @unserialize(base64_decode($message));

                [$data, $context] = $payload;

                try {
                    $descriptor->describe($io, $data, $context, 1);
                } catch (Throwable $e) {
                }
            }
        });
    }
}
