<?php

declare(strict_types=1);

namespace Buyme\MadelineProtoIntegration\Services\V1\Telegram\Message;

use Buyme\MadelineProtoIntegration\Enum\Http\HttpRequestMethodsEnum;
use Buyme\MadelineProtoIntegration\Enum\Telegram\Endpoints\V1\Message\TelegramMessageEndpointsEnum;
use Buyme\MadelineProtoIntegration\Services\V1\Http\MadelineHttpClientService;
use Throwable;

readonly class TelegramMessageService
{
    public function __construct(private MadelineHttpClientService $httpClientService)
    {
    }

    /**
     * @throws Throwable
     */
    public function sendSimpleMessage(array|int|string $peer, string $message): bool
    {
        $requestParams = [
            'peer' => $peer,
            'message' => $message,
        ];

        try {
            $response = $this->httpClientService->performRequest(
                HttpRequestMethodsEnum::METHOD_POST->value,
                TelegramMessageEndpointsEnum::SEND_SIMPLE_MESSAGE->value,
                $requestParams
            );

            return boolval($response['data']['sent'] ?? false);
        } catch (Throwable $th) {
            report($th);

            throw $th;
        }
    }

    /**
     * @throws Throwable
     */
    public function sendMediaMessage(array|int|string $peer, string $message, string $fileUrl): bool
    {
        $requestParams = [
            'peer' => $peer,
            'message' => $message,
            'fileUrl' => $fileUrl
        ];

        try {
            $response = $this->httpClientService->performRequest(
                HttpRequestMethodsEnum::METHOD_POST->value,
                TelegramMessageEndpointsEnum::SEND_MEDIA_MESSAGE->value,
                $requestParams
            );

            return boolval($response['data']['sent'] ?? false);
        } catch (Throwable $th) {
            report($th);

            throw $th;
        }
    }
}
