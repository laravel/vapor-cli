<?php

namespace Laravel\VaporCli\BuildProcess;

use Laravel\VaporCli\ApplicationFiles;
use Laravel\VaporCli\Helpers;
use SplFileInfo;

class CopyApplicationToBuildPath
{
    use ParticipatesInBuildProcess;

    /**
     * Execute the build process step.
     *
     * @return void
     */
    public function __invoke()
    {
        Helpers::step('<options=bold>Copying Application Files</>');

        $this->ensureBuildDirectoryExists();

        foreach (ApplicationFiles::get($this->path) as $file) {
            if ($file->isLink()) {
                continue;
            }

            $file->isDir()
                ? $this->createDirectoryForCopy($file)
                : $this->createFileForCopy($file);
        }

        $this->flushCacheFiles();
        $this->flushStorageDirectories();
    }

    /**
     * Create a directory for the application copy operation.
     *
     * @param \SplFileInfo $file
     *
     * @return void
     */
    protected function createDirectoryForCopy(SplFileInfo $file)
    {
        $this->files->makeDirectory($this->appPath.'/'.$file->getRelativePathname());
    }

    /**
     * Create a file for the application copy operation.
     *
     * @param \SplFileInfo $file
     *
     * @return void
     */
    protected function createFileForCopy(SplFileInfo $file)
    {
        $this->files->copy(
            $file->getRealPath(),
            $this->appPath.'/'.$file->getRelativePathname()
        );

        $this->files->chmod(
            $this->appPath.'/'.$file->getRelativePathname(),
            fileperms($file->getRealPath())
        );
    }

    /**
     * Ensure that the build directory exists.
     *
     * @return void
     */
    protected function ensureBuildDirectoryExists()
    {
        if ($this->files->isDirectory($this->vaporPath.'/build')) {
            $this->files->deleteDirectory($this->vaporPath.'/build');
        }

        $this->files->makeDirectory(
            $this->vaporPath.'/build/app',
            0755,
            true
        );
    }

    /**
     * Flush the relevant cache files from the application.
     *
     * @return void
     */
    protected function flushCacheFiles()
    {
        $this->files->delete($this->appPath.'/bootstrap/cache/config.php');
    }

    /**
     * Flush the storage directories that are not needed.
     *
     * @return void
     */
    protected function flushStorageDirectories()
    {
        $path = $this->buildPath.'/app';

        $this->files->deleteDirectory($path.'/storage/app', true);
        $this->files->deleteDirectory($path.'/storage/logs', true);
        $this->files->deleteDirectory($path.'/storage/framework/cache', true);
        $this->files->deleteDirectory($path.'/storage/framework/views', true);
        $this->files->deleteDirectory($path.'/storage/framework/testing', true);
        $this->files->deleteDirectory($path.'/storage/framework/sessions', true);
    }
}
