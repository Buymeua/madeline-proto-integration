<?php

declare(strict_types=1);

namespace Buyme\MadelineProtoIntegration\Facades;

use Buyme\MadelineProtoIntegration\Services\V1\Telegram\Message\TelegramMessageService;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin TelegramMessageService
 */
class MPIMessage extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mpi-message';
    }
}
