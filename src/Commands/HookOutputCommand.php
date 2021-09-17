<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class HookOutputCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('hook:output')
            ->addArgument('hook', InputArgument::OPTIONAL, 'The deployment hook ID')
            ->setDescription('Retrieve the output for a deployment hook');
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
        Helpers::line('<info>Output:</info>');

        isset($hook['output']) && ! empty($hook['output'])
                    ? static::writeOutput($hook['output'])
                    : Helpers::line('No output information is available for this deployment hook.');

        Helpers::line();
    }

    /**
     * Write the output to the console.
     *
     * @param  string  $output
     * @return void
     */
    public static function writeOutput($output)
    {
        $output = base64_decode($output);

        if ($json = json_decode($output, true)) {
            $output = $json['output'];
        }

        Helpers::write($output);
    }
}
