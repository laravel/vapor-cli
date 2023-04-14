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
        if (! array_key_exists('id', static::current() ?? [])) {
            Helpers::abort(sprintf('Invalid project ID. Please verify your Vapor manifest at [%s].', Path::manifest()));
        }

        return static::current()['id'];
    }

    /**
     * Get the project name from the current directory's manifest.
     *
     * @return int
     */
    public static function name()
    {
        if (! array_key_exists('name', static::current() ?? [])) {
            Helpers::abort(sprintf('Invalid project name. Please verify your Vapor manifest at [%s].', Path::manifest()));
        }

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
            Helpers::abort(sprintf('Unable to find a Vapor manifest at [%s].', Path::manifest()));
        }

        return Yaml::parse(file_get_contents(Path::manifest()));
    }

    /**
     * Get the default environment of the project.
     *
     * @return string
     */
    public static function defaultEnvironment()
    {
        return static::current()['default-environment'] ?? 'staging';
    }

    /**
     * Get the build commands for the given environment.
     *
     * @param  string  $environment
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
     * Get the Dockerfile for the given environment.
     *
     * @param  string  $environment
     * @return string
     */
    public static function dockerfile($environment)
    {
        return static::current()['environments'][$environment]['dockerfile'] ?? "{$environment}.Dockerfile";
    }

    /**
     * Get the Docker build arguments.
     *
     * @param  string  $environment
     * @return array
     */
    public static function dockerBuildArgs($environment)
    {
        return static::current()['environments'][$environment]['docker-build-args'] ?? [];
    }

    /**
     * Determine if the environment uses a database proxy.
     *
     * @param  string  $environment
     * @return bool
     */
    public static function databaseProxy($environment)
    {
        return static::current()['environments'][$environment]['database-proxy'] ?? false;
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
     * Determine if we should exclude the node_modules directory.
     *
     * @return bool
     */
    public static function excludeNodeModules()
    {
        if (isset(static::current()['exclude-node-modules'])) {
            return static::current()['exclude-node-modules'];
        }

        return true;
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
     * Get the runtime for the given environment.
     *
     * @param  string  $environment
     * @return string|null
     */
    public static function runtime($environment)
    {
        return static::current()['environments'][$environment]['runtime'] ?? null;
    }

    /**
     * Determine if the environment uses Octane.
     *
     * @param  string  $environment
     * @return bool
     */
    public static function octane($environment)
    {
        return static::current()['environments'][$environment]['octane'] ?? false;
    }

    /**
     * Determine if the environment uses Octane database session ttl.
     *
     * @param  string  $environment
     * @return int
     */
    public static function octaneDatabaseSessionTtl($environment)
    {
        return static::current()['environments'][$environment]['octane-database-session-ttl'] ?? 0;
    }

    /**
     * Determine if the environment uses a Docker image.
     *
     * @param  string  $environment
     * @return bool
     */
    public static function usesContainerImage($environment)
    {
        return in_array(
            static::current()['environments'][$environment]['runtime'] ?? null,
            ['docker', 'docker-arm']
        );
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
     * @param  array  $project
     * @return void
     */
    public static function fresh($project)
    {
        static::freshConfiguration($project);
    }

    /**
     * Write a fresh main manifest file for the given project.
     *
     * @param  array  $project
     * @return void
     */
    protected static function freshConfiguration($project)
    {
        $environments['production'] = array_filter([
            'memory' => 1024,
            'cli-memory' => 512,
            'runtime' => 'php-8.2:al2',
            'build' => [
                'COMPOSER_MIRROR_PATH_REPOS=1 composer install --no-dev',
                'php artisan event:cache',
                file_exists(Path::current().'/webpack.mix.js')
                    ? 'npm ci && npm run prod && rm -rf node_modules'
                    : 'npm ci && npm run build && rm -rf node_modules',
            ],
        ]);

        if (! (isset($project['is_sandboxed']) && $project['is_sandboxed'])) {
            $environments['staging'] = array_filter([
                'memory' => 1024,
                'cli-memory' => 512,
                'runtime' => 'php-8.2:al2',
                'build' => [
                    'COMPOSER_MIRROR_PATH_REPOS=1 composer install',
                    'php artisan event:cache',
                    file_exists(Path::current().'/webpack.mix.js')
                        ? 'npm ci && npm run dev && rm -rf node_modules'
                        : 'npm ci && npm run build && rm -rf node_modules',
                ],
            ]);
        }

        static::write(array_filter([
            'id' => $project['id'],
            'name' => $project['name'],
            'environments' => $environments,
        ]));
    }

    /**
     * Add an environment to the manifest.
     *
     * @param  string  $environment
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
     * @param  string  $environment
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
     * @param  string|null  $path
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
