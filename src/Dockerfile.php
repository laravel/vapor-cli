<?php

namespace Laravel\VaporCli;

use Symfony\Component\Yaml\Yaml;

class Dockerfile
{
    /**
     * Add a fresh dockerfile for the given environment.
     *
     * @param string $environment
     *
     * @return void
     */
    public static function fresh($environment)
    {
        $content = <<<'Dockerfile'
FROM vapor-alpine-php74:latest

COPY . /var/task
Dockerfile;

        static::write($environment, $content);
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
            Path::dockerfile($environment),
            $content
        );
    }
}
