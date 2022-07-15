<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Console\Input\InputArgument;

class InvalidateAssetCacheCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('asset:invalidate-cache')
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment name')
            ->setDescription('Invalidate the asset cache for the given environment');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $this->vapor->invalidateAssetCache(
            Manifest::id(),
            $this->argument('environment')
        );

        Helpers::info("Asset cache for {$this->argument('environment')} is being invalidated.");
    }
}
