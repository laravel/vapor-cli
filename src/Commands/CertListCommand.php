<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class CertListCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cert:list')
            ->addArgument('domain', InputArgument::OPTIONAL, 'The domain name to list certificates for')
            ->setDescription('List the certificates linked to your account');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $certificates = $this->vapor->certificates(
            $this->argument('domain')
        );

        if (empty($certificates)) {
            Helpers::abort('You do not have any certificates.');
        }

        $this->table([
            'ID', 'Provider', 'Domain', 'Alternative Domains', 'Status', 'Active', 'Created',
        ], collect($certificates)->map(function ($certificate) {
            return [
                $certificate['id'],
                $certificate['cloud_provider']['name'],
                $certificate['domain'],
                implode(', ', $certificate['alternative_names']) ?: '-',
                ucwords(str_replace('_', ' ', $certificate['status'])),
                ($certificate['used_by_active_deployment'] ?? false) ? '<info>✔</info>' : '',
                Helpers::time_ago($certificate['created_at']),
            ];
        })->all());
    }
}
