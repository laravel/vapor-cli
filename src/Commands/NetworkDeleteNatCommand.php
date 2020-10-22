<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class NetworkDeleteNatCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('network:delete-nat')
            ->addArgument('network', InputArgument::REQUIRED, 'The network name / ID')
            ->setDescription('Remove the NAT Gateway and outgoing Internet access from the given network');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        if (! is_numeric($networkId = $this->argument('network'))) {
            $networkId = $this->findIdByName($this->vapor->networks(), $networkId);
        }

        $this->vapor->removeNetworkInternetAccess($networkId);

        Helpers::info('Network updated successfully.');
        Helpers::line();
        Helpers::line('Network updates may take several minutes to finish provisioning.');
    }
}
