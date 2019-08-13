<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class DatabaseUsersCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('database:users')
            ->addArgument('database', InputArgument::REQUIRED, 'The name of the database')
            ->setDescription('List the database users for a database');
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

        $this->table([
            'ID', 'Username', 'Created',
        ], collect($this->vapor->databaseUsers($databaseId))->map(function ($user) {
            return [
                $user['id'],
                $user['username'],
                Helpers::time_ago($user['created_at']),
            ];
        })->all());
    }
}
