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
            'php-8.0',
        ])) {
            Helpers::warn(
                'The runtimes "php-7.3", "php-7.4", and "php-8.0" are deprecated.'
                .' Those runtimes are based on Amazon Linux 1, for which AWS standard support has ended.'
                .' Amazon Linux 1 is only receiving critical and important security updates and it may not work with new Vapor/AWS features.'
                .' Please use Amazon Linux 2 with "php-7.4:al2" or "php-8.0:al2" instead.'
            );
        }

        return $this;
    }
}
