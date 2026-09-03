<?php

namespace Laravel\VaporCli\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Laravel\VaporCli\ConsoleVaporClient;
use PHPUnit\Framework\TestCase;

class RequestMethodCasingTest extends TestCase
{
    /**
     * @dataProvider methodDataProvider
     */
    public function test_the_request_method_is_sent_uppercase($method)
    {
        $client = new ConsoleVaporClientWithRecordedRequests();

        $client->requestWithoutErrorHandling($method, '/api/projects/1');

        $this->assertSame('GET', $client->recorded[0]['request']->getMethod());
    }

    public static function methodDataProvider()
    {
        return [
            'lowercase' => ['get'],
            'uppercase' => ['GET'],
            'mixed case' => ['Get'],
        ];
    }
}

class ConsoleVaporClientWithRecordedRequests extends ConsoleVaporClient
{
    public $recorded = [];

    public function requestWithoutErrorHandling($method, $uri, array $json = [])
    {
        return parent::requestWithoutErrorHandling($method, $uri, $json);
    }

    protected function client()
    {
        $stack = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode([])),
        ]));

        $stack->push(Middleware::history($this->recorded));

        return new Client([
            'base_uri' => 'https://vapor.laravel.com',
            'handler' => $stack,
        ]);
    }
}
