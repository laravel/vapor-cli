<?php

namespace Laravel\VaporCli;

class Dockerfile
{
    /**
     * Add a fresh Dockerfile for the given environment.
     *
     * @param  string  $environment
     * @return void
     */
    public static function fresh($environment)
    {
        $content = <<<'Dockerfile'
FROM laravelphp/vapor:php81

COPY . /var/task
Dockerfile;

        static::write($environment, $content);
    }

    /**
     * Write the given content to the environment Dockerfile.
     *
     * @param  string  $environment
     * @param  string  $content
     * @return void
     */
    protected static function write($environment, $content)
    {
        file_put_contents(
            Path::dockerfile($environment),
            $content
        );
    }

    /**
     * Delete the Dockerfile for the given environment.
     *
     * @param  string  $environment
     * @return void
     */
    public static function deleteEnvironment($environment)
    {
        @unlink(Path::dockerfile($environment));
    }
}
