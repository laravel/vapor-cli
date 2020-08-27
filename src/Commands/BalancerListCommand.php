<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;

class BalancerListCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('balancer:list')
            ->setDescription('List the load balancers that belong to the current team');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $this->table([
            'ID', 'Network', 'Name', 'Status',
        ], collect($this->vapor->balancers())->map(function ($balancer) {
            return [
                $balancer['id'],
                $balancer['network']['name'],
                $balancer['name'],
                ucfirst($balancer['status']),
            ];
        })->all());
    }
}
