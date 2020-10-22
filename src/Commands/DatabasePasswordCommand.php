<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class DatabasePasswordCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('database:password')
            ->addArgument('database', InputArgument::REQUIRED, 'The name / ID of the database')
            ->setDescription('Rotate the password of the given database');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        if (! is_numeric($databaseId = $this->argument('database'))) {
            $databaseId = $this->findIdByName($this->vapor->databases(), $databaseId);
        }

        if (is_null($databaseId)) {
            Helpers::abort('Unable to find a database with that name / ID.');
        }

        $password = $this->vapor->rotateDatabasePassword($databaseId);

        Helpers::info('Database password rotated successfully.');
        Helpers::line();
        Helpers::line('<comment>New Password:</comment> '.$password);
    }
}
