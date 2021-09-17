<?php

namespace Laravel\VaporCli\BuildProcess;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;

class ConfigureComposerAutoloader
{
    use ParticipatesInBuildProcess;

    /**
     * Execute the build process step.
     *
     * @return void
     */
    public function __invoke()
    {
        if (! Manifest::shouldSeparateVendor($this->environment)) {
            return;
        }

        Helpers::step('<options=bold>Configuring Composer Autoloader</>');

        file_put_contents(
            $this->appPath.'/vendor/composer/autoload_static.php',
            $this->configure($this->appPath.'/vendor/composer/autoload_static.php')
        );
    }

    /**
     * Configure the Artisan executable.
     *
     * @param  string  $file
     * @return string
     */
    protected function configure($file)
    {
        return str_replace(
            [
                "__DIR__ . '/../..'",
            ],
            [
                "'/var/task'",
            ],
            file_get_contents($file)
        );
    }
}
