<?php

namespace Laravel\VaporCli\BuildProcess;

use Laravel\VaporCli\Docker;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;

class BuildContainerImage
{
    use ParticipatesInBuildProcess {
        __construct as private configure;
    }

    /**
     * @var string|null
     */
    private $image;

    public function __construct(?string $environment = null, ?string $image = null)
    {
        $this->configure($environment);
        $this->image = $image;
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

        if ($this->image) {
            Helpers::step('<options=bold>Pulling Container Image</>');
            Docker::pull($this->appPath, Manifest::name(), $this->environment, $this->image);

            return;
        }

        Helpers::step('<options=bold>Building Container Image</>');

        Docker::build($this->appPath, Manifest::name(), $this->environment);
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
}
