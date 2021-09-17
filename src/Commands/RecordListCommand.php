<?php

namespace Laravel\VaporCli\Commands;

use Illuminate\Support\Str;
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
            'ID', 'Concern', 'Type', 'Name', 'Value', 'Alias',
        ], collect($this->vapor->records($zoneId))->map(function ($record) {
            return [
                $record['id'],
                $this->getDnsRecordConcern($record),
                $record['type'],
                $record['name'],
                $record['value'],
                $record['alias'] ? '<info>✔</info>' : '',
            ];
        })->all());
    }

    /**
     * Gets the DNS Record concern.
     *
     * @param  array  $record
     * @return string
     */
    public function getDnsRecordConcern($record)
    {
        switch (true) {
            case ! $record['locked']:
                return 'Custom';
            case Str::endsWith($record['value'], 'acm-validations.aws'):
                return 'Certificates';
            case Str::endsWith($record['value'], 'dkim.amazonses.com') || Str::startsWith($record['name'], '_amazonses'):
                return 'Mail';
            default:
                return 'Environments';
        }
    }
}
