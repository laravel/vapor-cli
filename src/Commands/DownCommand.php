<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment name')
            ->addOption('secret', null, InputOption::VALUE_REQUIRED, 'The secret phrase that may be used to bypass maintenance mode')
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

        Helpers::step('<options=bold>Initiating Maintenance Mode Deployment</>');

        $deployment = $this->displayDeploymentProgress(
            $this->vapor->enableMaintenanceMode(
                Manifest::id(),
                $this->argument('environment'),
                $this->option('secret')
            )
        );
    }
}
