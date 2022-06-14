<?php

namespace unit;

use GuzzleHttp\Exception\ConnectException;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use Barberry\GuzzleMiddleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;

class GuzzleMiddlewareTest extends TestCase
{
    public function testRetryRequestCatchesServerError(): void
    {
        $middleware = GuzzleMiddleware::retryRequest(2, 0.01);
        $handler = new MockHandler([
            new Response(501),
            new Response(502),
            new Response(200)
        ]);
        $client = new Client(['handler' => $middleware($handler)]);
        $response = $client->get('http://barberry.host');

        self::assertEquals(200, $response->getStatusCode());
    }

    public function testRetryRequestCatchesConnectionError(): void
    {
        $middleware = GuzzleMiddleware::retryRequest(1, 0.01);
        $request = new Request('GET', 'http://barberry.host');
        $connectException = new ConnectException(
            'Connection failed',
            $request
        );
        $handler = new MockHandler([
            $connectException,
            new Response(200)
        ]);
        $client = new Client(['handler' => $middleware($handler)]);
        $response = $client->send($request, []);

        self::assertEquals(200, $response->getStatusCode());
    }

    public function testRetryRequestDoNotCatchClientError(): void
    {
        $middleware = GuzzleMiddleware::retryRequest(1, 0.01);
        $handler = new MockHandler([new Response(400), new Response(202)]);
        $client = new Client(['handler' => $middleware($handler)]);
        $response = $client->get('http://barberry.host');

        self::assertEquals(400, $response->getStatusCode());
    }
}
