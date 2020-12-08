<?php

namespace Laravel\VaporCli;

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
        (new Process([
            'docker', 'build',
            '--file='.$path.'/'.$environment.'.Dockerfile',
            '--tag='.$project.':'.$environment,
            '.'
        ], $path))->mustRun();
    }

    /**
     * Build a docker image.
     *
     * @param  string  $path
     * @param  string  $project
     * @param  string  $environment
     * @param  string  $repoUri
     * @param  string  $name
     * @return void
     */
    public static function publish($path, $project, $environment, $repoUri, $name)
    {
        (new Process([
            'docker', 'tag',
            $project.':'.$environment,
            $repoUri.':'.$name
        ], $path))->mustRun();

        (new Process([
            'docker', 'push',
            $repoUri.':'.$name
        ], $path))
            ->setTimeout(5 * 60)
            ->mustRun(function ($type, $line) {
                Helpers::write($line);
            });
    }

    /**
     * Write the given content to the environment dockerfile.
     *
     * @param string  $environment
     * @param string  $content
     *
     * @return void
     */
    protected static function write($environment, $content)
    {
        file_put_contents(
            Path::dockerfile(),
            $content
        );
    }
}
