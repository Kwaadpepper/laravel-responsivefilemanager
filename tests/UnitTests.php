<?php

namespace Tests\Feature;

use Orchestra\Testbench\TestCase;

class UnitTests extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Your code here
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $this->get('/filemanager/index.php')->assertStatus(200);
        $this->get('/filemanager/img/ico/ac3.jpg')->assertStatus(200);
    }

    protected function getPackageProviders($app)
    {
        return ['Kwaadpepper\ResponsiveFileManager\FileManagerServiceProvider'];
    }
}
