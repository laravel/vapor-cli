<?php

namespace Laravel\VaporCli;

use Symfony\Component\Process\Process;

class Clipboard
{
    /**
     * Store the deployment environment's vanity URL in the clipboard.
     *
     * @param array $deployment
     *
     * @return void
     */
    public static function deployment(array $deployment)
    {
        static::put("https://{$deployment['environment']['vanity_domain']}");
    }

    /**
     * Add the given string to the clipboard.
     *
     * @param string $string
     *
     * @return void
     */
    public static function put($string)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            Process::fromShellCommandline('echo '.$string.' | clip')->run();
        } else {
            Process::fromShellCommandline('echo "'.$string.'" | pbcopy')->run();
        }
    }
}
