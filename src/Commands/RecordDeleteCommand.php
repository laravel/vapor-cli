<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RecordDeleteCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('record:delete')
            ->addArgument('zone', InputArgument::REQUIRED, 'The zone name / record ID')
            ->addArgument('type', InputArgument::REQUIRED, 'The record type')
            ->addArgument('name', InputArgument::OPTIONAL, 'The record name')
            ->addArgument('value', InputArgument::OPTIONAL, 'The record value')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force deletion of the record without confirmation')
            ->setDescription('Delete a DNS record');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $forceDeletion = $this->option('force');

        if (! $forceDeletion && ! Helpers::confirm('Are you sure you want to delete this record', false)) {
            Helpers::abort('Action cancelled.');
        }

        if (! is_numeric($zoneId = $this->argument('zone'))) {
            $zoneId = $this->findIdByName($this->vapor->zones(), $zoneId, 'zone');
        }

        if (is_null($zoneId)) {
            Helpers::abort('Unable to find a zone with that name / ID.');
        }

        $this->vapor->deleteRecord(
            $zoneId,
            $this->argument('type'),
            $this->argument('name'),
            $this->argument('value')
        );

        Helpers::info('Record deleted successfully.');
    }
}
