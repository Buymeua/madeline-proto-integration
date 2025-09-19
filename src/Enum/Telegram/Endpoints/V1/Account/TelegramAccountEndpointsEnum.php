<?php

declare(strict_types=1);

namespace Buyme\MadelineProtoIntegration\Enum\Telegram\Endpoints\V1\Account;

enum TelegramAccountEndpointsEnum: string
{
    case TELEGRAM_ACCOUNT = 'v1/telegram/accounts';
	case TELEGRAM_ACCOUNT_SUBMIT_CODE = 'v1/telegram/accounts/%s/submit-code';

	public function endpointSubmitCode(string|int $accountId = null): string
	{
		return match ($this) {
			self::TELEGRAM_ACCOUNT => $this->value,
			self::TELEGRAM_ACCOUNT_SUBMIT_CODE => sprintf($this->value, $accountId),
		};
	}
}
