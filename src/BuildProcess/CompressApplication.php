<?php

namespace Laravel\VaporCli\BuildProcess;

use Laravel\VaporCli\BuiltApplicationFiles;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Process\Process;
use ZipArchive;

class CompressApplication
{
    use ParticipatesInBuildProcess;

    /**
     * Execute the build process step.
     *
     * @return void
     */
    public function __invoke()
    {
        if (Manifest::usesContainerImage($this->environment)) {
            return;
        }

        Helpers::step('<options=bold>Compressing Application</>');

        if (PHP_OS == 'Darwin') {
            $this->compressApplicationOnMac();

            return $this->ensureArchiveIsWithinSizeLimits();
        }

        $archive = new ZipArchive();

        $archive->open($this->buildPath.'/app.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach (BuiltApplicationFiles::get($this->appPath) as $file) {
            $relativePathName = str_replace('\\', '/', $file->getRelativePathname());

            $archive->addFile($file->getRealPath(), $relativePathName);

            $archive->setExternalAttributesName(
                $relativePathName,
                ZipArchive::OPSYS_UNIX,
                ($this->getPermissions($file) & 0xFFFF) << 16
            );
        }

        $archive->close();

        $this->ensureArchiveIsWithinSizeLimits();
    }

    /**
     * Utilize the "zip" utility to compress the application.
     *
     * @return void
     */
    protected function compressApplicationOnMac()
    {
        (new Process(['zip', '-r', $this->buildPath.'/app.zip', '.'], $this->appPath))->mustRun();
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

    /**
     * Ensure the application archive is within supported size limits.
     *
     * @return void
     */
    protected function ensureArchiveIsWithinSizeLimits()
    {
        $size = ceil($this->getDirectorySize($this->buildPath.'/app') / 1048576);

        if ($size > 250) {
            Helpers::line();
            Helpers::abort('Application is greater than 250MB. Your application is '.$size.'MB.');
        }
    }

    /**
     * Get the size of the given directory.
     *
     * @param  string  $path
     * @return int
     */
    protected function getDirectorySize($path)
    {
        $size = 0;

        foreach (glob(rtrim($path, '/').'/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : $this->getDirectorySize($each);
        }

        return $size;
    }
}
