<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DatabaseDeleteCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('database:delete')
            ->addArgument('database', InputArgument::REQUIRED, 'The database name / ID')
            ->addOption('force', false, InputOption::VALUE_NONE, 'Force deletion of the database without confirmation')
            ->setDescription('Delete a database');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $forceDeletion = $this->option('force', false);

        if (! $forceDeletion && ! Helpers::confirm('Are you sure you want to delete this database', false)) {
            Helpers::abort('Action cancelled.');
        }

        if (! is_numeric($databaseId = $this->argument('database'))) {
            $databaseId = $this->findIdByName($this->vapor->databases(), $databaseId);
        }

        if (is_null($databaseId)) {
            Helpers::abort('Unable to find a database with that name / ID.');
        }

        $this->vapor->deleteDatabase($databaseId);

        Helpers::info('Database deletion initiated successfully.');
        Helpers::line();
        Helpers::line('The database deletion process may take several minutes to complete.');
    }
}
