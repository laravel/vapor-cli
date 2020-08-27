<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class BalancerCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('balancer')
            ->addArgument('name', InputArgument::OPTIONAL, 'The name of the load balancer')
            ->setDescription('Create a new load balancer');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $networkId = $this->determineNetwork(
            'Which network should the load balancer be placed in?'
        );

        if (is_null($networkId)) {
            Helpers::abort('Unable to find a network with that name / ID.');
        }

        $response = $this->vapor->createBalancer(
            $networkId,
            $this->argument('name')
        );

        Helpers::info('Load balancer creation initiated successfully.');
        Helpers::line();
        Helpers::line('Load balancers may take several minutes to finish provisioning.');
    }
}
