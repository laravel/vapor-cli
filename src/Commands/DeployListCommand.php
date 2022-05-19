<?php

namespace Laravel\VaporCli\Commands;

use Illuminate\Support\Str;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Console\Input\InputArgument;

class DeployListCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('deploy:list')
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment name')
            ->setDescription('List the deployments for an environment');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $deployments = $this->vapor->deployments(
            Manifest::id(),
            $environment = $this->argument('environment')
        );

        if (empty($deployments)) {
            Helpers::abort("The [{$environment}] environment does not have any recent deployments.");
        }

        $this->table([
            'ID', 'Message', 'Commit', 'Initiator', 'Happened',
        ], collect($deployments)->map(function ($deployment) {
            return [
                $deployment['id'],
                Str::limit($deployment['artifact']['commit_message'], 40) ?: '-',
                substr($deployment['artifact']['commit'], 0, 8) ?: '-',
                $deployment['initiator']['name'],
                Helpers::time_ago($deployment['created_at']),
            ];
        })->all());
    }
}
