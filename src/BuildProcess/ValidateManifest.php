<?php

namespace Laravel\VaporCli\BuildProcess;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;

class ValidateManifest
{
    use ParticipatesInBuildProcess;

    /**
     * Execute the build process step.
     *
     * @return void
     */
    public function __invoke()
    {
        Helpers::step('<options=bold>Validating Manifest File</>');

        $this->warnAboutDeprecations();
    }

    /**
     * Check and warn about deprecations, if any.
     *
     * @return $this
     */
    protected function warnAboutDeprecations()
    {
        if (Manifest::shouldSeparateVendor($this->environment)) {
            Helpers::warn(
                '- The "separate-vendor" option is deprecated.'
                .' Please use Docker based deployments instead:'
                .' https://docs.vapor.build/1.0/projects/environments.html#runtime'
            );
        }

        return $this;
    }
}
