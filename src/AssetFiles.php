<?php

namespace Laravel\VaporCli;

use Symfony\Component\Finder\Finder;

class AssetFiles
{
    /**
     * Get an asset file Finder instance.
     *
     * @param  string  $path
     * @return \Symfony\Component\Finder\Finder|array
     */
    public static function get($path)
    {
        if (! is_dir($path)) {
            return collect();
        }

        return (new Finder())
                ->in($path)
                ->files()
                ->exclude('storage')
                ->notName('.htaccess')
                ->notName('web.config')
                ->notName('browserconfig.xml')
                ->notName('*.webmanifest')
                ->notName('*manifest.json')
                ->notName('*.php')
                ->ignoreVcs(true)
                ->ignoreDotFiles(! Manifest::dotFilesAsAssets())
                ->sortByName();
    }

    /**
     * Get the relative pathnames of all of the asset files in the path.
     *
     * @param  string  $path
     * @return array
     */
    public static function relativePaths($path)
    {
        $files = collect(iterator_to_array(static::get($path)));

        return $files->map(function ($file) {
            return str_replace('\\', '/', $file->getRelativePathname());
        })->values()->all();
    }
}
