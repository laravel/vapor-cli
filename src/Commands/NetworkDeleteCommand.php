<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class NetworkDeleteCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('network:delete')
            ->addArgument('network', InputArgument::REQUIRED, 'The network name / ID')
            ->setDescription('Delete a network');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        if (! Helpers::confirm('Are you sure you want to delete this network', false)) {
            Helpers::abort('Action cancelled.');
        }

        if (! is_numeric($networkId = $this->argument('network'))) {
            $networkId = $this->findIdByName($this->vapor->networks(), $networkId);
        }

        if (is_null($networkId)) {
            Helpers::abort('Unable to find a network with that name / ID.');
        }

        $this->vapor->deleteNetwork($networkId);

        Helpers::info('Network deletion initiated successfully.');
        Helpers::line();
        Helpers::line('The network deletion process may take several minutes to complete.');
    }
}
