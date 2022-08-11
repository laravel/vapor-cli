<?php

namespace Laravel\VaporCli\BuildProcess;

use Illuminate\Filesystem\Filesystem;
use Laravel\VaporCli\Path;

class CollectEnvironmentVariables
{
    use ParticipatesInBuildProcess;

    /**
     * The asset base URL.
     */
    protected $assetUrl;

    /**
     * Create a new project builder.
     *
     * @param  string|null  $assetUrl
     * @return void
     */
    public function __construct($assetUrl = null)
    {
        $this->assetUrl = $assetUrl;

        $this->appPath = Path::app();
        $this->files = new Filesystem();
    }

    /**
     * Execute the build process step.
     *
     * @return void
     */
    public function __invoke()
    {
        $environmentVariables = [
            'ASSET_URL' => $this->assetUrl,
            'MIX_URL' => $this->assetUrl,
        ];

        $this->files->put(
            $this->appPath.'/vaporEnvironmentVariables.php',
            '<?php return '.var_export($environmentVariables, true).';'
        );
    }
}
