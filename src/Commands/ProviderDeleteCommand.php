<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class ProviderDeleteCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('provider:delete')
            ->addArgument('provider', InputArgument::REQUIRED, 'The provider name / ID')
            ->setDescription('Delete a cloud provider');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        if (! Helpers::confirm('Are you sure you want to delete this cloud provider', false)) {
            Helpers::abort('Action cancelled.');
        }

        if (! is_numeric($providerId = $this->argument('provider'))) {
            $providerId = $this->findIdByName($this->vapor->providers(), $providerId);
        }

        if (is_null($providerId)) {
            Helpers::abort('Unable to find a cloud provider with that name / ID.');
        }

        $this->vapor->deleteProvider($providerId);

        Helpers::info('Cloud provider account deletion initiated successfully.');
    }
}
