<?php

namespace Laravel\VaporCli\BuildProcess;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Process\Process;

class InjectHandlers
{
    use ParticipatesInBuildProcess;

    /**
     * Execute the build process step.
     *
     * @return void
     */
    public function __invoke()
    {
        Helpers::step('<bright>Injecting Serverless Handlers</>');

        if (! is_dir(getcwd().'/vendor/laravel/vapor-core')) {
            Helpers::abort('Unable to find laravel/vapor-core installation.');
        }

        $stubPath = $this->appPath.'/vendor/laravel/vapor-core/stubs';

        $this->files->copy($stubPath.'/runtime.php', $this->appPath.'/runtime.php');
        $this->files->copy($stubPath.'/cliRuntime.php', $this->appPath.'/cliRuntime.php');
        $this->files->copy($stubPath.'/fpmRuntime.php', $this->appPath.'/fpmRuntime.php');
        $this->files->copy($stubPath.'/httpRuntime.php', $this->appPath.'/httpRuntime.php');
        $this->files->copy($stubPath.'/httpHandler.php', $this->appPath.'/httpHandler.php');
    }
}
