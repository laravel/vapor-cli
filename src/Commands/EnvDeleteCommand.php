<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Dockerfile;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Console\Input\InputArgument;

class EnvDeleteCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('env:delete')
            ->addArgument('environment', InputArgument::REQUIRED, 'The environment name')
            ->setDescription('Delete an environment');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $environment = $this->argument('environment');

        if (! Helpers::confirm("Are you sure you want to delete the [{$environment}] environment", false)) {
            Helpers::abort('Action cancelled.');
        }

        $this->vapor->deleteEnvironment(
            Manifest::id(),
            $environment
        );

        Manifest::deleteEnvironment($environment);

        Dockerfile::deleteEnvironment($environment);

        Helpers::info('Environment deletion initiated successfully.');
        Helpers::line();
        Helpers::line('The environment deletion process may take several seconds to complete.');
    }
}
