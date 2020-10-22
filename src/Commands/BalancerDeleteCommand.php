<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class BalancerDeleteCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('balancer:delete')
            ->addArgument('balancer', InputArgument::REQUIRED, 'The load balancer name / ID')
            ->setDescription('Delete a load balancer');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        if (! Helpers::confirm('Are you sure you want to delete this load balancer', false)) {
            Helpers::abort('Action cancelled.');
        }

        [$balancerId, $balancerName] = $this->determineBalancer();

        if (is_null($balancerId)) {
            Helpers::abort('Unable to find a load balancer with that name / ID.');
        }

        $this->vapor->deleteBalancer($balancerId);

        Helpers::info('Load balancer deleted successfully.');
    }

    /**
     * Determine the load balancer that should be deleted.
     *
     * @return array
     */
    protected function determineBalancer()
    {
        $balancers = $this->vapor->balancers();

        if (! is_numeric($balancerId = $this->argument('balancer'))) {
            $balancerName = $balancerId;

            $balancerId = $this->findIdByName($balancers, $balancerId);
        } else {
            $balancerName = collect($balancers)->first(function ($balancer) use ($balancerId) {
                return $balancer['id'] == $balancerId;
            })['name'];
        }

        return [$balancerId, $balancerName];
    }
}
