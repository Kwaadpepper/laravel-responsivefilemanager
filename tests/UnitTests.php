<?php

namespace Tests\Feature;

use Illuminate\Contracts\Foundation\Application;
use Orchestra\Testbench\TestCase;
use Kwaadpepper\ResponsiveFileManager\FileManagerServiceProvider;

class UnitTests extends TestCase
{
    /**
     * @var ServiceProvider
     */
    protected $service_provider;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // create dummy app
        $this->createApplication();
        $this->service_provider = new FileManagerServiceProvider($this->app);
    }

    /**
     * @test
     */
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf(FileManagerServiceProvider::class, $this->service_provider);
    }

    /**
     * @test
     */
    public function it_does_nothing_in_the_register_method()
    {
        $this->assertNull($this->service_provider->register());
    }

    /**
     * @test
     */
    public function it_performs_a_boot_method()
    {
        $this->application_mock->shouldReceive('publishes')
                               ->once()
                               ->with([
                                   'resources/filemanager/ajax_calls.php',
                               ])
                               ->andReturnNull();

        $this->application_mock->shouldReceive('mergeConfigFrom')
                               ->once()
                               ->withArgs([
                                   '/config/rfm.php',
                                   'greatthing',
                               ])
                               ->andReturnNull();

        $this->service_provider->boot();
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

    /**
     * Load package service provider
     * @param  \Illuminate\Foundation\Application $app
     * @return lasselehtinen\MyPackage\MyPackageServiceProvider
     */
    protected function getPackageProviders($app)
    {
        return [FileManagerServiceProvider::class];
    }

    /**
     * Load package alias
     * @param  \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'MyPackage' => MyPackageFacade::class,
        ];
    }
}
