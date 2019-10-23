<?php

namespace Laravel\VaporCli\BuildProcess;

use Laravel\VaporCli\Path;
use Laravel\VaporCli\Helpers;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class SetBuildEnvironment
{
    use ParticipatesInBuildProcess;

    /**
     * The asset base URL.
     */
    protected $assetUrl;

    /**
     * Create a new project builder.
     *
     * @param  string|null  $environment
     * @param  string|null  $assetUrl
     * @return void
     */
    public function __construct($environment = null, $assetUrl = null)
    {
        $this->assetUrl = $assetUrl;
        $this->environment = $environment;

        $this->appPath = Path::app();
        $this->path = Path::current();
        $this->vaporPath = Path::vapor();
        $this->buildPath = Path::build();

        $this->files = new Filesystem;
    }

    /**
     * Execute the build process step.
     *
     * @return void
     */
    public function __invoke()
    {
        Helpers::step('<bright>Setting Build Environment</>');

        if (! file_exists($envPath = $this->appPath.'/.env.'.$this->environment) && 
            ! file_exists($envPath = $this->appPath.'/.env')) {
            return;
        }

        $this->files->prepend(
            $envPath, 'APP_ENV='.$this->environment.PHP_EOL
        );

        // Mix takes the last environment variable value...
        $this->files->append(
            $envPath, PHP_EOL.'APP_ENV='.$this->environment.PHP_EOL
        );

        $this->files->append(
            $envPath, 'ASSET_URL='.$this->assetUrl.PHP_EOL
        );
    }
}
