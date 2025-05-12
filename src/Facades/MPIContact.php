<?php

declare(strict_types=1);

namespace Buyme\MadelineProtoIntegration\Facades;

use Buyme\MadelineProtoIntegration\Services\V1\Telegram\Contact\TelegramContactService;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin TelegramContactService
 */
class MPIContact extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mpi-contact';
    }
}

