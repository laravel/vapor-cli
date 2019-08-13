<?php

namespace Laravel\VaporCli\BuildProcess;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Process\Process;

class ExecuteBuildCommands
{
    use ParticipatesInBuildProcess;

    /**
     * Execute the build process step.
     *
     * @return void
     */
    public function __invoke()
    {
        Helpers::step('<bright>Executing Build Commands</>');

        foreach (Manifest::buildCommands($this->environment) as $command) {
            Helpers::step('<comment>Running Command</comment>: '.$command);

            $process = new Process($command, $this->appPath, null, null, null);

            $process->mustRun(function ($type, $line) {
                Helpers::write($line);
            });
        }
    }
}
