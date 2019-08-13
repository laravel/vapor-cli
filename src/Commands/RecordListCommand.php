<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class RecordListCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('record:list')
            ->addArgument('zone', InputArgument::REQUIRED, 'The zone name / ID')
            ->setDescription('List the DNS records that belong to a zone');
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

        $this->table([
            'ID', 'Type', 'Name', 'Value', 'Alias', 'Locked',
        ], collect($this->vapor->records($zoneId))->map(function ($record) {
            return [
                $record['id'],
                $record['type'],
                $record['name'],
                $record['value'],
                $record['alias'] ? '<info>✔</info>' : '',
                $record['locked'] ? '<info>✔</info>' : '',
            ];
        })->all());
    }
}
