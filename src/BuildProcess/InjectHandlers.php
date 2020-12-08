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
            $this->files->copy($stubPath.'/runtime-with-vendor-download.php', $this->appPath.'/runtime.php');
        } else {
            $this->files->copy($stubPath.'/runtime.php', $this->appPath.'/runtime.php');
        }

        $this->files->copy($stubPath.'/cliRuntime.php', $this->appPath.'/cliRuntime.php');
        $this->files->copy($stubPath.'/fpmRuntime.php', $this->appPath.'/fpmRuntime.php');
        $this->files->copy($stubPath.'/httpRuntime.php', $this->appPath.'/httpRuntime.php');
        $this->files->copy($stubPath.'/httpHandler.php', $this->appPath.'/httpHandler.php');

        if (Manifest::shouldSeparateVendor($this->environment)) {
            file_put_contents(
                $this->appPath.'/httpHandler.php',
                $this->configureHttpHandler($this->appPath.'/httpHandler.php')
            );
        }
    }

    /**
     * Configure the HTTP handler.
     *
     * @param string $file
     *
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
}
