<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class CacheDeleteCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cache:delete')
            ->addArgument('cache', InputArgument::REQUIRED, 'The cache name / ID')
            ->setDescription('Delete a cache');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        if (! Helpers::confirm('Are you sure you want to delete this cache', false)) {
            Helpers::abort('Action cancelled.');
        }

        if (! is_numeric($cacheId = $this->argument('cache'))) {
            $cacheId = $this->findIdByName($this->vapor->caches(), $cacheId);
        }

        if (is_null($cacheId)) {
            Helpers::abort('Unable to find a cache with that name / ID.');
        }

        $this->vapor->deleteCache($cacheId);

        Helpers::info('Cache deletion initiated successfully.');
        Helpers::line();
        Helpers::line('The cache deletion process may take several minutes to complete.');
    }
}
