<?php

namespace Laravel\VaporCli;

use Symfony\Component\Finder\Finder;

class BuiltApplicationFiles
{
    /**
     * Get a built application Finder instance.
     *
     * @param  string  $path
     * @return \Symfony\Component\Finder\Finder
     */
    public static function get($path)
    {
        return (new Finder())
                ->in($path)
                ->files()
                ->ignoreVcs(true)
                ->ignoreDotFiles(false);
    }
}
