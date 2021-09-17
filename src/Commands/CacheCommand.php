<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\CacheInstanceClasses;
use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class CacheCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cache')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the cache')
            ->setDescription('Create a new cache');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $networkId = $this->determineNetwork(
            'Which network should the cache be placed in?'
        );

        if (is_null($networkId)) {
            Helpers::abort('Unable to find a network with that name / ID.');
        }

        if (! $this->networkHasNatGateway($networkId) &&
            ! Helpers::confirm('A cache will require Vapor to add a NAT gateway to your network (~32 / month). Would you like to proceed', true)) {
            Helpers::abort('Action cancelled.');
        }

        $instanceClass = $this->determineInstanceClass();
        $type = $this->determineCacheType();

        $response = $this->vapor->createCache(
            $networkId,
            $this->argument('name'),
            $type,
            $instanceClass
        );

        Helpers::info('Cache creation initiated successfully.');
        Helpers::line();
        Helpers::line('Caches may take several minutes to finish provisioning.');
    }

    /**
     * Determine the cache type.
     *
     * @return string
     */
    protected function determineCacheType()
    {
        return $this->menu('Which type of cache would you like to create?', [
            'redis6.x-cluster' => 'Redis 6.x Cluster',
            'redis-cluster'    => 'Redis 5.x Cluster',
        ]);
    }

    /**
     * Determine the instance class of the cache.
     *
     * @return string|null
     */
    protected function determineInstanceClass()
    {
        $type = $this->menu('Which type of cache instance would you like to create?', [
            'general' => 'General Purpose',
            'memory'  => 'Memory Optimized',
        ]);

        if ($type == 'general') {
            return $this->determineGeneralInstanceClass();
        } else {
            return $this->determineMemoryOptimizedInstanceClass();
        }
    }

    /**
     * Determine the instance class of a general cache.
     *
     * @return string
     */
    protected function determineGeneralInstanceClass()
    {
        return $this->menu(
            'How much performance does your cache require?',
            CacheInstanceClasses::general()
        );
    }

    /**
     * Determine the instance class of a memory optimized cache.
     *
     * @return string
     */
    protected function determineMemoryOptimizedInstanceClass()
    {
        return $this->menu(
            'How much performance does your cache require?',
            CacheInstanceClasses::memory()
        );
    }

    /**
     * Determine if the given network has a NAT gateway.
     *
     * @param  int  $networkId
     * @return bool
     */
    protected function networkHasNatGateway($networkId)
    {
        return collect($this->vapor->networks())->first(function ($network) use ($networkId) {
            return $network['id'] == $networkId;
        })['has_internet_access'] ?? false;
    }
}
