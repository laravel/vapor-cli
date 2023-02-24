<?php

namespace Laravel\VaporCli;

use Symfony\Component\Process\Process;

class Git
{
    /**
     * Get the Git current commit hash for the project.
     *
     * @return string|null
     */
    public static function hash()
    {
        if (static::isClean()) {
            return static::command("git log --pretty=format:'%H' -n 1");
        }
    }

    /**
     * Get the Git current commit message for the project.
     *
     * @return string|null
     */
    public static function message()
    {
        if (static::isClean()) {
            return static::command("git log --pretty=format:'%s' -n 1");
        }
    }

    /**
     * Determine if the Git status of the project is clean.
     *
     * @return bool
     */
    public static function isClean()
    {
        return empty(static::command('git status -s'));
    }

    /**
     * Run the given command and return the trimmed output.
     *
     * @param  string  $command
     * @return bool
     */
    protected static function command($command)
    {
        $process = Process::fromShellCommandline($command, Path::current());

        $process->run();

        return trim($process->getOutput());
    }
}
