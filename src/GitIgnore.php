<?php

namespace Laravel\VaporCli;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class GitIgnore
{
    /**
     * Add the paths to the Git "ignore" file.
     *
     * @param  array|string  $path
     * @return void
     */
    public static function add(array $paths)
    {
        $paths = Arr::wrap($paths);

        if (! file_exists(getcwd().'/.gitignore')) {
            return;
        }

        $contents = file_get_contents(getcwd().'/.gitignore');

        foreach ($paths as $path) {
            if (! Str::contains($contents, $path.PHP_EOL)) {
                $contents .= $path.PHP_EOL;
            }
        }

        file_put_contents(getcwd().'/.gitignore', $contents);
    }
}
