<?php

namespace Tests;

use Illuminate\Support\Facades\File;
use React\EventLoop\Loop;

/**
 * @see \Pkboom\TinkerOnVscode\TinkerOnVscodeCommand
 */
class TinkerOnVscodeCommandTest extends TestCase
{
    public function tearDown(): void
    {
        parent::tearDown();

        Loop::stop();
    }

    /** @test */
    public function it_creates_input_and_output_files()
    {
        File::delete($this->getTempDirectory('input.php'));
        File::delete($this->getTempDirectory('output.json'));

        $this->artisan('tinker-on-vscode')->assertExitCode(0);

        $this->assertFileExists($this->getTempDirectory('input.php'));
        $this->assertFileExists($this->getTempDirectory('output.json'));
    }
}
