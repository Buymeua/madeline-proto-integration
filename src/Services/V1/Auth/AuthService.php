<?php

declare(strict_types=1);

namespace Buyme\MadelineProtoIntegration\Services\V1\Auth;

use Buyme\MadelineProtoIntegration\Enum\Http\HttpRequestMethodsEnum;
use Buyme\MadelineProtoIntegration\Models\MpiAccountUser;
use Buyme\MadelineProtoIntegration\Services\V1\Http\HttpClientService;
use Throwable;

final readonly class AuthService
{
    public function __construct(
        private HttpClientService $httpClientService,
        private AuthTokenService $authTokenService,
    ) {
    }

    public function autoLogin(): bool
    {
        if ($this->check()) {
            return true;
        }

        return $this->login();
    }

    public function check(): bool
    {
        $requestEndpoint = 'v1/auth/check';

        try {
            $response = $this->httpClientService->performRequest(HttpRequestMethodsEnum::METHOD_GET->value, $requestEndpoint);
        } catch (Throwable) {
            return false;
        }

        return boolval($response['data']['authenticated'] ?? false);
    }

    public function login(): bool
    {
        $requestEndpoint = 'v1/auth/login';

        $params = [
            'login' => config('madeline-proto-integration.auth.login'),
            'password' => config('madeline-proto-integration.auth.password'),
        ];

        try {
            $response = $this->httpClientService->performRequest(
                HttpRequestMethodsEnum::METHOD_POST->value,
                $requestEndpoint,
                $params
            );

            $token = $response['data']['token'] ?? null;

            if ($token) {
                $this->authTokenService->setAuthToken($token);
            }

            return !is_null($token);
        } catch (Throwable $th) {
            report($th);

            return false;
        }
    }

    public function multiLogin(): bool
    {
        $requestEndpoint = 'v1/auth/login';

        $params = [
            'login' => config('madeline-proto-integration.auth.login'),
            'password' => config('madeline-proto-integration.auth.password'),
        ];

        try {
            $response = $this->httpClientService->performRequest(
                HttpRequestMethodsEnum::METHOD_POST->value,
                $requestEndpoint,
                $params
            );

            $token = $response['data']['token'] ?? null;

            if ($token) {
                $this->authTokenService->setAuthToken($token);
            }

            return !is_null($token);
        } catch (Throwable $th) {
            report($th);

            return false;
        }
    }
}
