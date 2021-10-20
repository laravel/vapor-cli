<?php

namespace Laravel\VaporCli\BuildProcess;

use Illuminate\Filesystem\Filesystem;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Path;

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

        $this->files = new Filesystem();
    }

    /**
     * Execute the build process step.
     *
     * @return void
     */
    public function __invoke()
    {
        Helpers::step('<options=bold>Setting Build Environment</>');

        if (! file_exists($envPath = $this->appPath.'/.env')) {
            $this->files->put($envPath, '');
        }

        if (file_exists($this->appPath.'/.env.'.$this->environment)) {
            $this->files->copy(
                $this->appPath.'/.env.'.$this->environment,
                $envPath
            );

            $this->files->delete($this->appPath.'/.env.'.$this->environment);
        }

        $this->files->prepend(
            $envPath,
            'APP_ENV='.$this->environment.PHP_EOL
        );

        // Mix takes the last environment variable value...
        $this->files->append(
            $envPath,
            PHP_EOL.'APP_ENV='.$this->environment.PHP_EOL
        );

        $this->files->append(
            $envPath,
            'ASSET_URL='.$this->assetUrl.PHP_EOL
        );

        $this->files->append(
            $envPath,
            'MIX_VAPOR_ASSET_URL='.$this->assetUrl.PHP_EOL
        );
    }
}
