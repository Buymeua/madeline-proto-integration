<?php

declare(strict_types=1);

namespace Buyme\MadelineProtoIntegration\Providers;

use Buyme\MadelineProtoIntegration\Services\V1\Telegram\Auth\TelegramAuthService;
use Buyme\MadelineProtoIntegration\Services\V1\Telegram\Contact\TelegramContactService;
use Buyme\MadelineProtoIntegration\Services\V1\Telegram\Message\TelegramMessageService;
use Buyme\MadelineProtoIntegration\Services\V1\Telegram\Channel\TelegramChannelService;
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

        $this->app->singleton('mpi-message', function (Application $app) {
            return $app->make(TelegramMessageService::class);
        });

        $this->app->singleton('mpi-contact', function (Application $app) {
            return $app->make(TelegramContactService::class);
        });

        $this->app->singleton('mpi-channel', function (Application $app) {
            return $app->make(TelegramChannelService::class);
        });
    }
}
