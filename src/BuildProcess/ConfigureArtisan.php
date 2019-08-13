<?php

namespace Laravel\VaporCli\BuildProcess;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Finder\Finder;

class ConfigureArtisan
{
    use ParticipatesInBuildProcess;

    /**
     * Execute the build process step.
     *
     * @return void
     */
    public function __invoke()
    {
        Helpers::step('<bright>Configuring Artisan</>');

        file_put_contents(
            $this->appPath.'/artisan',
            $this->configure($this->appPath.'/artisan')
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
            "\$app = require_once __DIR__.'/bootstrap/app.php';".PHP_EOL,
            "\$app = require_once __DIR__.'/bootstrap/app.php';".PHP_EOL.'$app->useStoragePath(Laravel\Vapor\Runtime\StorageDirectories::PATH);'.PHP_EOL,
            file_get_contents($file)
        );
    }
}
