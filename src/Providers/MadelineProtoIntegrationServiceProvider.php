<?php

declare(strict_types=1);

namespace Buyme\MadelineProtoIntegration\Providers;

use Buyme\MadelineProtoIntegration\Services\V1\Telegram\Auth\TelegramAuthService;
use Buyme\MadelineProtoIntegration\Services\V1\Telegram\User\TelegramUserService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class MadelineProtoIntegrationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/madeline-proto-integration.php' => config_path('madeline-proto-integration.php'),
        ], 'config');

    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/madeline-proto-integration.php', 'madeline-proto-integration');

        $this->registerFacades();
    }

    private function registerFacades(): void
    {
        $this->app->singleton('mpi-auth', function (Application $app) {
            return $app->make(TelegramAuthService::class);
        });

        $this->app->singleton('mpi-user', function (Application $app) {
            return $app->make(TelegramUserService::class);
        });
    }
}
