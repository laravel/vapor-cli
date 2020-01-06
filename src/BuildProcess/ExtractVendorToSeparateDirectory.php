<?php

namespace Laravel\VaporCli\BuildProcess;

use Illuminate\Filesystem\Filesystem;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;

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
        if (! Manifest::shouldSeparateVendor()) {
            return;
        }

        Helpers::step('<bright>Extracting Vendor Files</>');

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
