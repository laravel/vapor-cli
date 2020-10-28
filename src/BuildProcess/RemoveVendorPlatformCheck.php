<?php

namespace Laravel\VaporCli\BuildProcess;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Path;

class RemoveVendorPlatformCheck
{
    use ParticipatesInBuildProcess;

    /**
     * Execute the build process step.
     *
     * @return void
     */
    public function __invoke()
    {
        Helpers::step('<options=bold>Removing Composer Platform Check</>');

        if ($this->files->exists($path = Path::app().'/vendor/composer/platform_check.php')) {
            $this->files->put($path, '<?php'.PHP_EOL);
        }
    }
}
