<?php

namespace Laravel\VaporCli\BuildProcess;

use Laravel\VaporCli\BuiltApplicationFiles;
use Laravel\VaporCli\Helpers;
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
        Helpers::step('<bright>Compressing Application</>');

        if (PHP_OS == 'Darwin') {
            $this->compressApplicationOnMac();

            return $this->ensureArchiveIsWithinSizeLimits();
        }

        $archive = new ZipArchive;

        $archive->open($this->buildPath.'/app.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach (BuiltApplicationFiles::get($this->appPath) as $file) {
            $relativePathName = str_replace("\\", "/", $file->getRelativePathname());

            $archive->addFile($file->getRealPath(), $relativePathName);

            $archive->setExternalAttributesName(
                $relativePathName,
                ZipArchive::OPSYS_UNIX,
                ($this->getPermissions($file) & 0xffff) << 16
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
                        : 33060; // '-r--r--r--'
    }

    /**
     * Ensure the application archive is within supported size limits.
     *
     * @return void
     */
    protected function ensureArchiveIsWithinSizeLimits()
    {
        $size = round(filesize($this->buildPath.'/app.zip') / 1048576, 1);

        if ($size > 45) {
            Helpers::line();
            Helpers::abort('Compressed application is greater than 45MB. Your application is '.$size.'MB.');
        }
    }
}
