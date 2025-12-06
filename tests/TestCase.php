<?php

namespace Imamsudarajat04\ChangeLogs\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Imamsudarajat04\ChangeLogs\ChangeLogsServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Imamsudarajat04\ChangeLogs\tests\Models\User;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Get package providers.
     *
     * @param $app
     * @return \class-string[]
     */
    protected function getPackageProviders($app): array
    {
        return [
            ChangeLogsServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        # Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        # Setup change-logs config
        $app['config']->set('change-logs.enabled', true);
//        $app['config']->set('change-logs.cleanup.enabled', true);
        $app['config']->set('change-logs.user_model', User::class);
    }

    /**
     * Define database migration.
     *
     * @return void
     */
    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../src/database/migrations');

        # Load test migrations (users table)
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }
}