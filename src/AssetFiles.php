<?php

namespace Laravel\VaporCli;

use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class AssetFiles
{
    /**
     * Get an asset file Finder instance.
     *
     * @param string     $path
     * @param array|null $publicFiles
     *
     * @return \Symfony\Component\Finder\Finder|array
     */
    public static function get($path, $publicFiles = null)
    {
        $publicFiles = $publicFiles === null ? Manifest::publicFiles() : $publicFiles;

        if (!is_dir($path)) {
            return collect();
        }

        $finder = (new Finder())
                ->in($path)
                ->files()
                ->exclude('storage')
                ->notName('.htaccess')
                ->notName('web.config')
                ->notName('browserconfig.xml')
                ->notName('*.webmanifest')
                ->notName('manifest.json')
                ->notName('mix-manifest.json')
                ->notName('*.php')
                ->ignoreVcs(true)
                ->ignoreDotFiles(false);

        collect($publicFiles)->each(function ($publicFile) use ($finder, $path) {
            [$directory, $filePattern] = static::parsePattern($path, $publicFile);

            $publicFileFinder = (new Finder())
                ->in($directory)
                ->files()
                ->depth('== 0')
                ->ignoreVcs(true)
                ->ignoreDotFiles(false)
                ->name($filePattern);

            collect($publicFileFinder)->values()->map(function ($file) use ($path) {
                return substr($file->getPathname(), strlen($path) + 1);
            })->each(function ($file) use ($finder) {
                $finder->notPath($file);
            });
        });

        return $finder->sortByName();
    }

    /**
     * Get the relative pathnames of all of the asset files in the path.
     *
     * @param string $path
     *
     * @return array
     */
    public static function relativePaths($path)
    {
        $files = collect(iterator_to_array(static::get($path)));

        return $files->map(function ($file) {
            return str_replace('\\', '/', $file->getRelativePathname());
        })->values()->all();
    }

    /**
     * Parse the given ignore pattern into a base directory and file pattern.
     *
     * @param string $path
     * @param string $pattern
     *
     * @return array
     */
    protected static function parsePattern($path, $pattern)
    {
        $filePattern = basename(trim($pattern, '/'));

        return Str::contains(trim($pattern, '/'), '/')
                    ? [dirname($path.'/'.trim($pattern, '/')), $filePattern]
                    : [$path, $filePattern];
    }
}
