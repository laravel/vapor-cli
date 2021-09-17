<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\DatabaseInstanceClasses;
use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class DatabaseScaleCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('database:scale')
            ->addArgument('database', InputArgument::REQUIRED, 'The database name / ID')
            ->setDescription('Modify the instance class and / or allocated storage for a database');
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

        $database = $this->vapor->database($databaseId);

        $instanceClass = $this->determineRdsInstanceClass();

        $allocatedStorage = $this->determineAllocatedStorage($database);

        $this->vapor->scaleDatabase(
            $databaseId,
            $instanceClass,
            $allocatedStorage
        );

        Helpers::info('Database modification initiated successfully.');
        Helpers::line();
        Helpers::line('Databases may take several minutes to finish scaling.');
    }

    /**
     * Determine the instance class of an RDS database.
     *
     * @return string
     */
    protected function determineRdsInstanceClass()
    {
        $type = $this->menu('Which type of database instance would you like to scale to?', [
            'general' => 'General Purpose',
            'memory'  => 'Memory Optimized',
        ]);

        if ($type == 'general') {
            return $this->menu(
                'Which database size would you like to scale to?',
                DatabaseInstanceClasses::general()
            );
        } else {
            return $this->menu(
                'Which database size would you like to scale to?',
                DatabaseInstanceClasses::memory()
            );
        }
    }

    /**
     * Determine how much storage should be allocated to the database.
     *
     * @param  array  $database
     * @return int
     */
    protected function determineAllocatedStorage(array $database)
    {
        $allocatedStorage = Helpers::ask('How much storage should be allocated to your database (GB) ($0.115 / GB)', $database['storage']);

        if ($allocatedStorage < 20 || $allocatedStorage > 32768) {
            Helpers::abort('Allocated storage must be between 20GB and 32TB.');
        }

        if ($allocatedStorage < $database['storage']) {
            Helpers::abort('Allocated storage may not be decreased.');
        }

        return $allocatedStorage;
    }
}
