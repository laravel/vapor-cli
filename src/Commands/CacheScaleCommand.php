<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CacheScaleCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cache:scale')
            ->addArgument('cache', InputArgument::REQUIRED, 'The cache name / ID')
            ->addArgument('scale', InputArgument::OPTIONAL, 'The number of nodes that should be in the cache cluster')
            ->addOption('memory', null, InputOption::VALUE_OPTIONAL, 'The maximum amount of memory that can be used by the serverless cache')
            ->addOption('cpu', null, InputOption::VALUE_OPTIONAL, 'The maximum amount of ECPUs that can be used by the serverless cache')
            ->setDescription('Modify the number of nodes in a cache cluster');
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

        if ($cache['type'] === 'redis7.x-serverless') {
            $this->scaleServerlessCache($cacheId);
        } else {
            $this->scaleCacheCluster($cacheId);
        }

        Helpers::info('Cache modification initiated successfully.');
        Helpers::line();
        Helpers::line('Caches may take several minutes to finish scaling.');
    }

    /**
     * Scale a serverless cache.
     */
    protected function scaleServerlessCache(int $cacheId): void
    {
        if (is_null($this->option('memory')) || is_null($this->option('cpu'))) {
            Helpers::abort('You must specify both the memory and CPU limits. To remove the either limit, set it to 0.');
        }

        $this->vapor->scaleCache(
            $cacheId,
            null,
            $this->option('memory') === '0' ? null : $this->option('memory'),
            $this->option('cpu') === '0' ? null : $this->option('cpu')
        );
    }

    /**
     * Scale a cache cluster.
     */
    protected function scaleCacheCluster(int $cacheId): void
    {
        if (! $scale = $this->argument('scale')) {
            Helpers::abort('You must specify the number of nodes to scale the cache to.');
        }

        $this->vapor->scaleCache($cacheId, $scale);
    }
}
