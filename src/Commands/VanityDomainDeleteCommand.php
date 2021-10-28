<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Console\Input\InputArgument;

class VanityDomainDeleteCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('vanity-domain:delete')
            ->addArgument('environment', InputArgument::REQUIRED, 'The environment name')
            ->setDescription('Delete the vanity domain associated with the given environment');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $environment = $this->argument('environment');

        if (! Helpers::confirm("Are you sure you want to delete the vanity domain of the [{$environment}] environment", false)) {
            Helpers::abort('Action cancelled.');
        }

        $this->vapor->deleteVanityDomain(
            Manifest::id(),
            $environment
        );

        Helpers::info('Vanity domain deletion initiated successfully.');
        Helpers::line();
        Helpers::line('The process may take several seconds to complete.');
    }
}
