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
		string        $admin_bot_id,
		string        $title,
		?string       $description = null,
		?string       $username_channel = null,
		?UploadedFile $photo = null,
	): array
	{

		$requestParams = [
			'admin_bot_id' => $admin_bot_id,
			'title' => $title,
			'description' => $description,
			'username_channel' => $username_channel
		];

		try {

			return $this->httpClientService->performMultipartRequest(
				method: HttpRequestMethodsEnum::METHOD_POST->value,
				uri: TelegramChannelEndpointsEnum::TELEGRAM_CHANNEL->value,
				data: $requestParams,
				files: [
					'file' => [
						'path' => $photo->getPathname(),
						'name' => $photo->getClientOriginalName(),
						'mime' => $photo->getMimeType(),
					],
				]
			);

		} catch (Throwable $th) {
			report($th);

			throw $th;
		}
	}

	public function update(
		string        $channel_id,
		?string       $title,
		?string       $description,
		?string       $username_channel,
		?UploadedFile $photo,
	): array
	{
		$requestParams = [
			'title' => $title,
			'description' => $description,
			'username_channel' => $username_channel,
		];

		try {

			return $this->httpClientService->performMultipartRequest(
				method: HttpRequestMethodsEnum::METHOD_POST->value,
				uri: TelegramChannelEndpointsEnum::TELEGRAM_CHANNEL->value . "/$channel_id",
				data: $requestParams,
				files: [
					'file' => [
						'path' => $photo->getPathname(),
						'name' => $photo->getClientOriginalName(),
						'mime' => $photo->getMimeType(),
					],
				]
			);

		} catch (Throwable $th) {
			report($th);

			throw $th;
		}
	}

	public function show(
		string $channel_id,
	): array
	{

		try {

			 $response = $this->httpClientService->performRequest(
				HttpRequestMethodsEnum::METHOD_GET->value,
				TelegramChannelEndpointsEnum::TELEGRAM_CHANNEL->value . "/$channel_id"
			);

			return $response;

		} catch (Throwable $th) {
			report($th);

			throw $th;
		}
	}
}
