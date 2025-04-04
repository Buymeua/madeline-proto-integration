<?php

declare(strict_types=1);

namespace Buyme\MadelineProtoIntegration\Facades;

use Buyme\MadelineProtoIntegration\Services\V1\Telegram\Auth\TelegramAuthService;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin TelegramAuthService
 */
class MPIAuth extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mpi-auth';
    }
}
