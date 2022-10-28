<?php

namespace Laravel\VaporCli\BuildProcess;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;

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
        Helpers::step('<options=bold>Injecting Serverless Handlers</>');

        if (! is_dir($this->appPath.'/vendor/laravel/vapor-core')) {
            Helpers::abort('Unable to find laravel/vapor-core installation.');
        }

        $stubPath = $this->appPath.'/vendor/laravel/vapor-core/stubs';

        if (Manifest::shouldSeparateVendor($this->environment)) {
            $this->copyMissing($stubPath.'/runtime-with-vendor-download.php', $this->appPath.'/runtime.php');
        } else {
            $this->copyMissing($stubPath.'/runtime.php', $this->appPath.'/runtime.php');
        }

        $this->copyMissing($stubPath.'/cliRuntime.php', $this->appPath.'/cliRuntime.php');
        $this->copyMissing($stubPath.'/fpmRuntime.php', $this->appPath.'/fpmRuntime.php');
        $this->copyMissing($stubPath.'/httpRuntime.php', $this->appPath.'/httpRuntime.php');
        $this->copyMissing($stubPath.'/httpHandler.php', $this->appPath.'/httpHandler.php');

        if (Manifest::shouldSeparateVendor($this->environment)) {
            file_put_contents(
                $this->appPath.'/httpHandler.php',
                $this->configureHttpHandler($this->appPath.'/httpHandler.php')
            );
        }

        if (file_exists($stubPath.'/octaneRuntime.php')) {
            $this->files->copy($stubPath.'/octaneRuntime.php', $this->appPath.'/octaneRuntime.php');
        }
    }

    /**
     * Configure the HTTP handler.
     *
     * @param  string  $file
     * @return string
     */
    protected function configureHttpHandler($file)
    {
        return str_replace(
            "require __DIR__.'/vendor/autoload.php';",
            "require '/tmp/vendor/autoload.php';",
            file_get_contents($file)
        );
    }

    /**
     * Copy a file to a new location if that file does not exist.
     *
     * @param  string  $from
     * @param  string  $to
     * @return void
     */
    protected function copyMissing($from, $to)
    {
        if (! $this->files->exists($to)) {
            $this->files->copy($from, $to);
        }
    }
}
