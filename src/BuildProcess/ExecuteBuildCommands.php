<?php

namespace Laravel\VaporCli\BuildProcess;

use Illuminate\Support\Str;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Process\Process;

class ExecuteBuildCommands
{
    use ParticipatesInBuildProcess;

    /**
     * @var array<int, string>
     */
    protected $unsupportedCommands = [
        'clear-compiled',
        'optimize:clear',
        'route:cache',
    ];

    /**
     * Execute the build process step.
     *
     * @return void
     */
    public function __invoke()
    {
        Helpers::step('<options=bold>Executing Build Commands</>');

        foreach ($this->supportedCommands() as $command) {
            Helpers::step('<comment>Running Command</comment>: '.$command);

            $process = Process::fromShellCommandline($command, $this->appPath, ['LARAVEL_VAPOR' => 1], null, null);

            $process->mustRun(function ($type, $line) {
                Helpers::write($line);
            });
        }
    }

    /**
     * Remove unsupported commands from the manifest.
     *
     * @return array<int, string>
     */
    public function supportedCommands()
    {
        return collect(Manifest::buildCommands($this->environment))
            ->filter(function ($command) {
                return ! Str::contains($command, $this->unsupportedCommands);
            })
            ->values()
            ->all();
    }
}
