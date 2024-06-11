<?php

namespace Laravel\VaporCli;

use Illuminate\Support\Arr;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use SplFileObject;

class VaporIgnore
{
    public static function get(): LazyCollection
    {
        $path = getcwd().'/.vaporignore';

        $baseDir = dirname($path);

        return static::getLines($path)->map(function ($line) use ($baseDir) {
            return static::parseLine($baseDir, trim($line));
        })->flatten()->filter();
    }

    protected static function getLines($path): LazyCollection
    {
        return LazyCollection::make(function () use ($path) {
            $file = new SplFileObject($path);

            $file->setFlags(
                SplFileObject::SKIP_EMPTY
                | SplFileObject::READ_AHEAD
                | SplFileObject::DROP_NEW_LINE
            );

            while (! $file->eof()) {
                yield $file->fgets();
            }
        });
    }

    protected static function parseLine($baseDir, $line): false|array|null
    {
        $line = trim($line);

        return Arr::map(match ($line) {
            '', '#' => [], // ignore empty lines and comments
            default => glob("$baseDir/$line")
        }, static function ($line) use ($baseDir) {
            return Str::of($line)
                      ->after($baseDir)
                      ->trim('/')
                      ->pipe(function ($line) {
                          return '/^'.preg_quote($line, '/').'/';
                      })
                      ->toString();
        });
    }
}

