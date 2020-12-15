<?php

namespace Laravel\VaporCli;

use Symfony\Component\Yaml\Yaml;

class Manifest
{
    /**
     * Get the project ID from the current directory's manifest.
     *
     * @return int
     */
    public static function id()
    {
        return static::current()['id'];
    }

    /**
     * Get the project name from the current directory's manifest.
     *
     * @return int
     */
    public static function name()
    {
        return static::current()['name'];
    }

    /**
     * Retrieve the manifest for the current working directory.
     *
     * @return array
     */
    public static function current()
    {
        if (! file_exists(Path::manifest())) {
            Helpers::abort('Unable to find a Vapor manifest in this directory.');
        }

        return Yaml::parse(file_get_contents(Path::manifest()));
    }

    /**
     * Get the build commands for the given environment.
     *
     * @param string $environment
     *
     * @return array
     */
    public static function buildCommands($environment)
    {
        if (! isset(static::current()['environments'][$environment])) {
            Helpers::abort("The [{$environment}] environment has not been defined.");
        }

        return static::current()['environments'][$environment]['build'] ?? [];
    }

    /**
     * Get the ignored file patterns for the project.
     *
     * @return array
     */
    public static function ignoredFiles()
    {
        return static::current()['ignore'] ?? [];
        // return static::current()['environments'][$environment]['ignore'] ?? [];
    }

    /**
     * Determine if we should separate the vendor directory.
     *
     * @param  string  $environment
     * @return bool
     */
    public static function shouldSeparateVendor($environment)
    {
        if (static::usesContainerImage($environment)) {
            return false;
        }

        return static::current()['separate-vendor'] ?? false;
    }

    /**
     * Determine if the environment uses a Docker image.
     *
     * @param  string  $environment
     * @return bool
     */
    public static function usesContainerImage($environment)
    {
        return (static::current()['environments'][$environment]['runtime'] ?? null) == 'docker';
    }

    /**
     * Determine if we should interpret dot files in the public directory as assets.
     *
     * @return bool
     */
    public static function dotFilesAsAssets()
    {
        return static::current()['dot-files-as-assets'] ?? false;
    }

    /**
     * Write a fresh manifest file for the given project.
     *
     * @param array $project
     *
     * @return void
     */
    public static function fresh($project)
    {
        static::freshConfiguration($project);
    }

    /**
     * Write a fresh main manifest file for the given project.
     *
     * @param array $project
     *
     * @return void
     */
    protected static function freshConfiguration($project)
    {
        static::write(array_filter([
            'id'           => $project['id'],
            'name'         => $project['name'],
            'environments' => [
                'production' => array_filter([
                    'memory'     => 1024,
                    'cli-memory' => 512,
                    'runtime'    => 'php-7.4',
                    'build'      => [
                        'COMPOSER_MIRROR_PATH_REPOS=1 composer install --no-dev',
                        'php artisan event:cache',
                        'npm ci && npm run prod && rm -rf node_modules',
                    ],
                ]),
                'staging' => array_filter([
                    'memory'     => 1024,
                    'cli-memory' => 512,
                    'runtime'    => 'php-7.4',
                    'build'      => [
                        'COMPOSER_MIRROR_PATH_REPOS=1 composer install',
                        'php artisan event:cache',
                        'npm ci && npm run dev && rm -rf node_modules',
                    ],
                ]),
            ],
        ]));
    }

    /**
     * Add an environment to the manifest.
     *
     * @param string $environment
     * @param array  $config
     *
     * @return void
     */
    public static function addEnvironment($environment, array $config = [])
    {
        $manifest = static::current();

        if (isset($manifest['environments'][$environment])) {
            Helpers::abort('That environment already exists.');
        }

        $manifest['environments'][$environment] = ! empty($config) ? $config : [
            'build' => ['COMPOSER_MIRROR_PATH_REPOS=1 composer install --no-dev'],
        ];

        $manifest['environments'] = collect(
            $manifest['environments']
        )->sortKeys()->all();

        static::write($manifest);
    }

    /**
     * Delete the given environment from the manifest.
     *
     * @param string $environment
     *
     * @return void
     */
    public static function deleteEnvironment($environment)
    {
        $manifest = static::current();

        unset($manifest['environments'][$environment]);

        static::write($manifest);
    }

    /**
     * Write the given array to disk as the new manifest.
     *
     * @param array       $manifest
     * @param string|null $path
     *
     * @return void
     */
    protected static function write(array $manifest, $path = null)
    {
        file_put_contents(
            $path ?: Path::manifest(),
            Yaml::dump($manifest, $inline = 20, $spaces = 4)
        );
    }
}
