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

readonly class HttpClientService implements HttpClientServiceInterface
{
    use GuzzleHttpResponseTrait;

    public function __construct(private AuthTokenService $authTokenService)
    {
    }

    public function performRequest(string $method, string $uri, array $params = [], array $headers = []): array
    {
        $baseUri = trim(strval(config('madeline-proto-integration.url')), ' \\');

        $client = new Client(['base_uri' => $baseUri . '\\']);

        $requestUri = trim($uri, ' \\');

        $this->applyDefaultHeaders($headers);

        $requestOptions = [
            'headers' => $headers,
        ];

        $this->prepareParams($method, $params, $requestUri, $requestOptions);

        try {
            $response = $client->request($method, $requestUri, $requestOptions);

            return $this->getResponseContent($response);
        } catch (RequestException $exception) {
            $decodedContent = $this->getRequestExceptionContent($exception);
            $messageCode = strval(Arr::get($decodedContent, 'message_code'));

            $customException = match ($messageCode) {
                MessageCodesEnum::NOT_LOGGED_IN->value => MadelineNotLoggedInException::class,
                MessageCodesEnum::NO_AUTH_SESSION->value => MadelineNoAuthSessionException::class,
                default => null,
            };

            if (is_null($customException)) {
                throw new MadelineRequestException(
                    strval(Arr::get($decodedContent, 'message')),
                    $exception->getCode(),
                    $exception,
                    $messageCode
                );
            }

            throw new $customException;
        }
    }

    public function performMultipartRequest(string $method, string $uri, array $data = [], array $files = [], array $headers = []): array
    {
        $baseUri = trim(strval(config('madeline-proto-integration.url')), ' \\');

        $client = new Client(['base_uri' => $baseUri . '\\']);

        $requestUri = trim($uri, ' \\');

        $this->applyDefaultHeaders($headers,false);

        $multipart = [];

        foreach ($data as $name => $value) {
            $multipart[] = [
                'name' => $name,
                'contents' => (string) $value,
            ];
        }

        foreach ($files as $name => $file) {
            $multipart[] = [
                'name' => $name,
                'contents' => fopen($file['path'], 'r'),
                'filename' => $file['name'] ?? basename($file['path']),
                'headers' => [
                    'Content-Type' => $file['mime'] ?? 'application/octet-stream',
                ],
            ];
        }

        $requestOptions = [
            'headers' => $headers,
            'multipart' => $multipart,
            'timeout' => 180,
        ];

        try {
            $response = $client->request($method, $requestUri, $requestOptions);

            return $this->getResponseContent($response);
        } catch (RequestException $exception) {
            $decodedContent = $this->getRequestExceptionContent($exception);
            $messageCode = strval(Arr::get($decodedContent, 'message_code'));

            $customException = match ($messageCode) {
                MessageCodesEnum::NOT_LOGGED_IN->value => MadelineNotLoggedInException::class,
                MessageCodesEnum::NO_AUTH_SESSION->value => MadelineNoAuthSessionException::class,
                default => null,
            };

            if (is_null($customException)) {
                throw new MadelineRequestException(
                    strval(Arr::get($decodedContent, 'message')),
                    $exception->getCode(),
                    $exception,
                    $messageCode
                );
            }

            throw new $customException;
        }
    }

	/**
	 * @throws MadelineRequestException
	 * @throws GuzzleException
	 */
	public function performRequestMultipleAccounts(string $method, string $uri, array $params = [], array $headers = []): array
    {
		$users = MpiAccountUser::get();

        $baseUri = trim(strval(config('madeline-proto-integration.url')), ' \\');

        $client = new Client(['base_uri' => $baseUri . '\\']);

        $requestUri = trim($uri, ' \\');

        $this->applyDefaultHeadersWithoutToken($headers);

		$errors = [];

		foreach	($users as $user) {
			$headers['Authorization'] = sprintf('Bearer %s', $user->token);

			$requestOptions = [
				'headers' => $headers,
			];

			$this->prepareParams($method, $params, $requestUri, $requestOptions);

			try {
				$response = $client->request($method, $requestUri, $requestOptions);

				return $this->getResponseContent($response);
			} catch (RequestException $exception) {
				$decodedContent = $this->getRequestExceptionContent($exception);
				$messageCode = strval(Arr::get($decodedContent, 'message_code'));

				$customException = match ($messageCode) {
					MessageCodesEnum::NOT_LOGGED_IN->value => MadelineNotLoggedInException::class,
					MessageCodesEnum::NO_AUTH_SESSION->value => MadelineNoAuthSessionException::class,
					default => null,
				};

				$errors[] = [
					'user_id' => $user->id,
					'message_code' => $messageCode,
					'error' => $exception->getMessage(),
				];

//				if (is_null($customException)) {}
			}
		}

		throw new MadelineRequestException(
			'All accounts failed',
			0,
			null,
			json_encode($errors, JSON_UNESCAPED_UNICODE)
		);
    }

	/**
	 * Выполнить multipart-запрос к Madeline API с перебором нескольких аккаунтов.
	 *
	 * @throws MadelineRequestException
	 * @throws GuzzleException
	 */
	public function performMultipartRequestMultipleAccounts(
		string $method,
		string $uri,
		array $data = [],
		array $files = [],
		array $headers = []
	): array {
		$users = MpiAccountUser::get();

		$baseUri = trim(strval(config('madeline-proto-integration.url')), ' \\');
		$client = new Client(['base_uri' => $baseUri . '\\']);
		$requestUri = trim($uri, ' \\');

		$this->applyDefaultHeadersWithoutToken($headers, false);

		$errors = [];

		foreach ($users as $user) {
			$headers['Authorization'] = sprintf('Bearer %s', $user->token);

			$multipart = [];

			foreach ($data as $name => $value) {
				$multipart[] = [
					'name' => $name,
					'contents' => (string) $value,
				];
			}

			foreach ($files as $name => $file) {
				$multipart[] = [
					'name' => $name,
					'contents' => fopen($file['path'], 'r'),
					'filename' => $file['name'] ?? basename($file['path']),
					'headers' => [
						'Content-Type' => $file['mime'] ?? 'application/octet-stream',
					],
				];
			}

			$requestOptions = [
				'headers' => $headers,
				'multipart' => $multipart,
				'timeout' => 180,
			];

			try {
				$response = $client->request($method, $requestUri, $requestOptions);

				return $this->getResponseContent($response);

			} catch (RequestException $exception) {
				$decodedContent = $this->getRequestExceptionContent($exception);
				$messageCode = strval(Arr::get($decodedContent, 'message_code'));

				$customException = match ($messageCode) {
					MessageCodesEnum::NOT_LOGGED_IN->value => MadelineNotLoggedInException::class,
					MessageCodesEnum::NO_AUTH_SESSION->value => MadelineNoAuthSessionException::class,
					default => null,
				};

				$errors[] = [
					'user_id' => $user->id,
					'message_code' => $messageCode,
					'error' => $exception->getMessage(),
				];

//				if (!is_null($customException)) {}

			}
		}

		throw new MadelineRequestException(
			'All accounts failed',
			0,
			null,
			json_encode($errors, JSON_UNESCAPED_UNICODE)
		);
	}


    protected function getResponseContent(ResponseInterface $response): array
    {
        $content = $response->getBody()->getContents();
        $decodedContent = json_decode($content, true);

        return ($decodedContent) ?: [];
    }

    protected function prepareParams(
        string $method,
        array $params,
        string &$requestUri,
        array &$requestOptions,
    ): void {
        if (in_array($method, [HttpRequestMethodsEnum::METHOD_POST, HttpRequestMethodsEnum::METHOD_PUT])) {
            $requestOptions['body'] = json_encode($params);

            return;
        }

        $queryParams = http_build_query($params);
        $requestUri .= '?' . $queryParams;
    }

    protected function applyDefaultHeaders(array &$headers, bool $forceJson = true): void
    {
        if ($forceJson) {
            $headers['Content-Type'] = 'application/json';
            $headers['Accept'] = 'application/json';
        }

        $authToken = $this->authTokenService->getAuthToken();

        if ($authToken) {
            $headers['Authorization'] = sprintf('Bearer %s', $authToken);
        }
    }

    protected function applyDefaultHeadersWithoutToken(array &$headers, bool $forceJson = true): void
    {
        if ($forceJson) {
            $headers['Content-Type'] = 'application/json';
            $headers['Accept'] = 'application/json';
        }
    }
}
