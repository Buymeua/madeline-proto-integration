<?php

declare(strict_types=1);

namespace Buyme\MadelineProtoIntegration\Enum\Telegram;

enum MessageCodesEnum: string
{
    case SUCCESS = 'success';

    case NOT_LOGGED_IN = 'not.logged.in';

    case NO_AUTH_SESSION = 'no.auth.session';

    case CONFIRMATION_CODE_REQUIRED = 'confirmation.code.required';

    case CONFIRMATION_2FA_REQUIRED = 'confirmation.2fa.required';

    case ACCOUNT_BANNED = 'account.banned';
}
