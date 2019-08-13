<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Laravel\VaporCli\Clipboard;
use Symfony\Component\Console\Input\InputArgument;

class RedeployCommand extends Command
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
            ->setName('redeploy')
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment name', 'staging')
            ->setDescription("Redeploy an environment's latest deployment");
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        Helpers::step('<bright>Initiating Redeployment</bright>');

        $deployment = $this->displayDeploymentProgress(
            $this->vapor->redeploy(Manifest::id(), $this->argument('environment'))
        );

        Clipboard::deployment($deployment);
    }
}
