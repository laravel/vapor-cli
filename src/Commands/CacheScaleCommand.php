<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

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
            ->addArgument('scale', InputArgument::REQUIRED, 'The number of nodes that should be in the cache cluster')
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

        $this->vapor->scaleCache(
            $cacheId,
            $this->argument('scale')
        );

        Helpers::info('Cache modification initiated successfully.');
        Helpers::line();
        Helpers::line('Caches may take several minutes to finish scaling.');
    }
}
