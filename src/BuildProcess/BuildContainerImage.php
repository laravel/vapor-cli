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

        $this->validateDockerFile($this->environment);

        Helpers::step('<options=bold>Building Container Image</>');

        Docker::build(
            $this->appPath,
            Manifest::name(),
            $this->environment,
            array_merge(['__VAPOR_RUNTIME='.Manifest::runtime($this->environment)], $this->buildArgs)
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
     */
    protected function validateDockerFile($environment)
    {
        $runtime = Manifest::runtime($environment);
        $contents = file_get_contents(Path::dockerfile($environment));

        // Return early if the image isn't built from a Laravel base image.
        if (! Str::contains($contents, 'vapor:php')) {
            return;
        }

        if ($runtime === 'docker' && Str::contains($contents, '-arm')) {
            Helpers::abort('An ARM based image cannot be used with the "docker" runtime.');
        }

        if ($runtime === 'docker-arm' && ! Str::contains($contents, '-arm')) {
            Helpers::abort('An x86 based image cannot be used with the "docker-arm" runtime.');
        }
    }
}
