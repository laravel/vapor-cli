<?php

namespace Laravel\VaporCli\Aws;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Str;
use Laravel\VaporCli\ConsoleVaporClient;
use Laravel\VaporCli\Exceptions\RequestFailedException;
use Laravel\VaporCli\Helpers;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class AwsStorageProvider
{
    protected $vapor;

    const MAX_RETRIES = 3;

    /**
     * Create a new storage provider instance.
     *
     * @param  \Laravel\VaporCli\ConsoleVaporClient  $vapor
     * @return void
     */
    public function __construct(ConsoleVaporClient $vapor)
    {
        $this->vapor = $vapor;
    }

    /**
     * Store the given file using the given pre-signed URL.
     *
     * @param  string  $url
     * @param  array  $headers
     * @param  string  $file
     * @param  bool  $withProgress
     * @return void
     */
    public function store($url, array $headers, $file, $withProgress = false)
    {
        $stream = fopen($file, 'r+');

        $size = (int) round(filesize($file) / 1024, 2);

        if ($withProgress) {
            $progressBar = new ProgressBar(Helpers::app('output'), $size);
            $progressBar->setFormat(' %current%KB/%max%KB [%bar%] %percent:3s%% (%remaining:-6s% remaining)');
            $progressBar->start();
        } else {
            $progressBar = null;
        }

        $progressCallback = $withProgress ? function ($_, $__, $___, $uploaded) use ($progressBar) {
            $progressBar->setProgress((int) round($uploaded / 1024, 2));
        }
        : null;

        $response = (new Client())->request('PUT', $url, array_filter([
            'body' => $stream,
            'headers' => empty($headers) ? null : $headers,
            'progress' => $progressCallback,
        ]));

        if ($withProgress) {
            $progressBar->finish();
            Helpers::line();
        }

        if (is_resource($stream)) {
            fclose($stream);
        }
    }

    /**
     * Execute the given pre-signed request.
     *
     * @param  string  $method
     * @param  string  $url
     * @param  array  $headers
     * @return void
     */
    public function request($method, $url, array $headers = [])
    {
        (new Client())->request('PUT', $url, array_filter([
            'headers' => empty($headers) ? null : $headers,
        ]));
    }

    /**
     * Execute the given store requests.
     *
     * @param  array  $requests
     * @param  string  $assetPath
     * @param  callable  $callback
     * @return void
     */
    public function executeStoreRequests($requests, $assetPath, $callback)
    {
        collect($requests)->chunk(10)->each(function ($chunkOfRequests) use ($assetPath, $callback) {
            $requests = $chunkOfRequests->map(function ($request) use ($assetPath) {
                $file = $assetPath.'/'.$request['path'];
                $request['stream'] = fopen($file, 'r+');

                return $request;
            });

            $generator = function () use ($requests, $callback) {
                foreach ($requests as $request) {
                    $callback($request);

                    yield new Request(
                        'PUT',
                        $request['url'],
                        array_merge(
                            $request['headers'],
                            ['Cache-Control' => 'public, max-age=31536000']
                        ),
                        $request['stream']
                    );
                }
            };

            $failure = null;

            try {
                (new Pool($this->client(), $generator(), [
                    'concurrency' => 10,
                    'rejected' => function ($reason, $index) use (&$failure) {
                        if ($failure === null) {
                            $failure = new RequestFailedException($reason->getMessage(), $index);
                        }
                    },
                ]))
                    ->promise()
                    ->wait();
            } finally {
                $requests->each(function ($request) {
                    if (is_resource($request['stream'])) {
                        fclose($request['stream']);
                    }
                });
            }

            if ($failure !== null) {
                throw $failure;
            }
        });
    }

    /**
     * Execute the given copy requests.
     *
     * @param  array  $requests
     * @param  callable  $callback
     * @return void
     */
    public function executeCopyRequests($requests, $callback)
    {
        $generator = function () use ($requests, $callback) {
            foreach ($requests as $request) {
                $callback($request);

                yield new Request(
                    'PUT',
                    $request['url'],
                    array_merge(
                        $request['headers'],
                        ['Cache-Control' => 'public, max-age=31536000']
                    )
                );
            }
        };

        $failure = null;

        (new Pool($this->client(), $generator(), [
            'concurrency' => 10,
            'rejected' => function ($reason, $index) use (&$failure) {
                if ($failure === null) {
                    $failure = new RequestFailedException($reason->getMessage(), $index);
                }
            },
        ]))
            ->promise()
            ->wait();

        if ($failure !== null) {
            throw $failure;
        }
    }

    /**
     * Get a new HTTP client instance with the retry handler.
     *
     * @return \GuzzleHttp\Client
     */
    protected function client()
    {
        return new Client(['handler' => $this->retryHandler()]);
    }

    /**
     * Get a handler stack containing the retry middleware configuration.
     *
     * @return \GuzzleHttp\HandlerStack
     */
    protected function retryHandler()
    {
        $stack = HandlerStack::create();

        $stack->push(Middleware::retry(function (int $retries, RequestInterface $request, ?ResponseInterface $response = null) {
            $text = '<comment>Retrying Request: </comment><options=bold>'.$request->getMethod().'</> '.Str::before($request->getUri(), '?');

            if ($response && $response->getStatusCode() < 300) {
                return false;
            }

            if ($retries < self::MAX_RETRIES) {
                Helpers::step($text);

                return true;
            }

            return false;
        }));

        return $stack;
    }
}
