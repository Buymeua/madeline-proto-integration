<?php

namespace Buyme\MadelineProtoIntegration\Services\V1\Telegram\Account;

use Buyme\MadelineProtoIntegration\Enum\Http\HttpRequestMethodsEnum;
use Buyme\MadelineProtoIntegration\Enum\Telegram\Endpoints\V1\Account\TelegramAccountEndpointsEnum;
use Buyme\MadelineProtoIntegration\Services\V1\Http\MadelineHttpClientService;

class TelegramAccountService
{
	public function __construct(private MadelineHttpClientService $httpClientService)
	{
	}

	public function accounts(): array
	{
		try {

			return $this->httpClientService->performRequest(
				HttpRequestMethodsEnum::METHOD_GET->value,
				TelegramAccountEndpointsEnum::TELEGRAM_ACCOUNT->value
			);

		} catch (\Throwable $th) {
			report($th);

			throw $th;
		}
	}

	public function store(
		string $login,
		string $password,
		string $phone,
		string $api_id,
		string $api_hash,
	): array
	{

		$requestParams = [
			"login" => $login,
			"password" => $password,
			"phone" => $phone,
			"api_id" => $api_id,
			"api_hash" => $api_hash,
		];

		try {

			return $this->httpClientService->performRequest(
				HttpRequestMethodsEnum::METHOD_POST->value,
				TelegramAccountEndpointsEnum::TELEGRAM_ACCOUNT->value,
				$requestParams
			);

		} catch (Throwable $th) {
			report($th);

			throw $th;
		}
	}
}
