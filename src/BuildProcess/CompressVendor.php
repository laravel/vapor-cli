<?php

namespace Laravel\VaporCli\BuildProcess;

use Laravel\VaporCli\BuiltApplicationFiles;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Process\Process;
use ZipArchive;

class CompressVendor
{
    use ParticipatesInBuildProcess;

    /**
     * Execute the build process step.
     *
     * @return void
     */
    public function __invoke()
    {
        if (! Manifest::shouldSeparateVendor($this->environment)) {
            return;
        }

        Helpers::step('<options=bold>Compressing Vendor Directory</>');

        if (PHP_OS == 'Darwin') {
            return $this->compressOnMac();
        }

        $archive = new ZipArchive();

        $archive->open($this->buildPath.'/vendor.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach (BuiltApplicationFiles::get($this->vendorPath) as $file) {
            $relativePathName = str_replace('\\', '/', $file->getRelativePathname());

            $archive->addFile($file->getRealPath(), $relativePathName);

            $archive->setExternalAttributesName(
                $relativePathName,
                ZipArchive::OPSYS_UNIX,
                ($this->getPermissions($file) & 0xFFFF) << 16
            );
        }

        $archive->close();
    }

    /**
     * Utilize the "zip" utility to compress the vendor directory.
     *
     * @return void
     */
    protected function compressOnMac()
    {
        (new Process(['zip', '-r', $this->buildPath.'/vendor.zip', '.'], $this->vendorPath))->mustRun();
    }

    /**
     * Get the proper file permissions for the file.
     *
     * @param  \SplFileInfo  $file
     * @return int
     */
    protected function getPermissions($file)
    {
        return $file->isDir() || $file->getFilename() == 'php'
                        ? 33133  // '-r-xr-xr-x'
                        : fileperms($file->getRealPath());
    }
}
