<?php

namespace Pkboom\TinkerOnVscode;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Command\Descriptor\DumpDescriptorInterface;
use Symfony\Component\VarDumper\Dumper\CliDumper;

/**
 * @see \Symfony\Component\VarDumper\Command\Descriptor\CliDescriptor
 */
class CliDescriptor implements DumpDescriptorInterface
{
    private $dumper;
    private $lastIdentifier;

    public function __construct(CliDumper $dumper)
    {
        $this->dumper = $dumper;
    }

    public function describe(OutputInterface $output, Data $data, array $context, int $clientId): void
    {
        $io = $output instanceof SymfonyStyle ? $output : new SymfonyStyle(new ArrayInput([]), $output);
        $this->dumper->setColors($output->isDecorated());

        $lastIdentifier = $this->lastIdentifier;
        $this->lastIdentifier = $clientId;

        $section = "Received from client #$clientId";
        if (isset($context['request'])) {
            $request = $context['request'];
            $this->lastIdentifier = $request['identifier'];
            $section = sprintf('%s %s', $request['method'], $request['uri']);
            if ($controller = $request['controller']) {
                $rows[] = ['controller', rtrim($this->dumper->dump($controller, true), "\n")];
            }
        } elseif (isset($context['cli'])) {
            $this->lastIdentifier = $context['cli']['identifier'];
            $section = '$ '.$context['cli']['command_line'];
        }

        if ($this->lastIdentifier !== $lastIdentifier) {
            $io->section($section);
        }

        if (isset($context['source'])) {
            $source = $context['source'];
            $file = sprintf('%s on line %d', $source['file_relative'] ?? $source['file'], $source['line']);
            $rows[] = ['source', $file];
        }

        $io->table([], $rows);

        $this->dumper->dump($data);
        $io->newLine();
    }
}
