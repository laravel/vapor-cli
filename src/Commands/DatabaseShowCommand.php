<?php

namespace Laravel\VaporCli\Commands;

use Illuminate\Support\Str;
use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class DatabaseShowCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('database:show')
            ->addArgument('database', InputArgument::REQUIRED, 'The database name / ID')
            ->setDescription('Display the details of a database');
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

        $this->table([
            'ID', 'Provider', 'Name', 'Region', 'Type', 'Class', 'Storage', 'Status',
        ], collect([$database])->map(function ($database) {
            return [
                $database['id'],
                $database['cloud_provider']['name'],
                $database['name'],
                $database['region'],
                in_array($database['type'], ['aurora-serverless', 'aurora-serverless-v2', 'aurora-serverless-pgsql', 'aurora-serverless-v2-pgsql']) ? 'Serverless' : 'Fixed Size',
                $database['instance_class'],
                $database['storage'].'GB',
                Str::title(str_replace('_', ' ', $database['status'])),
            ];
        })->all());

        if ($database['endpoint']) {
            Helpers::line();

            Helpers::line(' <info>Endpoint:</info> '.$database['endpoint']);
        }

        Helpers::line();

        $this->call('database:metrics', ['database' => $this->argument('database')]);
    }
}
