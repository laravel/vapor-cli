<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
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
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment name')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'The secret name')
            ->addOption('value', null, InputOption::VALUE_OPTIONAL, 'The secret value')
            ->addOption('file', null, InputOption::VALUE_OPTIONAL, 'The file that contains the secret value')
            ->setDescription('Create or update an environment secret');
    }

    /**
     * Execute the command.
     *
     * @return int
     */
    public function handle()
    {
        Helpers::danger('Secrets have been deprecated. Instead, please utilize environment variables and / or encrypted environment files.');

        return 1;
    }
}
