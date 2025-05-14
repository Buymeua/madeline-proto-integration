<?php

declare(strict_types=1);

namespace Buyme\MadelineProtoIntegration\Enum\Telegram;

enum ParseMode: string
{
    case HTML = 'HTML';
    case MARKDOWN = 'Markdown';
    case TEXT = 'text';
}
