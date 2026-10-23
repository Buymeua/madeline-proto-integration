<?php

declare(strict_types=1);

namespace Buyme\MadelineProtoIntegration\Tests;

use Buyme\MadelineProtoIntegration\Providers\MadelineProtoIntegrationServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [MadelineProtoIntegrationServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(str_repeat('k', 32)));
        $app['config']->set('madeline-proto-integration.url', 'https://mp.test/api/');
    }
}
