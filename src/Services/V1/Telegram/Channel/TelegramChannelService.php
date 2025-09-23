<?php

namespace Buyme\MadelineProtoIntegration\Services\V1\Telegram\Channel;

use Buyme\MadelineProtoIntegration\Enum\Http\HttpRequestMethodsEnum;
use Buyme\MadelineProtoIntegration\Enum\Telegram\Endpoints\V1\Channel\TelegramChannelEndpointsEnum;
use Buyme\MadelineProtoIntegration\Enum\Telegram\Endpoints\V1\Message\TelegramMessageEndpointsEnum;
use Buyme\MadelineProtoIntegration\Services\V1\Http\MadelineHttpClientService;
use Illuminate\Http\UploadedFile;
use Throwable;

readonly class TelegramChannelService
{
	public function __construct(private MadelineHttpClientService $httpClientService)
	{
	}

	public function store(
		string        $adminBotId,
		string        $title,
		?string       $description = null,
		?string       $usernameChannel = null,
		?UploadedFile $photo = null,
	): array
	{

		$requestParams = [
			'admin_bot_id' => $adminBotId,
			'title' => $title,
			'description' => $description,
			'username_channel' => $usernameChannel
		];

		try {

			$files = [];

			if ($photo instanceof UploadedFile) {
				$files['file'] = [
					'path' => $photo->getPathname(),
					'name' => $photo->getClientOriginalName(),
					'mime' => $photo->getMimeType(),
				];

				return $this->httpClientService->performMultipartRequestMultipleAccounts(
					method: HttpRequestMethodsEnum::METHOD_POST->value,
					uri: TelegramChannelEndpointsEnum::TELEGRAM_CHANNEL->value,
					data: $requestParams,
					files: $files
				);
			}

			return $this->httpClientService->performRequestMultipleAccounts(
				method: HttpRequestMethodsEnum::METHOD_POST->value,
				uri: TelegramChannelEndpointsEnum::TELEGRAM_CHANNEL->value,
				params: $requestParams
			);

		} catch (Throwable $th) {
			report($th);

			throw $th;
		}
	}

	public function update(
		string        $channelId,
		?string       $title,
		?string       $description,
		?string       $usernameChannel,
		?UploadedFile $photo,
	): array
	{
		$requestParams = [
			'title' => $title ?? null,
			'description' => $description ?? null,
			'username_channel' => $usernameChannel ?? null,
		];

		try {
			$files = [];

			if ($photo instanceof UploadedFile) {
				$files['file'] = [
					'path' => $photo->getPathname(),
					'name' => $photo->getClientOriginalName(),
					'mime' => $photo->getMimeType(),
				];
			}

			return $this->httpClientService->performMultipartRequest(
				method: HttpRequestMethodsEnum::METHOD_POST->value,
				uri: TelegramChannelEndpointsEnum::TELEGRAM_CHANNEL->value . "/$channelId",
				data: $requestParams,
				files: $files
			);

		} catch (Throwable $th) {
			report($th);

			throw $th;
		}
	}

	public function show(
		string $channelId,
	): array
	{
		try {

			return $this->httpClientService->performRequest(
				HttpRequestMethodsEnum::METHOD_GET->value,
				TelegramChannelEndpointsEnum::TELEGRAM_CHANNEL->value . "/$channelId"
			);

		} catch (Throwable $th) {
			report($th);

			throw $th;
		}
	}
}
