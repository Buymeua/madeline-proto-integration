<?php

declare(strict_types=1);

namespace Buyme\MadelineProtoIntegration\Enum\Telegram\Endpoints\V1\Contact;

enum TelegramContactEndpointsEnum: string
{
    case IMPORT_CONTACTS = 'v1/telegram/contacts/import-contacts';
}
