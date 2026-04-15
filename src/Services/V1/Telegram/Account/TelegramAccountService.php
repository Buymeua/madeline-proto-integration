<?php

namespace Buyme\MadelineProtoIntegration\Services\V1\Telegram\Account;

use Buyme\MadelineProtoIntegration\Enum\Http\HttpRequestMethodsEnum;
use Buyme\MadelineProtoIntegration\Enum\Telegram\Endpoints\V1\Account\TelegramAccountEndpointsEnum;
use Buyme\MadelineProtoIntegration\Services\V1\Http\MadelineHttpClientService;
use Throwable;

class TelegramAccountService
{
	public function __construct(private MadelineHttpClientService $httpClientService)
	{
	}

	public function accounts(): array|bool
	{
		try {

			return $this->httpClientService->performRequest(
				HttpRequestMethodsEnum::METHOD_GET->value,
				TelegramAccountEndpointsEnum::TELEGRAM_ACCOUNT->value
			);

		} catch (Throwable $th) {
			report($th);

			return false;
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

	public function submitCode(
		int $accountId,
		int $code,
	)
	{
		$requestParams = [
			"accountId" => $accountId,
			"code" => $code,
		];

		try {

			return $this->httpClientService->performRequest(
				HttpRequestMethodsEnum::METHOD_POST->value,
				TelegramAccountEndpointsEnum::TELEGRAM_ACCOUNT_SUBMIT_CODE->endpointSubmitCode($accountId),
				$requestParams
			);

		} catch (Throwable $th) {
			report($th);

			throw $th;
		}
	}

	public function status(int $accountId): array|bool
	{
		try {
			return $this->httpClientService->performRequest(
				HttpRequestMethodsEnum::METHOD_GET->value,
				TelegramAccountEndpointsEnum::TELEGRAM_ACCOUNT_STATUS->withAccountId($accountId)
			);
		} catch (Throwable $th) {
			report($th);

			return false;
		}
	}

	public function unban(int $accountId): array|bool
	{
		try {
			return $this->httpClientService->performRequest(
				HttpRequestMethodsEnum::METHOD_POST->value,
				TelegramAccountEndpointsEnum::TELEGRAM_ACCOUNT_UNBAN->withAccountId($accountId)
			);
		} catch (Throwable $th) {
			report($th);

			return false;
		}
	}

	public function reAuth(int $accountId): array|bool
	{
		try {
			return $this->httpClientService->performRequest(
				HttpRequestMethodsEnum::METHOD_POST->value,
				TelegramAccountEndpointsEnum::TELEGRAM_ACCOUNT_RE_AUTH->withAccountId($accountId)
			);
		} catch (Throwable $th) {
			report($th);

			return false;
		}
	}

	public function submit2FA(int $accountId, string $password): array
	{
		$requestParams = [
			'password' => $password,
		];

		try {
			return $this->httpClientService->performRequest(
				HttpRequestMethodsEnum::METHOD_POST->value,
				TelegramAccountEndpointsEnum::TELEGRAM_ACCOUNT_SUBMIT_2FA->withAccountId($accountId),
				$requestParams
			);
		} catch (Throwable $th) {
			report($th);

			throw $th;
		}
	}
}
