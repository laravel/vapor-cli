<?php

namespace Laravel\VaporCli\Commands;

use Illuminate\Support\Str;
use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class CacheShowCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cache:show')
            ->addArgument('cache', InputArgument::REQUIRED, 'The cache name / ID')
            ->setDescription('Display the details of a cache');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        if (! is_numeric($cacheId = $this->argument('cache'))) {
            $cacheId = $this->findIdByName($this->vapor->caches(), $cacheId);
        }

        if (is_null($cacheId)) {
            Helpers::abort('Unable to find a cache with that name / ID.');
        }

        $cache = $this->vapor->cache($cacheId);

        $this->table([
            'ID', 'Provider', 'Name', 'Region', 'Class', 'Scale', 'Status',
        ], collect([$cache])->map(function ($cache) {
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

        if ($cache['endpoint']) {
            Helpers::line();

            Helpers::line(' <info>Endpoint:</info> '.$cache['endpoint']);
        }

        Helpers::line();

        $this->call('cache:metrics', ['cache' => $this->argument('cache')]);
    }
}
