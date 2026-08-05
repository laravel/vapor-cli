<?php

namespace Laravel\VaporCli\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response;
use Laravel\VaporCli\Aws\AwsStorageProvider;
use Laravel\VaporCli\Exceptions\RequestFailedException;
use PHPUnit\Framework\TestCase;

class AwsStorageProviderTest extends TestCase
{
    protected $assetPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assetPath = sys_get_temp_dir().'/vapor-cli-test-'.uniqid();

        mkdir($this->assetPath);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->assetPath.'/*') as $file) {
            unlink($file);
        }

        rmdir($this->assetPath);

        parent::tearDown();
    }

    public function test_copy_requests_execute_successfully()
    {
        $provider = new TestAwsStorageProvider(new MockHandler([
            new Response(200),
            new Response(200),
        ]));

        $handled = [];

        $provider->executeCopyRequests([
            ['url' => 'https://example.com/1', 'headers' => []],
            ['url' => 'https://example.com/2', 'headers' => []],
        ], function ($request) use (&$handled) {
            $handled[] = $request['url'];
        });

        $this->assertSame(['https://example.com/1', 'https://example.com/2'], $handled);
    }

    public function test_copy_requests_throw_request_failed_exception_when_a_request_fails()
    {
        $mock = new MockHandler([
            new ConnectException('Connection refused', new GuzzleRequest('PUT', 'https://example.com/1')),
            new Response(200),
        ]);

        $provider = new TestAwsStorageProvider($mock);

        $handled = [];

        try {
            $provider->executeCopyRequests([
                ['url' => 'https://example.com/1', 'headers' => []],
                ['url' => 'https://example.com/2', 'headers' => []],
            ], function ($request) use (&$handled) {
                $handled[] = $request['url'];
            });

            $this->fail('Expected RequestFailedException to be thrown.');
        } catch (RequestFailedException $e) {
            $this->assertStringContainsString('Connection refused', $e->getMessage());
            $this->assertSame(0, $e->getIndex());
        }

        // The pool should settle cleanly, meaning the remaining requests
        // were still executed and the mock queue was fully drained...
        $this->assertCount(2, $handled);
        $this->assertSame(0, $mock->count());
    }

    public function test_store_requests_execute_successfully()
    {
        file_put_contents($this->assetPath.'/a.txt', 'a');
        file_put_contents($this->assetPath.'/b.txt', 'b');

        $provider = new TestAwsStorageProvider(new MockHandler([
            new Response(200),
            new Response(200),
        ]));

        $handled = [];

        $provider->executeStoreRequests([
            ['path' => 'a.txt', 'url' => 'https://example.com/a', 'headers' => []],
            ['path' => 'b.txt', 'url' => 'https://example.com/b', 'headers' => []],
        ], $this->assetPath, function ($request) use (&$handled) {
            $handled[] = $request['path'];
        });

        $this->assertSame(['a.txt', 'b.txt'], $handled);
    }

    public function test_store_requests_throw_request_failed_exception_and_close_streams_on_failure()
    {
        file_put_contents($this->assetPath.'/a.txt', 'a');
        file_put_contents($this->assetPath.'/b.txt', 'b');

        $provider = new TestAwsStorageProvider(new MockHandler([
            new ConnectException('Connection refused', new GuzzleRequest('PUT', 'https://example.com/a')),
            new Response(200),
        ]));

        $streams = [];

        try {
            $provider->executeStoreRequests([
                ['path' => 'a.txt', 'url' => 'https://example.com/a', 'headers' => []],
                ['path' => 'b.txt', 'url' => 'https://example.com/b', 'headers' => []],
            ], $this->assetPath, function ($request) use (&$streams) {
                $streams[] = $request['stream'];
            });

            $this->fail('Expected RequestFailedException to be thrown.');
        } catch (RequestFailedException $e) {
            $this->assertStringContainsString('Connection refused', $e->getMessage());
        }

        // All opened file streams should have been closed, even on failure...
        $this->assertCount(2, $streams);

        foreach ($streams as $stream) {
            $this->assertFalse(is_resource($stream));
        }
    }
}

class TestAwsStorageProvider extends AwsStorageProvider
{
    /**
     * The mock handler stack.
     *
     * @var \GuzzleHttp\HandlerStack
     */
    protected $handlerStack;

    /**
     * Create a new test storage provider instance.
     *
     * @param  \GuzzleHttp\Handler\MockHandler  $mock
     * @return void
     */
    public function __construct(MockHandler $mock)
    {
        $this->handlerStack = HandlerStack::create($mock);
    }

    /**
     * Get a new HTTP client instance with the mock handler.
     *
     * @return \GuzzleHttp\Client
     */
    protected function client()
    {
        return new Client(['handler' => $this->handlerStack]);
    }
}
