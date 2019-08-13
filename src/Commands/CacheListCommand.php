<?php

namespace Laravel\VaporCli\Commands;

use Illuminate\Support\Str;
use Laravel\VaporCli\Helpers;

class CacheListCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cache:list')
            ->setDescription('List the caches that belong to the current team');
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
            'ID', 'Provider', 'Name', 'Region', 'Class', 'Scale', 'Status',
        ], collect($this->vapor->caches())->map(function ($cache) {
            return [
                $cache['id'],
                $cache['cloud_provider']['name'],
                $cache['name'],
                $cache['region'],
                $cache['instance_class'],
                $cache['scale'],
                Str::title(str_replace('_', ' ', $cache['status'])),
            ];
        })->all());
    }
}
