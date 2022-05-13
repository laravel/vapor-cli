<?php

namespace Laravel\VaporCli\Aws;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\LazyCollection;
use Laravel\VaporCli\ConsoleVaporClient;
use Laravel\VaporCli\Exceptions\RequestFailedException;
use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Helper\ProgressBar;

class AwsStorageProvider
{
    protected $vapor;

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
     * @param  bool  $progress
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
            'body'     => $stream,
            'headers'  => empty($headers) ? null : $headers,
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
            $requests = LazyCollection::make($chunkOfRequests)->map(function ($request) use ($assetPath) {
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

            (new Pool(new Client(), $generator(), [
                'concurrency' => 10,
                'rejected' => function ($reason, $index) {
                    throw new RequestFailedException($reason->getMessage(), $index);
                },
            ]))
                ->promise()
                ->wait();

            $requests->each(function ($request) {
                fclose($request['stream']);
            });
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

        (new Pool(new Client(), $generator(), [
            'concurrency' => 10,
            'rejected' => function ($reason, $index) {
                throw new RequestFailedException($reason->getMessage(), $index);
            },
        ]))
            ->promise()
            ->wait();
    }
}
