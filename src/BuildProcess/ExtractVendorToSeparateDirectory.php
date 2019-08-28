<?php

namespace Laravel\VaporCli\BuildProcess;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\AssetFiles;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class ExtractVendorToSeparateDirectory
{
    use ParticipatesInBuildProcess;

    /**
     * Execute the build process step.
     *
     * @return void
     */
    public function __invoke()
    {
        Helpers::step('<bright>Extracting Vendor</>');

        $this->ensureVendorDirectoryExists();

        (new Filesystem)->copyDirectory(
            $this->appPath.'/vendor',
            $this->buildPath.'/vendor'
        );

        $this->files->deleteDirectory($this->appPath.'/vendor');
    }

    /**
     * Ensure that the vendor directory exists.
     *
     * @return void
     */
    protected function ensureVendorDirectoryExists()
    {
        if ($this->files->isDirectory($this->buildPath.'/vendor')) {
            $this->files->deleteDirectory($this->buildPath.'/vendor');
        }

        $this->files->makeDirectory(
            $this->buildPath.'/vendor', 0755, true
        );
    }
}
