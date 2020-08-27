<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class SecretCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('secret')
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment name', 'staging')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'The secret name')
            ->addOption('file', null, InputOption::VALUE_OPTIONAL, 'The file that contains the secret value')
            ->setDescription('Create or update an environment secret');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $this->vapor->storeSecret(
            Manifest::id(),
            $this->argument('environment'),
            $this->option('name') ?? Helpers::ask('Name'),
            $this->determineValue()
        );

        Helpers::info('Secret stored successfully.');
        Helpers::line('You should deploy the project to ensure the new secrets are available.');
    }

    /**
     * Determine the secret's value.
     *
     * @return string
     */
    protected function determineValue()
    {
        return $this->option('file')
                    ? file_get_contents($this->option('file'))
                    : Helpers::ask('Value');
    }
}
