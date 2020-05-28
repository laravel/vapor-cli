<?php

namespace Laravel\VaporCli\BuildProcess;

use Illuminate\Support\Str;
use Laravel\VaporCli\AssetFiles;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Path;
use Laravel\VaporCli\RewriteAssetUrls;

class ProcessAssets
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
        $this->path = Path::current();
        $this->vaporPath = Path::vapor();
        $this->buildPath = Path::build();
    }

    /**
     * Execute the build process step.
     *
     * @return void
     */
    public function __invoke()
    {
        Helpers::step('<options=bold>Processing Assets</>');

        foreach (AssetFiles::get($this->appPath.'/public') as $file) {
            if (! Str::endsWith($file->getRealPath(), '.css')) {
                continue;
            }

            file_put_contents(
                $file->getRealPath(),
                RewriteAssetUrls::inCssString(file_get_contents($file->getRealPath()), $this->assetUrl)
            );
        }
    }
}
