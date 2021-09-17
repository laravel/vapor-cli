<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class CacheTunnelCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cache:tunnel')
            ->addArgument('cache', InputArgument::REQUIRED, 'The name of the cache')
            ->setDescription('Create a secure tunnel to a cache, allowing local connections');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $caches = $this->vapor->caches();

        if (! is_numeric($cacheId = $this->argument('cache'))) {
            $cacheId = $this->findIdByName($caches, $cacheId);
        }

        if (is_null($cacheId)) {
            Helpers::abort('Unable to find a cache with that name / ID.');
        }

        $jumpBox = $this->findCompatibleJumpBox(
            $cache = collect($caches)->firstWhere('id', $cacheId)
        );

        Helpers::line('<info>Establishing secure tunnel to</info> <comment>['.$cache['name'].']</comment> <info>on</info> <comment>[localhost:6378]</comment><info>...</info>');

        passthru(sprintf(
            'ssh ec2-user@%s -i %s -o LogLevel=error -L 6378:%s:6379 -N',
            $jumpBox['endpoint'],
            $this->storeJumpBoxKey($jumpBox),
            $cache['endpoint']
        ));
    }

    /**
     * Find a jump-box compatible with the cache.
     *
     * @param  array  $cache
     * @return array
     */
    protected function findCompatibleJumpBox(array $cache)
    {
        $jumpBoxes = $this->vapor->jumpBoxes();

        $jumpBox = collect($jumpBoxes)->firstWhere(
            'network_id',
            $cache['network_id']
        );

        if (is_null($jumpBox)) {
            Helpers::abort('A jumpbox is required in order to create a tunnel.');
        }

        return $jumpBox;
    }

    /**
     * Store the private SSH key for the jump-box.
     *
     * @param  array  $jumpBox
     * @return string
     */
    protected function storeJumpBoxKey(array $jumpBox)
    {
        file_put_contents(
            $path = Helpers::home().'/.ssh/vapor-cache-tunnel',
            $this->vapor->jumpBoxKey($jumpBox['id'])['private_key']
        );

        chmod($path, 0600);

        return $path;
    }
}
