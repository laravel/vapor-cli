<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;

class JumpListCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('jump:list')
            ->setDescription('List the jumpboxes that belong to the current team');
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
            'ID', 'Network', 'Name', 'User', 'Endpoint', 'Status',
        ], collect($this->vapor->jumpBoxes())->map(function ($jumpBox) {
            return [
                $jumpBox['id'],
                $jumpBox['network']['name'],
                $jumpBox['name'],
                'ec2-user',
                $jumpBox['endpoint'] ?: '-',
                ucfirst($jumpBox['status']),
            ];
        })->all());
    }
}
