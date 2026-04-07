<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class DatabaseUpgradeCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('database:upgrade')
            ->addArgument('from', InputArgument::REQUIRED, 'The name / ID of the existing database')
            ->addArgument('to', InputArgument::REQUIRED, 'The name of the new database')
            ->setDescription('Create a new database of the selected type containing the contents of an existing database.');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        Helpers::abort('This command is deprecated. Please use the AWS console to upgrade the database.');
    }
}
