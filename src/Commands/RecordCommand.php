<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class RecordCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('record')
            ->addArgument('zone', InputArgument::REQUIRED, 'The zone name / ID')
            ->addArgument('type', InputArgument::REQUIRED, 'The DNS record type')
            ->addArgument('name', InputArgument::REQUIRED, 'The DNS record name')
            ->addArgument('value', InputArgument::REQUIRED, 'The DNS record value')
            ->setDescription('Add a new DNS record to a zone');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        if (! is_numeric($zoneId = $this->argument('zone'))) {
            $zoneId = $this->findIdByName($this->vapor->zones(), $zoneId, 'zone');
        }

        if (is_null($zoneId)) {
            Helpers::abort('Unable to find a zone with that name / ID.');
        }

        $this->vapor->createRecord(
            $zoneId,
            $this->argument('type'),
            $this->argument('name'),
            $this->argument('value')
        );

        Helpers::info('Record updated successfully.');
    }
}
