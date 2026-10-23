<?php

declare(strict_types=1);

namespace Buyme\MadelineProtoIntegration\Services\V1\Auth;

use Illuminate\Support\Facades\Cache;

class AuthTokenService
{
    private const TTL = 3600 * 24; // 24 hours

    private const AUTH_TOKEN_SESSION_KEY = 'madeline-proto-integration:auth_token';

    public function getAuthToken(): ?string
    {
        $token = Cache::get(self::AUTH_TOKEN_SESSION_KEY);

        return strval($token) ?: null;
    }

    public function setAuthToken(string $token): void
    {
        Cache::put(self::AUTH_TOKEN_SESSION_KEY, $token, self::TTL);
    }
}
