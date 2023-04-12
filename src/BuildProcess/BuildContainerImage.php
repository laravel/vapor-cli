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

        if (! $this->validateDockerFile($this->environment, $runtime = Manifest::runtime($this->environment))) {
            Helpers::abort('The base image used in the '.Path::dockerfile($this->environment).'cannot be used with the "'.$runtime.'" runtime.');
        }

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
     * @return bool
     */
    public function validateDockerFile($environment, $runtime)
    {
        $contents = file_get_contents(Path::dockerfile($environment));

        $fromInstructions = Str::of($contents)
            ->explode("\n")
            ->filter(function ($line) {
                return Str::startsWith($line, 'FROM');
            });

        $isCustomImage = $fromInstructions->doesntContain(function ($instruction) {
            return Str::contains($instruction, 'vapor:php');
        });

        if ($isCustomImage) {
            return true;
        }

        $hasArmInstruction = $fromInstructions->contains(function ($instruction) {
            return Str::contains($instruction, 'vapor:php') && Str::endsWith($instruction, '-arm');
        });

        $hasX86Instruction = $fromInstructions->contains(function ($instruction) {
            return Str::contains($instruction, 'vapor:php') && ! Str::endsWith($instruction, '-arm');
        });

        if ($runtime === 'docker' && $hasArmInstruction) {
            return false;
        }

        if ($runtime === 'docker-arm' && $hasX86Instruction) {
            return false;
        }

        return true;
    }
}
