<?php

namespace Laravel\VaporCli\Aws;

use GuzzleHttp\Client;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\ConsoleVaporClient;
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
    public function store($url, array $headers = [], $file, $withProgress = false)
    {
        $stream = fopen($file, 'r+');

        $size = round(filesize($file) / 1024 / 1024, 2);

        $size = $size > 1 ? $size : 1;

        if ($withProgress && $size > 1) {
            $progressBar = new ProgressBar(Helpers::app('output'), $size);
            $progressBar->setFormat(' %current%MB/%max%MB [%bar%] %percent:3s%% (%remaining:-6s% remaining)');
            $progressBar->start();
        } else {
            $progressBar = null;
        }

        $progressCallback = $withProgress && $size > 1 ? function ($_, $__, $___, $uploaded) use ($progressBar) {
            $progressBar->setProgress(round($uploaded / 1024 / 1024, 2));
        }
        : null;

        $response = (new Client)->request('PUT', $url, array_filter([
            'body' => $stream,
            'headers' => empty($headers) ? null : $headers,
            'progress' => $progressCallback
        ]));

        if ($withProgress && $size > 1) {
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
        (new Client)->request('PUT', $url, array_filter([
            'headers' => empty($headers) ? null : $headers,
        ]));
    }
}
