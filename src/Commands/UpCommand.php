<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Laravel\VaporCli\Clipboard;
use Symfony\Component\Console\Input\InputArgument;

class UpCommand extends Command
{
    use DisplaysDeploymentProgress;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('up')
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment name', 'staging')
            ->setDescription('Remove an environment from maintenance mode');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        Helpers::step('<bright>Initiating Active Mode Deployment</bright>');

        $deployment = $this->displayDeploymentProgress(
            $this->vapor->disableMaintenanceMode(Manifest::id(), $this->argument('environment'))
        );

        Clipboard::deployment($deployment);
    }
}
