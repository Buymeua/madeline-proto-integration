<?php

declare(strict_types=1);

namespace Buyme\MadelineProtoIntegration\Services\V1\Telegram\Contact;

use Buyme\MadelineProtoIntegration\Enum\Http\HttpRequestMethodsEnum;
use Buyme\MadelineProtoIntegration\Enum\Telegram\Endpoints\V1\Contact\TelegramContactEndpointsEnum;
use Buyme\MadelineProtoIntegration\Services\V1\Http\MadelineHttpClientService;
use Throwable;

readonly class TelegramContactService
{
    public function __construct(private MadelineHttpClientService $httpClientService)
    {
    }

    /**
     * @throws Throwable
     */
    public function importContacts(array $peers): array
    {
        $requestParams = [
            'peers' => $peers,
        ];

        try {
            $response = $this->httpClientService->performRequest(
                HttpRequestMethodsEnum::METHOD_POST->value,
                TelegramContactEndpointsEnum::IMPORT_CONTACTS->value,
                $requestParams
            );

            return $response['data'] ?? [];
        } catch (Throwable $th) {
            report($th);

            throw $th;
        }
    }
}
