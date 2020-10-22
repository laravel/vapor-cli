<?php

namespace Laravel\VaporCli\Commands;

use DateTimeZone;
use Exception;
use Illuminate\Support\Carbon;
use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class DatabaseRestoreCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('database:restore')
            ->addArgument('from', InputArgument::REQUIRED, 'The name / ID of the existing database')
            ->addArgument('to', InputArgument::REQUIRED, 'The name of the new database')
            ->setDescription('Create a new database using a point in time restore of an existing database');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        if (! is_numeric($databaseId = $this->argument('from'))) {
            $databaseId = $this->findIdByName($this->vapor->databases(), $databaseId);
        }

        if (is_null($databaseId)) {
            Helpers::abort('Unable to find a database with that name / ID.');
        }

        $timezone = $this->determineTimezone();

        $restoreTo = Carbon::createFromTimestamp(
            strtotime(Helpers::ask('What point in time would you like to restore to (any date parsable by the "strtotime" function)'))
        )->setTimezone($timezone);

        if (! Helpers::confirm('Create a new database ['.$this->argument('to').'] that contains the contents of ['.$this->argument('from').'] as of '.$restoreTo->format('Y-m-d H:i:s').' ('.$timezone.')', false)) {
            Helpers::abort('Action cancelled.');
        }

        $this->vapor->restoreDatabase(
            $databaseId,
            $this->argument('to'),
            $restoreTo->setTimezone('UTC')->getTimestamp()
        );

        Helpers::info('Database restoration initiated successfully.');
        Helpers::line();
        Helpers::line('Databases may take several minutes to finish provisioning.');
    }

    /**
     * Determine which timezone the time will be specified in.
     *
     * @return string
     */
    protected function determineTimezone()
    {
        try {
            new DateTimeZone($timezone = Helpers::ask('Which timezone would you like to use to specify the point in time to restore to', 'UTC'));

            return $timezone;
        } catch (Exception $e) {
            Helpers::abort('The provided timezone is invalid (http://php.net/manual/en/timezones.php).');
        }
    }
}
