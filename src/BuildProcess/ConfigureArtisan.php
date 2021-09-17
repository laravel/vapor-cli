<?php

namespace Laravel\VaporCli\BuildProcess;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;

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
        Helpers::step('<options=bold>Configuring Artisan</>');

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
            [
                '<?php',
                "\$app = require_once __DIR__.'/bootstrap/app.php';",
                "require __DIR__.'/vendor/autoload.php';",
            ],
            [
                '<?php'.PHP_EOL."ini_set('display_errors', '1');".PHP_EOL.'error_reporting(E_ALL);'.PHP_EOL,
                "\$app = require_once __DIR__.'/bootstrap/app.php';".PHP_EOL.'$app->useStoragePath(Laravel\Vapor\Runtime\StorageDirectories::PATH);'.PHP_EOL,
                Manifest::shouldSeparateVendor($this->environment) ? "require '/tmp/vendor/autoload.php';".PHP_EOL : "require __DIR__.'/vendor/autoload.php';".PHP_EOL,
            ],
            file_get_contents($file)
        );
    }
}
