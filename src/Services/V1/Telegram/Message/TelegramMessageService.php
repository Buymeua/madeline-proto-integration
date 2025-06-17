<?php

declare(strict_types=1);

namespace Buyme\MadelineProtoIntegration\Services\V1\Telegram\Message;

use Buyme\MadelineProtoIntegration\Enum\Http\HttpRequestMethodsEnum;
use Buyme\MadelineProtoIntegration\Enum\Telegram\Endpoints\V1\Message\TelegramMessageEndpointsEnum;
use Buyme\MadelineProtoIntegration\Enum\Telegram\ParseMode;
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
    public function sendSimpleMessage(
        array|int|string $peer,
        string $message,
        ParseMode $parseMode = ParseMode::TEXT,
    ): bool {
        $requestParams = [
            'peer' => $peer,
            'message' => $message,
            'parse_mode' => $parseMode->value,
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
    public function sendMediaMessage(
        array|int|string $peer,
        string $message,
        string $fileUrl,
        ParseMode $parseMode = ParseMode::TEXT,
    ): bool {
        $requestParams = [
            'peer' => $peer,
            'message' => $message,
            'file_url' => $fileUrl,
            'parse_mode' => $parseMode->value,
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

    /**
     * @throws Throwable
     */
    public function sendUploadedFileMessage(
        array|int|string $peer,
        \Illuminate\Http\UploadedFile $file,
        ParseMode $parseMode = ParseMode::TEXT,
        ?string $message = null,
    ): bool {
        try {
            $response = $this->httpClientService->performMultipartRequest(
                method: HttpRequestMethodsEnum::METHOD_POST->value,
                uri: TelegramMessageEndpointsEnum::SEND_UPLOADED_FILE_MESSAGE->value,
                data: [
                    'peer' => $peer,
                    'message' => $message,
                    'parse_mode' => $parseMode->value,
                ],
                files: [
                    'file' => [
                        'path' => $file->getPathname(),
                        'name' => $file->getClientOriginalName(),
                        'mime' => $file->getMimeType(),
                    ],
                ]
            );

            return boolval($response['data']['sent'] ?? false);
        } catch (Throwable $th) {
            report($th);

            throw $th;
        }
    }
}
