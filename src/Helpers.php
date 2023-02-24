<?php

namespace Laravel\VaporCli;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Style\SymfonyStyle;

class Helpers
{
    /**
     * Display a danger message and exit.
     *
     * @param  string  $text
     * @return never
     */
    public static function abort($text)
    {
        static::danger($text);

        exit(1);
    }

    /**
     * Resolve a service from the container.
     *
     * @param  string|null  $name
     * @return mixed
     */
    public static function app($name = null)
    {
        return $name ? Container::getInstance()->make($name) : Container::getInstance();
    }

    /**
     * Ask the user a question.
     *
     * @param  string  $question
     * @param  mixed  $default
     * @return mixed
     */
    public static function ask($question, $default = null)
    {
        $style = new SymfonyStyle(static::app('input'), static::app('output'));

        return $style->ask($question, $default);
    }

    /**
     * Display a comment message.
     *
     * @param  string  $text
     * @return void
     */
    public static function comment($text)
    {
        static::app('output')->writeln('<comment>'.$text.'</comment>');
    }

    /**
     * Get or set configuration values.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public static function config($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $innerKey => $value) {
                Config::set($innerKey, $value);
            }

            return;
        }

        return Config::get($key, $value);
    }

    /**
     * Ask the user a confirmation question.
     *
     * @param  string  $question
     * @param  mixed  $default
     * @return mixed
     */
    public static function confirm($question, $default = true)
    {
        $style = new SymfonyStyle(static::app('input'), static::app('output'));

        return $style->confirm($question, $default);
    }

    /**
     * Display a danger message.
     *
     * @param  string  $text
     * @return void
     */
    public static function danger($text)
    {
        static::app('output')->writeln('<fg=red>'.$text.'</>');
    }

    /**
     * Display a warning message.
     *
     * @param  string  $text
     * @return void
     */
    public static function warn($text)
    {
        static::app('output')->writeln('<fg=yellow>'.$text.'</>');
    }

    /**
     * Ensure that the user has authenticated with Laravel Vapor.
     *
     * @return void
     */
    public static function ensure_api_token_is_available()
    {
        if (isset($_ENV['VAPOR_API_TOKEN']) ||
            getenv('VAPOR_API_TOKEN')) {
            return;
        }

        if (! static::config('token') || ! static::config('team')) {
            throw new Exception("Please authenticate using the 'login' command before proceeding.");
        }
    }

    /**
     * Get a random exclamation.
     *
     * @return string
     */
    public static function exclaim()
    {
        return Arr::random([
            'Amazing',
            'Awesome',
            'Beautiful',
            'Boom',
            'Cool',
            'Done',
            'Got it',
            'Great',
            'Magic',
            'Nice',
            'Sweet',
            'Wonderful',
            'Yes',
        ]);
    }

    /**
     * Get the home directory for the user.
     *
     * @return string
     */
    public static function home()
    {
        return $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'];
    }

    /**
     * Display an informational message.
     *
     * @param  string  $text
     * @return void
     */
    public static function info($text)
    {
        static::app('output')->writeln('<info>'.$text.'</info>');
    }

    /**
     * Get the file size in kilobytes.
     *
     * @param  string  $file
     * @return string
     */
    public static function kilobytes($file)
    {
        return round(filesize($file) / 1024, 2).'KB';
    }

    /**
     * Display a message.
     *
     * @param  string  $text
     * @return void
     */
    public static function line($text = '')
    {
        static::app('output')->writeln($text);
    }

    /**
     * Get the file size in megabytes.
     *
     * @param  string  $file
     * @return string
     */
    public static function megabytes($file)
    {
        return round(filesize($file) / 1024 / 1024, 2).'MB';
    }

    /**
     * Ask the user to select from the given choices.
     *
     * @param  string  $question
     * @param  mixed  $default
     * @return mixed
     */
    public static function menu($title, $choices)
    {
        $style = new SymfonyStyle(static::app('input'), static::app('output'));

        return $style->askQuestion(new KeyChoiceQuestion($title, $choices));
    }

    /**
     * Ask the user a secret question.
     *
     * @param  string  $question
     * @return mixed
     */
    public static function secret($question)
    {
        $style = new SymfonyStyle(static::app('input'), static::app('output'));

        return $style->askHidden($question);
    }

    /**
     * Display a "step" message.
     *
     * @param  string  $text
     * @return void
     */
    public static function step($text)
    {
        static::line('<fg=blue>==></> '.$text);
    }

    /**
     * Format input into a textual table.
     *
     * @param  array  $headers
     * @param  array  $rows
     * @param  string  $style
     * @return void
     */
    public static function table(array $headers, array $rows, $style = 'borderless')
    {
        if (empty($rows)) {
            return;
        }

        $table = new Table(static::app('output'));

        $table->setHeaders($headers)->setRows($rows)->setStyle($style)->render();
    }

    /**
     * Display the date in "humanized" time-ago form.
     *
     * @param  string  $date
     * @return string
     */
    public static function time_ago($date)
    {
        return Carbon::parse($date)->diffForHumans();
    }

    /**
     * Write text to the console.
     *
     * @param  string  $text
     * @return void
     */
    public static function write($text)
    {
        static::app('output')->write($text);
    }
}
