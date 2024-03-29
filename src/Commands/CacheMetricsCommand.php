<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class CacheMetricsCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cache:metrics')
            ->addArgument('cache', InputArgument::REQUIRED, 'The cache name / ID')
            ->addArgument('period', InputArgument::OPTIONAL, 'The metric period (5m, 30m, 1h, 8h, 1d, 3d, 7d, 1M)', '1d')
            ->setDescription('Get usage and performance metrics for a cache');
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

        $metrics = $this->vapor->cacheMetrics(
            $cacheId,
            $this->argument('period')
        );

        if (isset($metrics['averageCacheProcessingUnits'])) {
            return $this->serverlessMetrics($metrics);
        }

        $this->table([
            'Node', 'Average CPU Utilization', 'Cache Hits', 'Cache Misses',
        ], collect(range(0, count($metrics['totalCacheHits']) - 1))->map(function ($node) use ($metrics) {
            return [
                'Node '.($node + 1),
                number_format($metrics['averageCacheCpuUtilization'][$node]).'%',
                $metrics['totalCacheHits'][$node],
                $metrics['totalCacheMisses'][$node],
            ];
        })->all());
    }

    /**
     * Format the serverless metrics for display.
     */
    protected function serverlessMetrics(array $metrics): void
    {
        $this->table([
            'Average CPU (ECPU Units)', 'Average Memory Utilization (Bytes)', 'Cache Hits', 'Cache Misses',
        ], [[
            number_format($metrics['averageCacheProcessingUnits'][0]),
            number_format($metrics['averageCacheBytesUsed'][0]),
            $metrics['totalCacheHits'][0],
            $metrics['totalCacheMisses'][0],
        ]]);
    }
}
