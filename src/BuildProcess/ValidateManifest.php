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

        if (in_array(Manifest::runtime($this->environment), [
            'php-7.3',
            'php-7.4',
            'php-7.4:al2',
            'php-7.4:imagick',
            'php-8.0',
            'php-8.0:al2',
        ])) {
            Helpers::warn(
                'The runtimes "php-7.3", "php-7.4", "php-7.4:al2", "php-7.4:imagick", "php-8.0", and "php-8.0:al2" are deprecated and will no longer be supported or receiving any updates.'
                .' For a full list of supported runtimes, please see: https://docs.vapor.build/1.0/projects/environments.html#runtime'
            );
        }

        return $this;
    }
}
