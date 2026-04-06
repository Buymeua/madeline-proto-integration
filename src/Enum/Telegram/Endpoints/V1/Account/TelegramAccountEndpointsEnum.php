<?php

declare(strict_types=1);

namespace Buyme\MadelineProtoIntegration\Enum\Telegram\Endpoints\V1\Account;

enum TelegramAccountEndpointsEnum: string
{
    case TELEGRAM_ACCOUNT = 'v1/telegram/accounts';
	case TELEGRAM_ACCOUNT_SUBMIT_CODE = 'v1/telegram/accounts/%s/submit-code';
	case TELEGRAM_ACCOUNT_STATUS = 'v1/telegram/accounts/%s/status';
	case TELEGRAM_ACCOUNT_UNBAN = 'v1/telegram/accounts/%s/unban';

	public function endpointSubmitCode(string|int $accountId = null): string
	{
		return match ($this) {
			self::TELEGRAM_ACCOUNT => $this->value,
			self::TELEGRAM_ACCOUNT_SUBMIT_CODE => sprintf($this->value, $accountId),
			default => $this->value,
		};
	}

	public function withAccountId(string|int $accountId): string
	{
		return sprintf($this->value, $accountId);
	}
}
