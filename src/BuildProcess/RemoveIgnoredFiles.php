<?php

namespace Laravel\VaporCli\BuildProcess;

use Illuminate\Support\Str;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Laravel\VaporCli\Path;
use Symfony\Component\Finder\Finder;

class RemoveIgnoredFiles
{
    use ParticipatesInBuildProcess;

    /**
     * Execute the build process step.
     *
     * @return void
     */
    public function __invoke()
    {
        Helpers::step('<options=bold>Removing Ignored Files</>');

        $this->removeDefaultIgnoredFiles();
        $this->removeDefaultIgnoredDirectories();
        $this->removeSymfonyTests();

        $this->removeUserIgnoredFiles();
    }

    /**
     * Remove the files that are ignored by default.
     *
     * @return void
     */
    protected function removeDefaultIgnoredFiles()
    {
        $defaultFiles = [
            Path::app().'/.env',
            Path::app().'/.env.example',
            Path::app().'/.phpunit.result.cache',
            Path::app().'/package-lock.json',
            Path::app().'/phpunit.xml',
            Path::app().'/readme.md',
            Path::app().'/server.php',
            Path::app().'/storage/oauth-private.key',
            Path::app().'/storage/oauth-public.key',
            Path::app().'/webpack.mix.js',
            Path::app().'/yarn.lock',
        ];

        foreach ($defaultFiles as $file) {
            if ($this->files->exists($file)) {
                $this->files->delete($file);
            }
        }

        if (! $this->files->exists(Path::app().'/database')) {
            return;
        }

        $files = (new Finder())
            ->in(Path::app().'/database')
            ->depth('== 0')
            ->name('*.sqlite');

        foreach ($files as $file) {
            $this->files->delete($file->getRealPath());
        }
    }

    /**
     * Remove the directories that are ignored by default.
     *
     * @return void
     */
    protected function removeDefaultIgnoredDirectories()
    {
        $defaultDirectories = [
            // Path::app().'/database/factories',
            // Path::app().'/database/seeds',
            Path::app().'/resources/css',
            Path::app().'/resources/js',
            Path::app().'/resources/less',
            Path::app().'/resources/sass',
            Path::app().'/resources/scss',
            Path::app().'/storage/cache',
            Path::app().'/storage/debugbar',
            Path::app().'/storage/logs',
            Path::app().'/storage/sessions',
            Path::app().'/storage/testing',
            Path::app().'/vendor/aws/aws-sdk-php/.changes',
            Path::app().'/vendor/monolog/monolog/tests',
            Path::app().'/vendor/swiftmailer/swiftmailer/doc',
            Path::app().'/vendor/swiftmailer/swiftmailer/tests',
        ];

        foreach ($defaultDirectories as $directory) {
            if ($this->files->isDirectory($directory)) {
                $this->files->deleteDirectory($directory, $preserve = true);
            }
        }
    }

    /**
     * Remove the tests from the Symfony components.
     *
     * @return void
     */
    protected function removeSymfonyTests()
    {
        foreach ($this->files->directories(Path::app().'/vendor/symfony') as $component) {
            if ($this->files->isDirectory($component.'/Tests')) {
                $this->files->deleteDirectory($component.'/Tests', $preserve = false);
            }
        }
    }

    /**
     * Remove the user ignored files specified in the project manifest.
     *
     * @return void
     */
    protected function removeUserIgnoredFiles()
    {
        foreach (Manifest::ignoredFiles() as $pattern) {
            [$directory, $filePattern] = $this->parsePattern($pattern);

            if ($this->files->exists($directory.'/'.$filePattern) && $this->files->isDirectory($directory.'/'.$filePattern)) {
                Helpers::step('<comment>Removing Ignored Directory:</comment> '.$filePattern.'/');

                $this->files->deleteDirectory($directory.'/'.$filePattern, $preserve = false);
            } else {
                $files = (new Finder())
                            ->in($directory)
                            ->depth('== 0')
                            ->ignoreDotFiles(false)
                            ->name($filePattern);

                foreach ($files as $file) {
                    Helpers::step('<comment>Removing Ignored File:</comment> '.str_replace(Path::app().'/', '', $file->getRealPath()));

                    $this->files->delete($file->getRealPath());
                }
            }
        }
    }

    /**
     * Parse the given ignore pattern into a base directory and file pattern.
     *
     * @param  string  $pattern
     * @return array
     */
    protected function parsePattern($pattern)
    {
        $filePattern = basename(trim($pattern, '/'));

        return Str::contains(trim($pattern, '/'), '/')
                    ? [dirname(Path::app().'/'.trim($pattern, '/')), $filePattern]
                    : [Path::app(), $filePattern];
    }
}
