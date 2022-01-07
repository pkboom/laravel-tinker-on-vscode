<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Tests\Models\TestModel;

/**
 * @see \Pkboom\TinkerOnVscode\ExecuteCodeCommand
 */
class ExecuteCodeCommandTest extends TestCase
{
    /** @test */
    public function it_outputs_the_result()
    {
        file_put_contents(Config::get('tinker-on-vscode.input'), "<?php \$foo = 'foo';");

        $this->artisan('execute:code')->assertExitCode(0);

        $output = file_get_contents(Config::get('tinker-on-vscode.output'));

        $this->assertSame('"foo"', $output);
    }

    /** @test */
    public function it_outputs_db_query()
    {
        $this->setUpDatabase($this->app);

        TestModel::create([
            'name' => 'asdf',
        ]);

        file_put_contents(Config::get('tinker-on-vscode.input'), "<?php \Tests\Models\TestModel::all();");

        $this->artisan('execute:code --query')->assertExitCode(0);

        $output = file_get_contents(Config::get('tinker-on-vscode.output'));

        $this->assertStringContainsString('asdf', $output);
        $this->assertStringContainsString('select * from test_models', $output);
    }

    protected function setUpDatabase($app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
    }
}
