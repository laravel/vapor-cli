<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Console\Input\InputArgument;

class SecretListCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('secret:list')
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment name', 'staging')
            ->setDescription('List the secrets for a given environment');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $secrets = $this->vapor->secrets(
            Manifest::id(),
            $this->argument('environment')
        );

        $this->table([
            'ID', 'Name', 'Current Version', 'Last Updated',
        ], collect($secrets)->map(function ($secret) {
            return [
                $secret['id'],
                $secret['name'],
                $secret['version'],
                Helpers::time_ago($secret['updated_at']),
            ];
        })->all());
    }
}
