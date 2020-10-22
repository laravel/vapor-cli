<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class DatabaseProxyCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('database:proxy')
            ->addArgument('database', InputArgument::REQUIRED, 'The name of the database')
            ->setDescription('Create an proxy for a database');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $databases = $this->vapor->databases();

        if (! is_numeric($databaseId = $this->argument('database'))) {
            $databaseId = $this->findIdByName($databases, $databaseId);
        }

        if (is_null($databaseId)) {
            Helpers::abort('Unable to find a database with that name / ID.');
        }

        $response = $this->vapor->createDatabaseProxy(
            $databaseId
        );

        Helpers::info('Database proxy created successfully.');
    }
}
