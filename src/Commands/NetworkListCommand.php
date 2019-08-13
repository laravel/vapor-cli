<?php

namespace Laravel\VaporCli\Commands;

use Illuminate\Support\Str;
use Laravel\VaporCli\Helpers;

class NetworkListCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('network:list')
            ->setDescription('List the networks that belong to the current team');
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
            'ID', 'Provider', 'Name', 'Region', 'VPC', 'NAT Gateway',  'Status',
        ], collect($this->vapor->networks())->map(function ($network) {
            return [
                $network['id'],
                $network['cloud_provider']['name'],
                $network['name'],
                $network['region'],
                $network['vpc_id'] ?: '-',
                $network['has_internet_access'] ? '<info>✔</info>' : '',
                Str::title(str_replace('_', ' ', $network['status'])),
            ];
        })->all());
    }
}
