<?php

namespace Laravel\VaporCli\BuildProcess;

use Laravel\VaporCli\Docker;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;

class BuildContainerImage
{
    use ParticipatesInBuildProcess;

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
