<?php

namespace Laravel\VaporCli;

use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class Docker
{
    /**
     * Build a docker image.
     *
     * @param  string  $path
     * @param  string  $project
     * @param  string  $environment
     * @return void
     */
    public static function build($path, $project, $environment)
    {
        Process::fromShellCommandline(
            sprintf('docker build --pull --file=%s.Dockerfile --tag=%s .',
                $environment,
                Str::slug($project).':'.$environment
            ),
            $path
        )->setTimeout(null)->mustRun(function ($type, $line) {
            Helpers::write($line);
        });
    }

    /**
     * Publish a docker image.
     *
     * @param  string  $path
     * @param  string  $project
     * @param  string  $environment
     * @param  string  $token
     * @param  string  $repoUri
     * @param  string  $tag
     * @return void
     */
    public static function publish($path, $project, $environment, $token, $repoUri, $tag)
    {
        Process::fromShellCommandline(
            sprintf('docker tag %s %s',
                Str::slug($project).':'.$environment,
                $repoUri.':'.$tag
            ),
            $path
        )->setTimeout(null)->mustRun();

        Process::fromShellCommandline(
            sprintf('docker login --username AWS --password %s %s',
                str_replace('AWS:', '', base64_decode($token)),
                explode('/', $repoUri)[0]
            ),
            $path
        )->setTimeout(null)->mustRun();

        Process::fromShellCommandline(
            sprintf('docker push %s',
                $repoUri.':'.$tag
            ),
            $path
        )->setTimeout(null)->mustRun(function ($type, $line) {
            Helpers::write($line);
        });
    }
}
