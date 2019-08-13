<?php

namespace Laravel\VaporCli\BuildProcess;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Process\Process;

class InjectErrorPages
{
    use ParticipatesInBuildProcess;

    /**
     * Execute the build process step.
     *
     * @return void
     */
    public function __invoke()
    {
        Helpers::step('<bright>Injecting Error Pages</>');

        $stubPath = $this->appPath.'/vendor/laravel/vapor-core/stubs';

        if (! file_exists($this->appPath.'/503.html')) {
            $this->files->copy($stubPath.'/503.html', $this->appPath.'/503.html');
        }
    }
}
