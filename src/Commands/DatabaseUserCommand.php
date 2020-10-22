<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class DatabaseUserCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('database:user')
            ->addArgument('database', InputArgument::REQUIRED, 'The name of the database')
            ->addArgument('user', InputArgument::REQUIRED, 'The username of the database user to create')
            ->setDescription('Create an additional database user for a database');
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

        $response = $this->vapor->createDatabaseUser(
            $databaseId,
            $this->argument('user')
        );

        Helpers::info('Database user created successfully.');
        Helpers::line();
        Helpers::line('<comment>Username:</comment> '.$response['username']);
        Helpers::line('<comment>Password:</comment> '.$response['password']);
    }
}
