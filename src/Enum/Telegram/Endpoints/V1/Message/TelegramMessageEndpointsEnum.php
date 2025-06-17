<?php

declare(strict_types=1);

namespace Buyme\MadelineProtoIntegration\Enum\Telegram\Endpoints\V1\Message;

enum TelegramMessageEndpointsEnum: string
{
    case SEND_SIMPLE_MESSAGE = 'v1/telegram/messages/send-simple-message';
    case SEND_MEDIA_MESSAGE = 'v1/telegram/messages/send-media-message';
    case SEND_UPLOADED_FILE_MESSAGE = 'v1/telegram/messages/send-upload-file-message';
}
