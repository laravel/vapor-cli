<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class NetworkShowCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('network:show')
            ->addArgument('network', InputArgument::REQUIRED, 'The network name / ID')
            ->setDescription('Display the details of a network');
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

        if (is_null($networkId)) {
            Helpers::abort('Unable to find a network with that name / ID.');
        }

        $network = $this->vapor->network($networkId);

        $this->table([
            'ID', 'Name', 'Provider', 'Region', 'VPC', 'Status',
        ], collect([$network])->map(function ($network) {
            return [
                $network['id'],
                $network['name'],
                $network['cloud_provider']['name'],
                $network['region'],
                $network['vpc_id'] ?: '-',
                ucfirst($network['status']),
            ];
        })->all());

        if ($network['status'] === 'available') {
            $this->displayNetworkDetails($network);
        }
    }

    /**
     * Display the network's details.
     *
     * @param  array  $network
     * @return void
     */
    protected function displayNetworkDetails(array $network)
    {
        Helpers::line();
        Helpers::line('<info>Default Security Group:</info> '.$network['default_security_group_id']);
        Helpers::line();
        Helpers::line('<info>Public Subnets:</info> '.implode(', ', $network['public_subnets']));
        Helpers::line();
        Helpers::info('Private Subnets:');
        Helpers::line();
        Helpers::line(implode(PHP_EOL, $network['private_subnets']));
    }
}
