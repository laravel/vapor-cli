<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class DatabaseDropUserCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('database:drop-user')
            ->addArgument('database', InputArgument::REQUIRED, 'The name of the database')
            ->addArgument('user', InputArgument::REQUIRED, 'The username of the database user to drop')
            ->setDescription('Drop a database user for a database');
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

        if (! is_numeric($userId = $this->argument('user'))) {
            $userId = $this->findIdByName($this->vapor->databaseUsers($databaseId), $userId, 'username');
        }

        if (is_null($userId)) {
            Helpers::abort('Unable to find a database user with that username.');
        }

        $response = $this->vapor->dropDatabaseUser($userId);

        Helpers::info('Database user dropped successfully.');
    }
}
