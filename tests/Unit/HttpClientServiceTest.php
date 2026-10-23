<?php

declare(strict_types=1);

namespace Buyme\MadelineProtoIntegration\Tests\Unit;

use Buyme\MadelineProtoIntegration\Exceptions\Auth\MadelineNotLoggedInException;
use Buyme\MadelineProtoIntegration\Exceptions\MadelineRequestException;
use Buyme\MadelineProtoIntegration\Models\MpiAccountUser;
use Buyme\MadelineProtoIntegration\Services\V1\Auth\AuthTokenService;
use Buyme\MadelineProtoIntegration\Services\V1\Http\HttpClientService;
use Buyme\MadelineProtoIntegration\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

final class HttpClientServiceTest extends TestCase
{
    /** @var list<array{request: RequestInterface}> */
    private array $history = [];

    public function test_post_params_are_sent_as_json_body_not_query(): void
    {
        $service = $this->service([new Response(200, [], '{"sent":true}')]);

        $result = $service->performRequest('POST', 'v1/telegram/messages/send-simple-message', ['peer' => 'user', 'message' => 'Привіт']);

        $request = $this->lastRequest();
        $this->assertSame(['sent' => true], $result);
        $this->assertSame('', $request->getUri()->getQuery());
        $this->assertSame(['peer' => 'user', 'message' => 'Привіт'], json_decode((string) $request->getBody(), true));
        $this->assertSame('application/json', $request->getHeaderLine('Content-Type'));
    }

    public function test_get_params_are_sent_as_query(): void
    {
        $service = $this->service([new Response(200, [], '{}'), new Response(200, [], '{}')]);

        $service->performRequest('GET', 'v1/telegram/users/exists/by-username', ['username' => 'buyme']);
        $this->assertSame('username=buyme', $this->lastRequest()->getUri()->getQuery());
        $this->assertSame('', (string) $this->lastRequest()->getBody());

        $service->performRequest('GET', 'v1/auth/check');
        $this->assertSame('https://mp.test/api/v1/auth/check', (string) $this->lastRequest()->getUri());
    }

    public function test_auth_token_is_sent_as_bearer(): void
    {
        app(AuthTokenService::class)->setAuthToken('secret-token');

        $this->service([new Response(200, [], '{}')])->performRequest('GET', 'v1/auth/check');

        $this->assertSame('Bearer secret-token', $this->lastRequest()->getHeaderLine('Authorization'));
    }

    public function test_failover_marks_banned_account_and_uses_next_one(): void
    {
        $banned = $this->account('first-token');
        $this->account('second-token');

        $service = $this->service([
            new Response(503, [], '{"message":"banned","message_code":"account.banned"}'),
            new Response(200, [], '{"sent":true}'),
        ]);

        $result = $service->performRequestMultipleAccounts('POST', 'v1/telegram/messages/send-simple-message', ['message' => 'x']);

        $this->assertSame(['sent' => true], $result);
        $this->assertTrue($banned->refresh()->is_banned);
        $this->assertSame('Bearer second-token', $this->lastRequest()->getHeaderLine('Authorization'));
    }

    public function test_failover_marks_banned_by_message_code(): void
    {
        $account = $this->account('only-token');

        $service = $this->service([new Response(500, [], '{"message":"banned","message_code":"account.banned"}')]);

        try {
            $service->performRequestMultipleAccounts('POST', 'v1/telegram/messages/send-simple-message', ['message' => 'x']);
            $this->fail('Expected MadelineRequestException');
        } catch (MadelineRequestException $e) {
            $this->assertSame('All accounts failed', $e->getMessage());
        }

        $this->assertTrue($account->refresh()->is_banned);
    }

    public function test_known_message_codes_map_to_dedicated_exceptions(): void
    {
        $service = $this->service([new Response(401, [], '{"message":"no","message_code":"not.logged.in"}')]);

        $this->expectException(MadelineNotLoggedInException::class);

        $service->performRequest('GET', 'v1/telegram/auth/check');
    }

    public function test_network_error_becomes_request_exception(): void
    {
        $service = $this->service([new ConnectException('connection refused', new Request('GET', 'v1/auth/check'))]);

        $this->expectException(MadelineRequestException::class);
        $this->expectExceptionMessage('connection refused');

        $service->performRequest('GET', 'v1/auth/check');
    }

    public function test_token_cache_key_is_namespaced(): void
    {
        app(AuthTokenService::class)->setAuthToken('abc');

        $this->assertNull(cache()->get('auth_token'));
        $this->assertSame('abc', app(AuthTokenService::class)->getAuthToken());
    }

    /**
     * @param list<Response|\Throwable> $responses
     */
    private function service(array $responses): HttpClientService
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->history));

        return new readonly class(app(AuthTokenService::class), new Client(['handler' => $stack, 'base_uri' => 'https://mp.test/api/'])) extends HttpClientService {
            public function __construct(AuthTokenService $authTokenService, private Client $mockClient)
            {
                parent::__construct($authTokenService);
            }

            protected function client(): Client
            {
                return $this->mockClient;
            }
        };
    }

    private function account(string $token): MpiAccountUser
    {
        return MpiAccountUser::query()->create([
            'login' => $token . '@mp.test',
            'password' => 'secret',
            'token' => $token,
        ]);
    }

    private function lastRequest(): RequestInterface
    {
        return end($this->history)['request'];
    }
}
