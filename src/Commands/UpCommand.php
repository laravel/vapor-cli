<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
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
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment name')
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

        Helpers::step('<options=bold>Initiating Active Mode Deployment</>');

        $deployment = $this->displayDeploymentProgress(
            $this->vapor->disableMaintenanceMode(Manifest::id(), $this->argument('environment'))
        );
    }
}
