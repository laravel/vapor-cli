<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Laravel\VaporCli\Clipboard;
use Symfony\Component\Console\Input\InputArgument;

class DownCommand extends Command
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
            ->setName('down')
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment name', 'staging')
            ->setDescription('Place an environment in maintenance mode');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        Helpers::step('<bright>Initiating Maintenance Mode Deployment</bright>');

        $deployment = $this->displayDeploymentProgress(
            $this->vapor->enableMaintenanceMode(Manifest::id(), $this->argument('environment'))
        );

        Clipboard::deployment($deployment);
    }
}
