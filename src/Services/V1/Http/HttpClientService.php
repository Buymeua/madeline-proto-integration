<?php

declare(strict_types=1);

namespace Buyme\MadelineProtoIntegration\Services\V1\Http;

use Buyme\MadelineProtoIntegration\Contracts\HttpClientServiceInterface;
use Buyme\MadelineProtoIntegration\Enum\Http\HttpRequestMethodsEnum;
use Buyme\MadelineProtoIntegration\Enum\Telegram\MessageCodesEnum;
use Buyme\MadelineProtoIntegration\Exceptions\Auth\MadelineNoAuthSessionException;
use Buyme\MadelineProtoIntegration\Exceptions\Auth\MadelineNotLoggedInException;
use Buyme\MadelineProtoIntegration\Exceptions\MadelineRequestException;
use Buyme\MadelineProtoIntegration\Models\MpiAccountUser;
use Buyme\MadelineProtoIntegration\Services\V1\Auth\AuthTokenService;
use Buyme\MadelineProtoIntegration\Traits\GuzzleHttp\GuzzleHttpResponseTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

readonly class HttpClientService implements HttpClientServiceInterface
{
    use GuzzleHttpResponseTrait;

    /** Методы, у которых параметры уходят JSON-телом (остальные — query string) */
    private const BODY_METHODS = [
        HttpRequestMethodsEnum::METHOD_POST,
        HttpRequestMethodsEnum::METHOD_PUT,
        HttpRequestMethodsEnum::METHOD_PATCH,
    ];

    private const MULTIPART_TIMEOUT_SECONDS = 180;

    public function __construct(private AuthTokenService $authTokenService)
    {
    }

    /**
     * Запрос от имени основного аккаунта (токен из AuthTokenService).
     *
     * @throws MadelineRequestException
     */
    public function performRequest(string $method, string $uri, array $params = [], array $headers = []): array
    {
        return $this->send($method, $uri, $this->jsonOptions($method, $params, $this->withToken($headers, $this->authTokenService->getAuthToken())));
    }

    /**
     * @throws MadelineRequestException
     */
    public function performMultipartRequest(string $method, string $uri, array $data = [], array $files = [], array $headers = []): array
    {
        return $this->send($method, $uri, $this->multipartOptions($data, $files, $this->withToken($headers, $this->authTokenService->getAuthToken(), json: false)));
    }

    /**
     * Перебирает аккаунты (не забаненные первыми), пока один не ответит успешно.
     *
     * @throws MadelineRequestException
     */
    public function performRequestMultipleAccounts(string $method, string $uri, array $params = [], array $headers = []): array
    {
        return $this->sendWithFailover(
            $method,
            $uri,
            fn (MpiAccountUser $account) => $this->jsonOptions($method, $params, $this->withToken($headers, $account->token)),
        );
    }

    /**
     * @throws MadelineRequestException
     */
    public function performMultipartRequestMultipleAccounts(string $method, string $uri, array $data = [], array $files = [], array $headers = []): array
    {
        return $this->sendWithFailover(
            $method,
            $uri,
            fn (MpiAccountUser $account) => $this->multipartOptions($data, $files, $this->withToken($headers, $account->token, json: false)),
        );
    }

    /**
     * @param callable(MpiAccountUser): array{0: string, 1: array} $buildOptions
     *
     * @throws MadelineRequestException
     */
    private function sendWithFailover(string $method, string $uri, callable $buildOptions): array
    {
        $errors = [];

        foreach (MpiAccountUser::query()->orderBy('is_banned')->get() as $account) {
            try {
                return $this->send($method, $uri, $buildOptions($account));
            } catch (MadelineRequestException $exception) {
                if ($this->isBanned($exception)) {
                    $account->markAsBanned();
                }

                $errors[] = [
                    'user_id' => $account->id,
                    'message_code' => $exception->getErrorCode(),
                    'error' => $exception->getMessage(),
                ];
            }
        }

        throw new MadelineRequestException('All accounts failed', 0, null, json_encode($errors, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @param array{0: string, 1: array} $prepared [uri suffix, guzzle options]
     *
     * @throws MadelineRequestException
     */
    private function send(string $method, string $uri, array $prepared): array
    {
        [$query, $options] = $prepared;

        try {
            $response = $this->client()->request($method, trim($uri, ' \\') . $query, $options);

            return $this->getResponseContent($response);
        } catch (RequestException $exception) {
            throw $this->mapException($exception);
        } catch (GuzzleException $exception) {
            throw new MadelineRequestException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }
    }

    /**
     * @return array{0: string, 1: array}
     */
    private function jsonOptions(string $method, array $params, array $headers): array
    {
        $options = ['headers' => $headers];

        if (in_array(HttpRequestMethodsEnum::tryFrom(strtoupper($method)), self::BODY_METHODS, true)) {
            return ['', [...$options, 'body' => json_encode($params, JSON_UNESCAPED_UNICODE)]];
        }

        return [$params === [] ? '' : '?' . http_build_query($params), $options];
    }

    /**
     * @return array{0: string, 1: array}
     */
    private function multipartOptions(array $data, array $files, array $headers): array
    {
        $multipart = [];

        foreach ($data as $name => $value) {
            $multipart[] = ['name' => $name, 'contents' => (string) $value];
        }

        foreach ($files as $name => $file) {
            $multipart[] = [
                'name' => $name,
                'contents' => fopen($file['path'], 'r'),
                'filename' => $file['name'] ?? basename($file['path']),
                'headers' => ['Content-Type' => $file['mime'] ?? 'application/octet-stream'],
            ];
        }

        return ['', ['headers' => $headers, 'multipart' => $multipart, 'timeout' => self::MULTIPART_TIMEOUT_SECONDS]];
    }

    private function withToken(array $headers, ?string $token, bool $json = true): array
    {
        if ($json) {
            $headers['Content-Type'] = 'application/json';
            $headers['Accept'] = 'application/json';
        }

        if ($token) {
            $headers['Authorization'] = sprintf('Bearer %s', $token);
        }

        return $headers;
    }

    private function mapException(RequestException $exception): MadelineRequestException|MadelineNotLoggedInException|MadelineNoAuthSessionException
    {
        $decodedContent = $this->getRequestExceptionContent($exception);
        $messageCode = (string) Arr::get($decodedContent, 'message_code');

        return match ($messageCode) {
            MessageCodesEnum::NOT_LOGGED_IN->value => new MadelineNotLoggedInException(),
            MessageCodesEnum::NO_AUTH_SESSION->value => new MadelineNoAuthSessionException(),
            default => new MadelineRequestException(
                (string) Arr::get($decodedContent, 'message', $exception->getMessage()),
                (int) $exception->getCode(),
                $exception,
                $messageCode,
            ),
        };
    }

    private function isBanned(MadelineRequestException $exception): bool
    {
        return $exception->getCode() === Response::HTTP_SERVICE_UNAVAILABLE
            || $exception->getErrorCode() === MessageCodesEnum::ACCOUNT_BANNED->value;
    }

    protected function client(): Client
    {
        return new Client(['base_uri' => rtrim(trim((string) config('madeline-proto-integration.url')), '/\\') . '/']);
    }

    private function getResponseContent(ResponseInterface $response): array
    {
        return json_decode($response->getBody()->getContents(), true) ?: [];
    }
}
