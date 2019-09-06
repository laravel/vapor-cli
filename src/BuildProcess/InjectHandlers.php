<?php

namespace Laravel\VaporCli\BuildProcess;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
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

        if (Manifest::shouldSeparateVendor()) {
            file_put_contents(
                $this->appPath.'/httpHandler.php',
                $this->configureHttpHandler($this->appPath.'/httpHandler.php')
            );
        }

        file_put_contents(
            $this->appPath.'/runtime.php',
            $this->configureRuntime($this->appPath.'/runtime.php')
        );
    }

    /**
     * Configure the runtime.php file.
     *
     * @param  string  $file
     * @return string
     */
    protected function configureRuntime($file)
    {
        if (Manifest::shouldSeparateVendor()) {
            return str_replace(
                "require \$appRoot.'/vendor/autoload.php';".PHP_EOL,
                "require '/tmp/vendor/autoload.php';".PHP_EOL,
                file_get_contents($file)
            );
        } else {
            $lines = explode(PHP_EOL, file_get_contents($file));
            $startIndex = array_search('/* START_VENDOR_DOWNLOADING */', $lines);
            $endIndex = array_search('/* END_VENDOR_DOWNLOADING */', $lines);

            array_splice($lines, $startIndex, $endIndex - $startIndex + 2);

            return implode(PHP_EOL, $lines);
        }
    }

    /**
     * Configure the http hanlder file.
     *
     * @param  string  $file
     * @return string
     */
    protected function configureHttpHandler($file)
    {
        return str_replace(
            "require __DIR__.'/vendor/autoload.php';".PHP_EOL,
            "require '/tmp/vendor/autoload.php';".PHP_EOL,
            file_get_contents($file)
        );
    }
}
