<?php

namespace Laravel\VaporCli\Commands;

use Illuminate\Support\Str;
use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class HookLogCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('hook:log')
            ->addArgument('hook', InputArgument::OPTIONAL, 'The deployment hook ID')
            ->setDescription('Retrieve the log messages for a deployment hook');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $hook = $this->vapor->deploymentHook($this->argument('hook')
                        ?? $this->vapor->latestFailedDeploymentHook()['id']
                        ?? null);

        Helpers::line('<info>Hook:</info> '.$hook['command']);
        Helpers::line('<info>Executed At:</info> '.$hook['created_at'].' ('.Helpers::time_ago($hook['created_at']).')');
        Helpers::line('<info>Logs:</info>');

        isset($hook['log']) && ! empty($hook['log'])
                    ? static::writeLog($hook['log'])
                    : Helpers::line('No log information is available for this deployment hook.');
    }

    /**
     * Write the log to the console.
     *
     * @param  string  $log
     * @return void
     */
    public static function writeLog($log)
    {
        $log = base64_decode($log);

        $lines = explode(PHP_EOL, $log);

        collect($lines)->filter(function ($line) {
            return ! Str::startsWith($line, ['START', 'END', 'REPORT']);
        })->each(function ($line) {
            if ($json = json_decode($line, true)) {
                $line = json_encode($json, JSON_PRETTY_PRINT).PHP_EOL;
            }

            Helpers::write($line);
        });

        Helpers::line();
    }
}
