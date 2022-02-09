<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class DatabaseMetricsCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('database:metrics')
            ->addArgument('database', InputArgument::REQUIRED, 'The database name / ID')
            ->addArgument('period', InputArgument::OPTIONAL, 'The metric period (1m, 5m, 30m, 1h, 8h, 1d, 7d, 1M)', '1d')
            ->setDescription('Get usage and performance metrics for a database');
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

        $metrics = $this->vapor->databaseMetrics(
            $databaseId,
            $this->argument('period')
        );

        $this->table([
            'Metric', 'Value',
        ], [
            ['Average CPU Utilization', number_format($metrics['averageDatabaseCpuUtilization']).'%'],
            ['Average Database Connections', number_format($metrics['averageDatabaseConnections'])],
            ['Max Database Connections', number_format($metrics['maxDatabaseConnections'])],
        ]);
    }
}
