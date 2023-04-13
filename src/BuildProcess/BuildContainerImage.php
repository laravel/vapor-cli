<?php

namespace Laravel\VaporCli\BuildProcess;

use Illuminate\Support\Str;
use Laravel\VaporCli\Docker;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Laravel\VaporCli\Path;

class BuildContainerImage
{
    use ParticipatesInBuildProcess {
        ParticipatesInBuildProcess::__construct as baseConstructor;
    }

    /**
     * The Docker for various PHP x86 versions.
     *
     * @var array<int, string>
     */
    public static $x86Images = [
        'laravelphp/vapor:php73',
        'laravelphp/vapor:php74',
        'laravelphp/vapor:php80',
        'laravelphp/vapor:php81',
        'laravelphp/vapor:php82',
    ];

    /**
     * The Docker for various PHP Arm versions.
     *
     * @var array<int, string>
     */
    public static $armImages = [
        'laravelphp/vapor:php82-arm',
    ];

    /**
     * The Docker build arguments.
     *
     * @var array
     */
    protected $buildArgs;

    /**
     * Create a new project builder.
     *
     * @param  string|null  $environment
     * @param  array  $buildArgs
     * @return void
     */
    public function __construct($environment = null, $buildArgs = [])
    {
        $this->baseConstructor($environment);

        $this->buildArgs = $buildArgs;
    }

    /**
     * Execute the build process step.
     *
     * @return void
     */
    public function __invoke()
    {
        if (! Manifest::usesContainerImage($this->environment)) {
            return;
        }

        if (! $this->validateDockerFile($this->environment, $runtime = Manifest::runtime($this->environment))) {
            Helpers::abort('The base image used in '.Path::dockerfile($this->environment).' is incompatible with the "'.$runtime.'" runtime, or you are running an outdated version of Vapor CLI.');
        }

        Helpers::step('<options=bold>Building Container Image</>');

        Docker::build(
            $this->appPath,
            Manifest::name(),
            $this->environment,
            $this->formatBuildArguments()
        );
    }

    /**
     * Get the image tag name.
     *
     * @return string
     */
    protected function getTagName()
    {
        return Manifest::name().':'.$this->environment;
    }

    /**
     * Ensure the provided Dockerfile is compatible with the runtime.
     *
     * @param  string  $environment
     * @param  string  $runtime
     * @return bool
     */
    public function validateDockerFile($environment, $runtime)
    {
        $contents = file_get_contents(Path::dockerfile($environment));

        if (! Str::contains($contents, 'laravelphp/vapor:php')) {
            // Custom image...
            return true;
        }

        if ($runtime === 'docker') {
            foreach (static::$x86Images as $image) {
                if (! Str::contains($contents, 'FROM '.$image.'-')
                    && Str::contains($contents, 'FROM '.$image)) {
                    return true;
                }
            }
        } elseif ($runtime === 'docker-arm') {
            foreach (static::$armImages as $image) {
                if (Str::contains($contents, 'FROM '.$image)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Format the Docker build arguments.
     *
     * @return array<int, string>
     */
    public function formatBuildArguments()
    {
        return array_merge(
            ['__VAPOR_RUNTIME='.Manifest::runtime($this->environment)],
            array_filter($this->buildArgs, function ($value) {
                return ! Str::startsWith($value, '__VAPOR_RUNTIME');
            })
        );
    }
}
