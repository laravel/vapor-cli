<?php

namespace Laravel\VaporCli\BuildProcess;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\AssetFiles;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class ExtractAssetsToSeparateDirectory
{
    use ParticipatesInBuildProcess;

    /**
     * Execute the build process step.
     *
     * @return void
     */
    public function __invoke()
    {
        Helpers::step('<bright>Extracting Assets</>');

        $this->ensureAssetDirectoryExists();

        (new Filesystem)->copyDirectory(
            $this->appPath.'/public',
            $this->buildPath.'/assets'
        );

        foreach (AssetFiles::get($this->appPath.'/public') as $file) {
            @unlink($file->getRealPath());
        }
    }

    /**
     * Ensure that the asset directory exists.
     *
     * @return void
     */
    protected function ensureAssetDirectoryExists()
    {
        if ($this->files->isDirectory($this->buildPath.'/assets')) {
            $this->files->deleteDirectory($this->buildPath.'/assets');
        }

        $this->files->makeDirectory(
            $this->buildPath.'/assets', 0755, true
        );
    }
}
