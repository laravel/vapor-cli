<?php

namespace Laravel\VaporCli\Commands;

use Illuminate\Support\Str;
use Laravel\VaporCli\Helpers;

class DatabaseListCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('database:list')
            ->setDescription('List the databases that belong to the current team');
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
            'ID', 'Provider', 'Name', 'Region', 'Type', 'Class', 'Storage', 'Status', 'Proxy',
        ], collect($this->vapor->databases())->map(function ($database) {
            return [
                $database['id'],
                $database['cloud_provider']['name'],
                $database['name'],
                $database['region'],
                in_array($database['type'], ['aurora-serverless', 'aurora-serverless-v2', 'aurora-serverless-pgsql', 'aurora-serverless-v2-pgsql'])
                    ? 'Serverless'
                    : 'Fixed Size',
                $database['instance_class'],
                $database['storage'].'GB',
                Str::title(str_replace('_', ' ', $database['status'])),
                $database['proxy'] ? Str::title(str_replace('_', ' ', $database['proxy']['status'])) : 'No',
            ];
        })->all());
    }
}
