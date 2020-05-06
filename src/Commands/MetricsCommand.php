<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Console\Input\InputArgument;

class MetricsCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('metrics')
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment name', 'staging')
            ->addArgument('period', InputArgument::OPTIONAL, 'The metric period (1m, 5m, 30m, 1h, 8h, 1d, 3d, 7d, 1M)', '1d')
            ->setDescription('Get usage and performance metrics for an environment');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $metrics = $this->vapor->metrics(
            Manifest::id(),
            $this->argument('environment'),
            $this->argument('period')
        );

        $this->table([
            'Metric', 'Value', 'Cost',
        ], [
            ['API Gateway Requests', number_format($metrics['totalRestApiRequests']), '<finished>$'.number_format($metrics['estimatedApiCost'], 2).'</finished>'],
            ['Web Function Invocations', number_format($metrics['totalFunctionInvocations']), '-'],
            ['CLI Function Invocations', number_format($metrics['totalCliFunctionInvocations']), '-'],
            ['Queue Function Invocations', number_format($metrics['totalQueueFunctionInvocations']), '-'],
            ['Average Web Function Duration', number_format($metrics['averageFunctionDuration'], 0).'ms', '-'],
            ['Average CLI Function Duration', number_format($metrics['averageCliFunctionDuration'], 0).'ms', '-'],
            ['Average Queue Function Duration', number_format($metrics['averageQueueFunctionDuration'], 0).'ms', '-'],
            ['Total Web Function Duration', number_format($metrics['totalFunctionDuration'] / 1000, 0).'s', '<finished>$'.number_format($metrics['estimatedCost'], 2).'</finished>'],
            ['Total CLI Function Duration', number_format($metrics['totalCliFunctionDuration'] / 1000, 0).'s', '<finished>$'.number_format($metrics['estimatedCliCost'], 2).'</finished>'],
            ['Total Queue Function Duration', number_format($metrics['totalQueueFunctionDuration'] / 1000, 0).'s', '<finished>$'.number_format($metrics['estimatedQueueCost'], 2).'</finished>'],
        ]);

        Helpers::line();

        Helpers::line('<info>Total Estimated Application Layer Cost:</info> $'.number_format($metrics['estimatedApiCost'] + $metrics['estimatedCost'] + $metrics['estimatedCliCost'], 2));
    }
}
