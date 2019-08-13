<?php

namespace Laravel\VaporCli\Commands;

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

        Helpers::line('<info>Deployment Hook Command:</info> '.$hook['command']);
        Helpers::line('<info>Deployment Hook Executed At:</info> '.$hook['created_at'].' ('.Helpers::time_ago($hook['created_at']).')');

        Helpers::line();

        isset($hook['log']) && ! empty($hook['log'])
                    ? Helpers::write(base64_decode($hook['log']))
                    : Helpers::line('No log information is available for this deployment hook.');
    }
}
