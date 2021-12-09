<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class NetworkCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('network')
            ->addArgument('network', InputArgument::REQUIRED, 'The network name')
            ->setDescription('Create a new network');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $this->vapor->createNetwork(
            $this->determineProvider('Which cloud provider should the network belong to?'),
            $this->argument('network'),
            $this->determineRegion('Which region should the network be placed in?'),
            $withInternetAccess = false
        );

        Helpers::info('Network created successfully.');
        Helpers::line();
        Helpers::line('Networks may take several minutes to finish provisioning.');
    }
}
