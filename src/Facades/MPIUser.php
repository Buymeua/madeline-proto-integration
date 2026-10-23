<?php

declare(strict_types=1);

namespace Buyme\MadelineProtoIntegration\Facades;

use Buyme\MadelineProtoIntegration\Services\V1\Telegram\User\TelegramUserService;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin TelegramUserService
 */
class MPIUser extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mpi-user';
    }
}
